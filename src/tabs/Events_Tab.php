<?php

namespace wpmvc\debug\tabs;

use wpmvc\debug\Debug;
use wpmvc\debug\collectors\Hook_Collector;

/**
 * Events tab — the WordPress actions and filters fired during the request,
 * aggregated per hook with fire counts and timing, plus a timeline of when
 * each hook first fired within the request.
 *
 * Fire data comes from Hook_Collector (started at Debug boot); the
 * registered callbacks are read from `$wp_filter` at render time, with
 * reflection resolving each callback's source file. Read-only.
 */
class Events_Tab extends Tab {

    public function get_id() : string {
        return 'events';
    }

    public function get_label() : string {
        return 'Events';
    }

    public function get_icon() : string {
        return '<path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z"/>';
    }

    public function get_badge() {
        return count( Hook_Collector::events() );
    }

    public function get_data() : array {
        $events  = Hook_Collector::events();
        $actions = 0;
        $filters = 0;
        $fires   = 0;
        $total   = 0.0;

        foreach ( $events as &$event ) {
            if ( 'action' === $event['type'] ) {
                $actions++;
            } else {
                $filters++;
            }

            $fires += $event['count'];
            $total += $event['time'];

            $event['callbacks'] = $this->get_callbacks( $event['hook'] );
        }

        unset( $event );

        return array(
            'started'  => Hook_Collector::started(),
            'events'   => $events,
            'duration' => Hook_Collector::started() ? microtime( true ) - Hook_Collector::request_start() : 0.0,
            'dropped'  => Hook_Collector::dropped(),
            'cap'      => Hook_Collector::MAX_HOOKS,
            'stats'    => array(
                array( 'label' => 'Hooks', 'meta' => number_format( $fires ) . ' fires in total', 'value' => count( $events ) ),
                array( 'label' => 'Actions', 'meta' => 'Unique hooks', 'value' => $actions ),
                array( 'label' => 'Filters', 'meta' => 'Unique hooks', 'value' => $filters ),
                array( 'label' => 'Total Time', 'meta' => 'In hook callbacks', 'value' => number_format( $total * 1000, 2 ) . ' ms' ),
            ),
        );
    }

    /**
     * The callbacks currently registered on a hook, flattened across
     * priorities, with the collector's own timing callback filtered out.
     *
     * @param string $hook Hook name.
     * @return array[]
     */
    private function get_callbacks( $hook ) : array {
        global $wp_filter;

        $callbacks = array();

        if ( empty( $wp_filter[ $hook ] ) ) {
            return $callbacks;
        }

        foreach ( $wp_filter[ $hook ]->callbacks as $priority => $handlers ) {
            foreach ( $handlers as $handler ) {
                $function = $handler['function'];

                if ( is_array( $function ) && Hook_Collector::class === $function[0] && 'stop' === $function[1] ) {
                    continue;
                }

                $callbacks[] = array_merge(
                    array(
                        'priority'      => $priority,
                        'accepted_args' => $handler['accepted_args'],
                    ),
                    $this->describe_callback( $function )
                );
            }
        }

        return $callbacks;
    }

    /**
     * Human-readable name, source label and file:line for a hook callback.
     *
     * @param callable $function The registered callback.
     * @return array
     */
    private function describe_callback( $function ) : array {
        $name = 'Unknown';
        $ref  = null;

        try {
            if ( $function instanceof \Closure ) {
                $name = 'Closure';
                $ref  = new \ReflectionFunction( $function );
            } elseif ( is_array( $function ) ) {
                $class = is_object( $function[0] ) ? get_class( $function[0] ) : (string) $function[0];
                $name  = $class . '::' . $function[1];
                $ref   = new \ReflectionMethod( $class, $function[1] );
            } elseif ( is_object( $function ) ) {
                $name = get_class( $function ) . '::__invoke';
                $ref  = new \ReflectionMethod( $function, '__invoke' );
            } elseif ( is_string( $function ) && false !== strpos( $function, '::' ) ) {
                list( $class, $method ) = explode( '::', $function, 2 );

                $name = $function;
                $ref  = new \ReflectionMethod( $class, $method );
            } elseif ( is_string( $function ) ) {
                $name = $function;
                $ref  = new \ReflectionFunction( $function );
            }
        } catch ( \ReflectionException $e ) {
            $ref = null;
        }

        $file = $ref ? $ref->getFileName() : false;

        return array(
            'name'   => $name,
            'source' => $this->callback_source( $file ),
            'file'   => $file ? str_replace( untrailingslashit( ABSPATH ), '', $file ) . ':' . $ref->getStartLine() : null,
        );
    }

    /**
     * Where a callback lives, derived from its file path: WordPress core,
     * a plugin/theme, this package, or the top-level directory otherwise.
     *
     * @param string|false $file Absolute file path, or false for PHP internals.
     * @return string
     */
    private function callback_source( $file ) : string {
        if ( ! $file ) {
            return 'PHP';
        }

        if ( 0 === strpos( $file, Debug::$root ) ) {
            return 'WPMVC Debug';
        }

        $relative = ltrim( str_replace( untrailingslashit( ABSPATH ), '', $file ), '/\\' );
        $relative = str_replace( '\\', '/', $relative );

        if ( 0 === strpos( $relative, 'wp-includes/' ) || 0 === strpos( $relative, 'wp-admin/' ) || preg_match( '#^wp-[^/]+\.php$#', $relative ) ) {
            return 'WordPress';
        }

        if ( preg_match( '#^wp-content/(plugins|themes|mu-plugins)/([^/.]+)#', $relative, $matches ) ) {
            $labels = array(
                'plugins'    => 'Plugin',
                'themes'     => 'Theme',
                'mu-plugins' => 'MU Plugin',
            );

            return $labels[ $matches[1] ] . ': ' . $matches[2];
        }

        if ( 0 === strpos( $relative, 'wpmvc/' ) ) {
            return 'WPMVC';
        }

        $segment = strtok( $relative, '/' );

        return $segment ? $segment : 'Unknown';
    }

}
