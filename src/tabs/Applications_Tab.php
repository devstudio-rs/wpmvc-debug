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

    public function get_icon() : string {
        return '<path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>';
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
