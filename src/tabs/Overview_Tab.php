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

    public function get_icon() : string {
        return '<path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4H2.5z"/>';
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
