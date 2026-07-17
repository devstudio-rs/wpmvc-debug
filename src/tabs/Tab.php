<?php

namespace wpmvc\debug\tabs;

use wpmvc\debug\Debug;

/**
 * Base class for debugger tabs.
 *
 * A tab provides its sidebar entry (label + optional count badge) and the
 * data for its view. The view file is resolved by the tab ID:
 * `views/tabs/{id}.php`, and receives the data as `$data`.
 */
abstract class Tab {

    /**
     * Tab ID — used for the nav/pane wiring (`data-wpmvc-debug-tab`) and
     * as the view file name.
     *
     * @return string
     */
    abstract public function get_id() : string;

    /**
     * Label shown in the sidebar.
     *
     * @return string
     */
    abstract public function get_label() : string;

    /**
     * Optional count badge for the sidebar entry; null renders no badge.
     *
     * @return int|null
     */
    public function get_badge() {
        return null;
    }

    /**
     * Inner SVG markup for the sidebar icon (paths for a 16×16 viewBox);
     * empty string renders no icon.
     *
     * @return string
     */
    public function get_icon() : string {
        return '';
    }

    /**
     * Data passed to the view as `$data`.
     *
     * @return array
     */
    abstract public function get_data() : array;

    /**
     * Register this tab's AJAX handlers. Called for every request (including
     * admin-ajax) during Debug init. Override in tabs that need endpoints.
     *
     * @return void
     */
    public function register_ajax() {}

    /**
     * Guard an AJAX handler: admins only, nonce-checked. Terminates the
     * request with a JSON error when either check fails.
     *
     * @return void
     */
    protected function verify_ajax() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Forbidden' ), 403 );
        }

        check_ajax_referer( Debug::NONCE, 'nonce' );
    }

    /**
     * Render the tab's view.
     *
     * @return void
     */
    public function render() {
        $data = $this->get_data();

        require Debug::$root . '/views/tabs/' . $this->get_id() . '.php';
    }

}
