<?php

namespace wpmvc\debug\tabs;

/**
 * Cache tab — the state of WordPress's caching layers for this request:
 * the object cache (hits/misses + per-group contents), the transients
 * stored in the database, and the autoloaded options.
 *
 * Only what WordPress actually exposes is shown — there is no history, so
 * no rates-over-time; and page/browser caches live outside PHP entirely.
 * The admin-only actions are flushing the object cache (shown only when a
 * persistent drop-in is active — flushing the default per-request cache
 * is a no-op) and deleting expired transients.
 */
class Cache_Tab extends Tab {

    /** Max transient rows listed (largest first beyond that). */
    const MAX_TRANSIENTS = 200;

    /** Autoloaded options listed (largest first). */
    const MAX_OPTIONS = 30;

    public function get_id() : string {
        return 'cache';
    }

    public function get_label() : string {
        return 'Cache';
    }

    public function get_icon() : string {
        return '<path d="M8.235 1.559a.5.5 0 0 0-.47 0l-7.5 4a.5.5 0 0 0 0 .882L3.188 8 .264 9.559a.5.5 0 0 0 0 .882l7.5 4a.5.5 0 0 0 .47 0l7.5-4a.5.5 0 0 0 0-.882L12.813 8l2.922-1.559a.5.5 0 0 0 0-.882zm3.515 7.008L14.438 10 8 13.433 1.562 10 4.25 8.567l3.515 1.874a.5.5 0 0 0 .47 0zM8 9.433 1.562 6 8 2.567 14.438 6z"/>';
    }

    public function register_ajax() {
        add_action( 'wp_ajax_wpmvc_debug_cache', array( $this, 'ajax_cache' ) );
    }

    /**
     * AJAX: flush the object cache or delete expired transients.
     *
     * @return void
     */
    public function ajax_cache() {
        $this->verify_ajax();

        $op = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( $_POST['op'] ) ) : '';

        if ( 'flush-object' === $op ) {
            wp_send_json_success( array( 'ok' => wp_cache_flush() ) );
        }

        if ( 'delete-expired-transients' === $op ) {
            delete_expired_transients( true );

            wp_send_json_success( array( 'ok' => true ) );
        }

