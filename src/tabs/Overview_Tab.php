<?php

namespace wpmvc\debug\tabs;

use wpmvc\base\App;

/**
 * Overview tab — general information about the current request, plus a
 * summary of loaded applications and components.
 * Values are read directly for now; they move to collectors as those land.
 */
class Overview_Tab extends Tab {

    /** Max components shown before the "View all" link. */
    const COMPONENT_PREVIEW = 5;

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

        $icons = array(
            // arrow-left-right
            'request'     => '<path fill-rule="evenodd" d="M1 11.5a.5.5 0 0 0 .5.5h11.793l-3.147 3.146a.5.5 0 0 0 .708.708l4-4a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 11H1.5a.5.5 0 0 0-.5.5zm14-7a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H14.5a.5.5 0 0 1 .5.5z"/>',
            // person
            'user'        => '<path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"/>',
            // sliders
            'environment' => '<path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3h9.05zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8h2.05zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1h9.05z"/>',
            // tag
            'versions'    => '<path d="M2 1a1 1 0 0 0-1 1v4.586a1 1 0 0 0 .293.707l7 7a1 1 0 0 0 1.414 0l4.586-4.586a1 1 0 0 0 0-1.414l-7-7A1 1 0 0 0 6.586 1H2zm4 3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>',
            // lightning-charge
            'performance' => '<path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z"/>',
        );

        $apps       = array();
        $components = array();

        foreach ( App::instances() as $class => $app ) {
            $config = $app->get_config();
            $name   = ! empty( $config['name'] ) ? $config['name'] : $class;
            $root   = isset( $config['aliases']['@root'] ) ? $config['aliases']['@root'] : null;

            $apps[] = array(
                'name'  => $name,
                'class' => $class,
                'root'  => $root ? str_replace( untrailingslashit( ABSPATH ), '', $root ) : null,
            );

            $loaded = $app->get_loaded_component_ids();

            foreach ( $app->get_component_ids() as $id ) {
                $components[] = array(
                    'id'     => $id,
                    'app'    => $name,
                    'loaded' => in_array( $id, $loaded, true ),
                );
            }
        }

        return array(
            'cards' => array(
                array(
                    'label' => 'Request',
                    'icon'  => $icons['request'],
                    'value' => trim( $method . ' ' . $path ),
                    'meta'  => date_i18n( 'H:i:s' ),
                ),
                array(
                    'label' => 'User',
                    'icon'  => $icons['user'],
                    'value' => $user->exists() ? $user->user_login : 'Guest',
                    'meta'  => $user->exists() ? implode( ', ', $user->roles ) : '—',
                ),
                array(
                    'label' => 'Environment',
                    'icon'  => $icons['environment'],
                    'value' => wp_get_environment_type(),
                    'meta'  => 'Debug ' . ( WP_DEBUG ? 'true' : 'false' ),
                ),
                array(
                    'label' => 'Versions',
                    'icon'  => $icons['versions'],
                    'value' => 'PHP ' . PHP_VERSION,
                    'meta'  => 'WordPress ' . get_bloginfo( 'version' ),
                ),
                array(
                    'label' => 'Performance',
                    'icon'  => $icons['performance'],
                    'value' => size_format( memory_get_peak_usage( true ) ),
                    'meta'  => number_format( (float) timer_stop() * 1000, 1 ) . ' ms',
                ),
            ),
            'applications'     => $apps,
            'components'       => array_slice( $components, 0, self::COMPONENT_PREVIEW ),
            'components_total' => count( $components ),
        );
    }

}
