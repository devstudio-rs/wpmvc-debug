<?php

namespace wpmvc\debug\tabs;

use wpmvc\base\App;

/**
 * Applications tab — every initialized WPMVC application instance
 * (`App::instances()`, since wpmvc 1.7.0), with its declared vs. loaded
 * components, bootstrap list and registered routes.
 */
class Applications_Tab extends Tab {

    public function get_id() : string {
        return 'applications';
    }

    public function get_label() : string {
        return 'Applications';
    }

    public function get_badge() {
        return count( App::instances() );
    }

    public function get_data() : array {
        $apps = array();

        foreach ( App::instances() as $class => $app ) {
            $config = $app->get_config();
            $loaded = $app->get_loaded_component_ids();

            // Only report routes when the router is already instantiated —
            // reading it otherwise would alter the app's state.
            $routes = in_array( 'router', $loaded, true ) ? count( $app->router->routes ) : null;

            $root = isset( $config['aliases']['@root'] ) ? $config['aliases']['@root'] : null;

            $apps[] = array(
                'class'      => $class,
                'name'       => ! empty( $config['name'] ) ? $config['name'] : $class,
                'domain'     => $config['domain'],
                'root'       => $root ? str_replace( untrailingslashit( ABSPATH ), '', $root ) : null,
                'components' => $app->get_component_ids(),
                'loaded'     => $loaded,
                'bootstrap'  => $app->bootstrap,
                'routes'     => $routes,
            );
        }

        return array(
            'apps' => $apps,
        );
    }

}
