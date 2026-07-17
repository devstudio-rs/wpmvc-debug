<?php

namespace wpmvc\debug\tabs;

use wpmvc\base\App;

/**
 * Components tab — every declared component across all applications, with
 * loaded/lazy status, bootstrap flag and the declared config (expandable
 * per row as an accordion).
 */
class Components_Tab extends Tab {

    public function get_id() : string {
        return 'components';
    }

    public function get_label() : string {
        return 'Components';
    }

    public function get_icon() : string {
        return '<path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5 8 5.961 14.154 3.5 8.186 1.113zM15 4.239l-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>';
    }

    public function get_badge() {
        $count = 0;

        foreach ( App::instances() as $app ) {
            $count += count( $app->get_component_ids() );
        }

        return $count;
    }

    public function get_data() : array {
        $apps       = array();
        $components = array();

        foreach ( App::instances() as $class => $app ) {
            $config = $app->get_config();
            $name   = ! empty( $config['name'] ) ? $config['name'] : $class;

            $apps[] = $name;
            $loaded = $app->get_loaded_component_ids();

            foreach ( $app->get_component_configs() as $id => $component_config ) {
                $component_class = isset( $component_config['class'] ) ? $component_config['class'] : '—';
                unset( $component_config['class'] );

                $components[] = array(
                    'id'        => $id,
                    'class'     => $component_class,
                    'app'       => $name,
                    'loaded'    => in_array( $id, $loaded, true ),
                    'bootstrap' => in_array( $id, $app->bootstrap, true ),
                    'config'    => $this->format_config( $component_config ),
                );
            }
        }

        return array(
            'apps'       => $apps,
            'components' => $components,
            'stats'      => array(
                array( 'label' => 'Total Components', 'meta' => 'Across all applications', 'value' => count( $components ) ),
                array( 'label' => 'Loaded', 'meta' => 'Already instantiated', 'value' => count( array_filter( wp_list_pluck( $components, 'loaded' ) ) ) ),
                array( 'label' => 'Lazy', 'meta' => 'Not yet initialized', 'value' => count( $components ) - count( array_filter( wp_list_pluck( $components, 'loaded' ) ) ) ),
                array( 'label' => 'Bootstrap', 'meta' => 'Eagerly loaded on init', 'value' => count( array_filter( wp_list_pluck( $components, 'bootstrap' ) ) ) ),
            ),
        );
    }

    /**
     * Flatten a declared component config for display: scalars as-is,
     * arrays as truncated JSON, secret-looking keys masked.
     *
     * @param array $config
     * @return array<string, string>
     */
    private function format_config( array $config ) : array {
        $formatted = array();

        foreach ( $config as $key => $value ) {
            if ( preg_match( '/password|secret|token|auth|_key/i', (string) $key ) ) {
                $formatted[ $key ] = '••••••';
                continue;
            }

            if ( is_array( $value ) ) {
                $encoded = wp_json_encode( $value );

                $formatted[ $key ] = strlen( $encoded ) > 120 ? substr( $encoded, 0, 120 ) . '…' : $encoded;
                continue;
            }

            if ( is_bool( $value ) ) {
                $formatted[ $key ] = $value ? 'true' : 'false';
                continue;
            }

            $formatted[ $key ] = (string) $value;
        }

        return $formatted;
    }

}
