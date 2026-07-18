<?php

namespace wpmvc\debug;

use wpmvc\base\Component;

/**
 * Debug toolbar component — a thin orchestrator over collectors.
 *
 * Registered as the `debug` component and listed under `bootstrap` in local
 * environments only, so hooks are attached without the component being
 * accessed. Collectors gather data during the request; the toolbar renders
 * at the end of it.
 */
class Debug extends Component {

    const VERSION = '0.5.0';

    /** Shared nonce action for the debugger's AJAX endpoints. */
    const NONCE = 'wpmvc-debug';

    /** Memoized tab instances (see get_tabs()). */
    private $tab_instances;

    /**
     * Absolute filesystem path to the package root, e.g. `/var/www/html/wpmvc-debug`.
     * Derived from the package location itself (same pattern as `App::$base_path`),
     * so no configuration is needed — the package must live under ABSPATH.
     *
     * @var string
     */
    public static $root;

    /**
     * Base URL of the package, e.g. `https://example.com/wpmvc-debug`.
     * Same pattern as `App::$base_url`.
     *
     * @var string
     */
    public static $web;

    /**
     * Tab class names, in display order. A component attribute, so the set
     * of tabs can be overridden/extended via the component config.
     *
     * @var string[]
     */
    public $tabs = array(
        tabs\Overview_Tab::class,
        tabs\Applications_Tab::class,
        tabs\Components_Tab::class,
        tabs\Database_Tab::class,
        tabs\Logs_Tab::class,
        tabs\Events_Tab::class,
        tabs\Scheduled_Jobs_Tab::class,
        tabs\Environment_Tab::class,
    );

    /**
     * @param array $config
     */
    public function __construct( $config = array() ) {
        parent::__construct( $config );

        static::$root = dirname( __DIR__ );
        static::$web  = home_url( str_replace( ABSPATH, '', static::$root ) );

        // wpdb checks SAVEQUERIES on every query, so defining it here starts
        // query capture from this point on. Queries executed before the
        // component is constructed are not captured — define SAVEQUERIES in
        // wp-config.php to capture the whole request. An explicit `false`
        // in wp-config.php is respected (the Database tab explains it).
        if ( ! defined( 'SAVEQUERIES' ) ) {
            define( 'SAVEQUERIES', true );
        }

        // Start hook capture now — like SAVEQUERIES above, anything fired
        // before the component is constructed is not recorded.
        collectors\Hook_Collector::start();

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_debugger' ) );

        // Let each tab register its own AJAX handlers. Done here (not in
        // render) because admin-ajax requests never reach wp_footer.
        foreach ( $this->get_tabs() as $tab ) {
            $tab->register_ajax();
        }
    }

    /**
     * Enqueue the compiled toolbar assets.
     *
     * @return void
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'wpmvc-debug', static::$web . '/assets/css/main.css', array(), self::VERSION );
        wp_enqueue_script( 'wpmvc-debug', static::$web . '/assets/js/main.js', array(), self::VERSION, true );

        wp_localize_script( 'wpmvc-debug', 'wpmvcDebug', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( self::NONCE ),
        ) );
    }

    /**
     * Render the debugger UI (floating button + panel) in the footer.
     *
     * @return void
     */
    public function render_debugger() {
        $tabs  = $this->get_tabs();
        $chips = $this->get_header_chips();

        require static::$root . '/views/debugger.php';
    }

    /**
     * Header info chips: PHP/WP versions, request time and peak memory.
     * Measured at render time (wp_footer), so the values cover the whole page.
     *
     * @return array
     */
    public function get_header_chips() : array {
        return array(
            array( 'label' => 'PHP', 'value' => PHP_VERSION ),
            array( 'label' => 'WP', 'value' => get_bloginfo( 'version' ) ),
            array( 'label' => 'Time', 'value' => number_format( (float) timer_stop() * 1000 ) . ' ms' ),
            array( 'label' => 'Memory', 'value' => size_format( memory_get_peak_usage( true ) ) ),
        );
    }

    /**
     * Instantiated tabs, in display order. Memoized so the same instances
     * back both AJAX registration and rendering within a request.
     *
     * @return tabs\Tab[]
     */
    public function get_tabs() : array {
        if ( null === $this->tab_instances ) {
            $this->tab_instances = array_map( function ( $class ) {
                return new $class();
            }, $this->tabs );
        }

        return $this->tab_instances;
    }

}
