<?php

namespace wpmvc\debug\tabs;

/**
 * Overview tab — general information about the current request.
 * Values are read directly for now; they move to collectors as those land.
 */
class Overview_Tab extends Tab {

    public function get_id() : string {
        return 'overview';
    }

    public function get_label() : string {
        return 'Overview';
    }

    public function get_data() : array {
        $method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
        $path   = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
        $user   = wp_get_current_user();

        return array(
            'cards' => array(
                array(
                    'label' => 'Request',
                    'value' => trim( $method . ' ' . $path ),
                    'meta'  => date_i18n( 'H:i:s' ),
                ),
                array(
                    'label' => 'User',
                    'value' => $user->exists() ? $user->user_login : 'Guest',
                    'meta'  => $user->exists() ? implode( ', ', $user->roles ) : '—',
                ),
                array(
                    'label' => 'Environment',
                    'value' => wp_get_environment_type(),
                    'meta'  => 'Debug ' . ( WP_DEBUG ? 'true' : 'false' ),
                ),
                array(
                    'label' => 'Versions',
                    'value' => 'PHP ' . PHP_VERSION,
                    'meta'  => 'WordPress ' . get_bloginfo( 'version' ),
                ),
                array(
                    'label' => 'Performance',
                    'value' => size_format( memory_get_peak_usage( true ) ),
                    'meta'  => number_format( (float) timer_stop() * 1000, 1 ) . ' ms',
                ),
            ),
        );
    }

}