        wp_send_json_error( array( 'message' => 'Unknown op' ), 400 );
    }

    public function get_data() : array {
        global $wp_object_cache;

        $external = wp_using_ext_object_cache();
        $class    = is_object( $wp_object_cache ) ? get_class( $wp_object_cache ) : null;
        $hits     = is_object( $wp_object_cache ) && isset( $wp_object_cache->cache_hits ) ? (int) $wp_object_cache->cache_hits : null;
        $misses   = is_object( $wp_object_cache ) && isset( $wp_object_cache->cache_misses ) ? (int) $wp_object_cache->cache_misses : null;
        $ratio    = null !== $hits && null !== $misses && ( $hits + $misses ) > 0
            ? $hits / ( $hits + $misses ) * 100
            : null;

        $groups      = $this->get_groups();
        $items_total = 0;
        $size_total  = 0;

        foreach ( (array) $groups as $group ) {
            $items_total += $group['items'];
            $size_total  += $group['size'];
        }

        $transients = $this->get_transients();
        $options    = $this->get_autoloaded_options();

        return array(
            'external'   => $external,
            'class'      => $class,
            'hits'       => $hits,
            'misses'     => $misses,
            'groups'     => $groups,
            'transients' => $transients,
            'options'    => $options,
            'stats'      => array(
                array(
                    'label' => 'Hit Rate',
                    'meta'  => null !== $hits ? number_format( $hits ) . ' hits / ' . number_format( (int) $misses ) . ' misses' : 'Not exposed by this backend',
                    'value' => null !== $ratio ? number_format( $ratio, 1 ) . '%' : '—',
                ),
                array(
                    'label' => 'Object Cache',
                    'meta'  => null !== $groups ? count( $groups ) . ' groups, ~' . size_format( $size_total ) : 'Contents not readable',
                    'value' => null !== $groups ? number_format( $items_total ) . ' items' : '—',
                ),
                array(
                    'label' => 'Transients',
                    'meta'  => number_format( $transients['expired'] ) . ' expired',
                    'value' => number_format( $transients['total'] ),
                ),
                array(
                    'label' => 'Autoloaded Options',
                    'meta'  => size_format( $options['size'] ) . ' loaded every request',
                    'value' => number_format( $options['total'] ),
                ),
            ),
        );
    }

    /**
     * Per-group contents of the object cache: item count and approximate
     * (serialized) size. The `cache` property is private in core's
     * WP_Object_Cache, hence the reflection; returns null when the active
     * backend keeps no readable array there.
     *
     * @return array[]|null
     */
    private function get_groups() {
        global $wp_object_cache;

        if ( ! is_object( $wp_object_cache ) ) {
            return null;
        }

        try {
            $property = new \ReflectionProperty( $wp_object_cache, 'cache' );
            $property->setAccessible( true );

            $cache = $property->getValue( $wp_object_cache );
        } catch ( \ReflectionException $e ) {
            return null;
        }

        if ( ! is_array( $cache ) ) {
            return null;
        }

        $groups = array();

        foreach ( $cache as $group => $entries ) {
            if ( ! is_array( $entries ) ) {
                continue;
            }

            $size = 0;

            foreach ( $entries as $value ) {
                try {
                    $size += strlen( serialize( $value ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- size estimate only, never unserialized.
                } catch ( \Throwable $e ) {
                    continue;
                }
            }

            $groups[] = array(
                'group' => (string) $group,
                'items' => count( $entries ),
                'size'  => $size,
            );
        }

        usort( $groups, static function ( $a, $b ) {
            return strcmp( $a['group'], $b['group'] );
        } );

        return $groups;
    }

    /**
     * Transients stored in the options table, with their expiry state.
     * With a persistent object cache active, transients live there instead
     * and this list only reflects leftover database rows.
     *
     * @return array
     */
    private function get_transients() : array {
        global $wpdb;

        // phpcs:disable WordPress.DB.DirectDatabaseQuery -- introspection of raw option rows.
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, LENGTH( option_value ) AS size, option_value FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                $wpdb->esc_like( '_transient_' ) . '%',
                $wpdb->esc_like( '_site_transient_' ) . '%'
            )
        );
        // phpcs:enable

        $timeouts = array();
        $values   = array();
        $now      = time();

        foreach ( (array) $rows as $row ) {
            $name = (string) $row->option_name;

            foreach ( array( '_site_transient_timeout_' => 'network', '_transient_timeout_' => 'site' ) as $prefix => $scope ) {
                if ( 0 === strpos( $name, $prefix ) ) {
                    $timeouts[ $scope . ':' . substr( $name, strlen( $prefix ) ) ] = (int) $row->option_value;

                    continue 2;
                }
            }

            foreach ( array( '_site_transient_' => 'network', '_transient_' => 'site' ) as $prefix => $scope ) {
                if ( 0 === strpos( $name, $prefix ) ) {
                    $values[] = array(
                        'name'  => substr( $name, strlen( $prefix ) ),
                        'scope' => $scope,
                        'size'  => (int) $row->size,
                    );

                    continue 2;
                }
            }
        }

        $expired = 0;

        foreach ( $values as &$value ) {
            $key    = $value['scope'] . ':' . $value['name'];
            $expiry = isset( $timeouts[ $key ] ) ? $timeouts[ $key ] : null;

            if ( null === $expiry ) {
                $value['status']  = 'none';
                $value['expires'] = '—';
            } elseif ( $expiry <= $now ) {
                $value['status']  = 'expired';
                $value['expires'] = human_time_diff( $expiry, $now ) . ' ago';

                $expired++;
            } else {
                $value['status']  = 'active';
                $value['expires'] = 'in ' . human_time_diff( $now, $expiry );
            }
        }

        unset( $value );

        $total = count( $values );

        if ( $total > self::MAX_TRANSIENTS ) {
            usort( $values, static function ( $a, $b ) {
                return $b['size'] <=> $a['size'];
            } );

            $values = array_slice( $values, 0, self::MAX_TRANSIENTS );
        }

        return array(
            'entries' => $values,
            'total'   => $total,
            'expired' => $expired,
        );
    }

    /**
     * The autoloaded options (`alloptions`) — count, combined size, and the
     * largest ones. These load on every request, so size here is a real
     * performance signal.
     *
     * @return array
     */
    private function get_autoloaded_options() : array {
        $alloptions = wp_load_alloptions();
        $entries    = array();
        $size       = 0;

        foreach ( (array) $alloptions as $name => $value ) {
            $length = is_scalar( $value ) ? strlen( (string) $value ) : strlen( maybe_serialize( $value ) );
            $size  += $length;

            $entries[] = array(
                'name' => (string) $name,
                'size' => $length,
            );
        }

        usort( $entries, static function ( $a, $b ) {
            return $b['size'] <=> $a['size'];
        } );

        return array(
            'entries' => array_slice( $entries, 0, self::MAX_OPTIONS ),
            'total'   => count( $entries ),
            'size'    => $size,
        );
    }

}
