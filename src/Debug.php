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

}
