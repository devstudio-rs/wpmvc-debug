<?php

namespace wpmvc\debug\collectors;

/**
 * Hook collector — records the WordPress actions and filters fired during
 * the request, via the `all` hook.
 *
 * Capture starts when the Debug component boots, so hooks fired earlier in
 * the bootstrap (mu-plugins and plugins loading) are not recorded. Fires of
 * the same hook are aggregated into one entry (fire count + summed time),
 * and the number of unique hooks is hard-capped so a pathological request
 * cannot exhaust memory. High-frequency noise hooks (translations, escaping,
 * option/transient reads) are excluded entirely, which both keeps the list
 * useful and keeps the per-fire overhead negligible.
 *
 * Timing is measured per fire: the `all` hook marks the start, and a
 * self-registered PHP_INT_MAX-priority callback on the hook itself marks the
 * end — so the duration covers every regular callback and is inclusive of
 * nested hooks fired from within them. Callbacks registered by others at
 * PHP_INT_MAX after ours are not covered. Hooks with no registered callbacks
 * keep a zero duration (no end callback is attached to them, so `has_filter`
 * checks by other code keep returning false).
 */
class Hook_Collector {

    /** Hard cap on unique hooks recorded. */
    const MAX_HOOKS = 500;

    /** Per-hook depth guard for the timing stack (recursion / lost ends). */
    const MAX_DEPTH = 32;

    /**
     * High-frequency noise hooks skipped entirely (exact names).
     *
     * @var string[]
     */
    public static $excluded = array(
        'gettext',
        'gettext_with_context',
        'ngettext',
        'ngettext_with_context',
        'attribute_escape',
        'esc_html',
        'clean_url',
        'js_escape',
        'sanitize_key',
        'sanitize_title',
        'sanitize_text_field',
    );

    /**
     * Noise hook prefixes skipped entirely — dynamic per-option/transient
     * hooks would otherwise flood the unique-hook cap on any request.
     *
     * @var string[]
     */
    public static $excluded_prefixes = array(
        'option_',
        'pre_option_',
        'default_option_',
        'sanitize_option_',
        'site_option_',
        'pre_site_option_',
        'default_site_option_',
        'transient_',
        'pre_transient_',
        'site_transient_',
        'pre_site_transient_',
        'theme_mod_',
    );

    /** Whether capture is running for this request. */
    private static $started = false;

    /** Request start (WP's `$timestart`), the zero point for offsets. */
    private static $request_start = 0.0;

    /** `self::$excluded` flipped for O(1) lookups. */
    private static $excluded_index = array();

    /**
     * Aggregated events, keyed by hook name. Insertion order is first-fire
     * order, so the list is chronological. Values: hook, type, count,
     * first (offset in seconds), time (summed seconds).
     *
     * @var array[]
     */
    private static $events = array();

    /** Hooks that already carry the timing end callback. */
    private static $timed = array();

    /** Per-hook stacks of fire start times (hooks can nest). */
    private static $starts = array();

    /** Last seen `did_action()` count per hook, for action/filter detection. */
    private static $action_counts = array();

    /** Unique hooks seen after the cap was reached. */
    private static $dropped = array();

    /**
     * Attach the `all` hook and start recording. No-op in admin, AJAX and
     * cron requests — the toolbar never renders there.
     *
     * @return void
     */
    public static function start() {
        if ( self::$started || is_admin() || wp_doing_cron() ) {
            return;
        }

        self::$started        = true;
        self::$request_start  = ! empty( $GLOBALS['timestart'] ) ? (float) $GLOBALS['timestart'] : microtime( true );
        self::$excluded_index = array_fill_keys( self::$excluded, true );

        add_filter( 'all', array( __CLASS__, 'record' ) );
    }

    /**
     * The `all` handler — runs at the start of every hook fire. Must stay
     * as cheap as possible: it executes tens of thousands of times.
     *
     * @param string $hook The hook being fired.
     * @return void
     */
    public static function record( $hook ) {
        if ( isset( self::$excluded_index[ $hook ] ) ) {
            return;
        }

        foreach ( self::$excluded_prefixes as $prefix ) {
            if ( 0 === strncmp( $hook, $prefix, strlen( $prefix ) ) ) {
                return;
            }
        }

        // do_action() increments its counter before firing `all`, while
        // apply_filters() leaves it untouched — so a raised count means
        // this very fire is an action.
        $fired = did_action( $hook );
        $seen  = isset( self::$action_counts[ $hook ] ) ? self::$action_counts[ $hook ] : 0;

        self::$action_counts[ $hook ] = $fired;

        if ( ! isset( self::$events[ $hook ] ) ) {
            if ( count( self::$events ) >= self::MAX_HOOKS ) {
                self::$dropped[ $hook ] = true;
                return;
            }

            self::$events[ $hook ] = array(
                'hook'  => $hook,
                'type'  => $fired > $seen ? 'action' : 'filter',
                'count' => 0,
                'first' => microtime( true ) - self::$request_start,
                'time'  => 0.0,
            );
        }

        self::$events[ $hook ]['count']++;

        global $wp_filter;

        // Nothing to time when the hook has no callbacks — and not attaching
        // the end callback keeps `has_filter( $hook )` false for other code.
        if ( empty( $wp_filter[ $hook ] ) || empty( $wp_filter[ $hook ]->callbacks ) ) {
            return;
        }

        if ( ! isset( self::$timed[ $hook ] ) ) {
            self::$timed[ $hook ] = true;

            add_filter( $hook, array( __CLASS__, 'stop' ), PHP_INT_MAX );
        }

        if ( ! isset( self::$starts[ $hook ] ) ) {
            self::$starts[ $hook ] = array();
        }

        if ( count( self::$starts[ $hook ] ) < self::MAX_DEPTH ) {
            self::$starts[ $hook ][] = microtime( true );
        }
    }

    /**
     * The timing end callback, registered once per hook at PHP_INT_MAX.
     * For filters the value must pass through untouched.
     *
     * @param mixed $value First hook argument (the filtered value).
     * @return mixed
     */
    public static function stop( $value = null ) {
        $hook = current_filter();

        if ( ! empty( self::$starts[ $hook ] ) ) {
            $elapsed = microtime( true ) - array_pop( self::$starts[ $hook ] );

            if ( isset( self::$events[ $hook ] ) ) {
                self::$events[ $hook ]['time'] += $elapsed;
            }
        }

        return $value;
    }

    /**
     * The aggregated events, in first-fire order.
     *
     * @return array[]
     */
    public static function events() : array {
        return array_values( self::$events );
    }

    /**
     * Whether capture ran for this request.
     *
     * @return bool
     */
    public static function started() : bool {
        return self::$started;
    }

    /**
     * The request start timestamp offsets are measured from.
     *
     * @return float
     */
    public static function request_start() : float {
        return self::$request_start;
    }

    /**
     * How many unique hooks fired after the cap and were not recorded.
     *
     * @return int
     */
    public static function dropped() : int {
        return count( self::$dropped );
    }

}