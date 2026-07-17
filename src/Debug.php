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

    const VERSION = '0.1.0';

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
     * @param array $config
     */
    public function __construct( $config = array() ) {
        parent::__construct( $config );

        static::$root = dirname( __DIR__ );
        static::$web  = home_url( str_replace( ABSPATH, '', static::$root ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_debugger' ) );
    }

    /**
     * Enqueue the compiled toolbar assets.
     *
     * @return void
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'wpmvc-debug', static::$web . '/assets/css/main.css', array(), self::VERSION );
        wp_enqueue_script( 'wpmvc-debug', static::$web . '/assets/js/main.js', array(), self::VERSION, true );
    }

    /**
     * Render the debugger UI (floating button + panel) in the footer.
     *
     * @return void
     */
    public function render_debugger() {
        require static::$root . '/views/debugger.php';
    }

}
