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
     * Data passed to the view as `$data`.
     *
     * @return array
     */
    abstract public function get_data() : array;

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
