<?php
/**
 * Logs tab view — WordPress debug log + WPMVC logger, as sub-tabs.
 *
 * @var array $data See tabs\Logs_Tab::get_data().
 */

$wpmvc_debug_level_badges = array(
    'error'             => 'danger',
    'fatal'             => 'danger',
    'parse'             => 'danger',
    'recoverable-fatal' => 'danger',
    'warning'           => 'warning',
    'notice'            => 'info',
    'deprecated'        => 'secondary',
    'info'              => 'info',
    'log'               => 'secondary',
);

/**
 * Render one list of log entries as an accordion. Defined here so both
 * sub-tabs share it.
 */
$wpmvc_debug_render_entries = static function ( array $entries, bool $with_group ) use ( $wpmvc_debug_level_badges ) {
    if ( empty( $entries ) ) {
        echo '<p class="wpmvc-text-body-secondary wpmvc-small wpmvc-mb-0">' . esc_html__( 'No log entries.' ) . '</p>';
        return;
    }
    ?>
    <div class="wpmvc-accordion">
        <?php foreach ( $entries as $entry ) : ?>
            <?php $badge = $wpmvc_debug_level_badges[ $entry['level'] ] ?? 'secondary'; ?>
            <div class="wpmvc-accordion-item" data-wpmvc-debug-item="<?php echo esc_attr( $entry['level'] ); ?>">
                <h2 class="wpmvc-accordion-header">
                    <button type="button" class="wpmvc-accordion-button wpmvc-collapsed" data-wpmvc-debug-accordion>
                        <span class="wpmvc-debug-logs-row">
                            <span class="wpmvc-badge wpmvc-bg-<?php echo esc_attr( $badge ); ?>-subtle wpmvc-text-<?php echo esc_attr( $badge ); ?>-emphasis wpmvc-text-uppercase"><?php echo esc_html( $entry['level'] ); ?></span>

                            <?php if ( $with_group && ! empty( $entry['group'] ) ) : ?>
                                <span class="wpmvc-badge wpmvc-text-bg-secondary"><?php echo esc_html( $entry['group'] ); ?></span>
                            <?php endif; ?>

                            <span class="wpmvc-text-body-secondary wpmvc-text-nowrap wpmvc-small"><?php echo esc_html( $entry['time'] ); ?></span>
                            <span class="wpmvc-debug-logs-msg wpmvc-text-truncate"><?php echo esc_html( $entry['message'] ); ?></span>
                        </span>
                    </button>
                </h2>

                <div class="wpmvc-accordion-collapse wpmvc-collapse">
                    <div class="wpmvc-accordion-body">
                        <pre class="wpmvc-debug-sql wpmvc-small wpmvc-mb-0"><?php echo esc_html( $entry['message'] ); ?></pre>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
};

/**
 * Render the search + level filter controls for a sub-tab, plus a clear
 * button (admins only) targeting the given log.
 */
$wpmvc_debug_render_controls = static function ( array $levels, $target ) {
    ?>
    <div class="wpmvc-d-flex wpmvc-flex-wrap wpmvc-align-items-center wpmvc-gap-2 wpmvc-mb-3">
        <input
            type="search"
            class="wpmvc-form-control wpmvc-form-control-sm"
            style="width: 14rem;"
            placeholder="<?php echo esc_attr__( 'Search logs…' ); ?>"
            data-wpmvc-debug-search
        >

        <select class="wpmvc-form-select wpmvc-form-select-sm wpmvc-w-auto" data-wpmvc-debug-group-filter>
            <option value=""><?php echo esc_html__( 'All Levels' ); ?></option>
            <?php foreach ( array_keys( $levels ) as $level ) : ?>
                <option value="<?php echo esc_attr( $level ); ?>"><?php echo esc_html( ucfirst( $level ) ); ?> (<?php echo esc_html( $levels[ $level ] ); ?>)</option>
            <?php endforeach; ?>
        </select>

        <?php if ( current_user_can( 'manage_options' ) ) : ?>
            <button
                type="button"
                class="wpmvc-btn wpmvc-btn-sm wpmvc-btn-outline-danger wpmvc-ms-auto wpmvc-d-inline-flex wpmvc-align-items-center wpmvc-gap-1"
                data-wpmvc-debug-clear-log
                data-wpmvc-debug-target="<?php echo esc_attr( $target ); ?>"
            >
                <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/></svg>
                <?php echo esc_html__( 'Clear' ); ?>
            </button>
        <?php endif; ?>
    </div>
    <?php
};

?>
<h5 class="wpmvc-mb-1"><?php echo esc_html__( 'Logs' ); ?></h5>
<p class="wpmvc-text-body-secondary wpmvc-mb-4"><?php echo esc_html__( 'Application and WordPress debug logs.' ); ?></p>

<div data-wpmvc-debug-subtabs>
    <nav class="wpmvc-nav wpmvc-nav-tabs wpmvc-mb-3">
        <button type="button" class="wpmvc-nav-link wpmvc-active" data-wpmvc-debug-subtab="wordpress">
            <?php echo esc_html__( 'WordPress' ); ?>
            <span class="wpmvc-badge wpmvc-rounded-pill wpmvc-text-bg-primary wpmvc-ms-1"><?php echo esc_html( count( $data['wordpress']['entries'] ) ); ?></span>
        </button>
        <button type="button" class="wpmvc-nav-link" data-wpmvc-debug-subtab="wpmvc">
            <?php echo esc_html__( 'Logger' ); ?>
            <span class="wpmvc-badge wpmvc-rounded-pill wpmvc-text-bg-primary wpmvc-ms-1"><?php echo esc_html( $data['wpmvc']['count'] ); ?></span>
        </button>
    </nav>

    <div data-wpmvc-debug-subpane="wordpress">
        <?php if ( ! $data['wordpress']['enabled'] ) : ?>
            <div class="wpmvc-alert wpmvc-alert-warning wpmvc-small" role="alert">
                <?php echo esc_html__( 'WordPress debug logging is off. Enable it in wp-config.php:' ); ?>
                <code>define( 'WP_DEBUG_LOG', true );</code>
            </div>
        <?php else : ?>
            <p class="wpmvc-text-body-secondary wpmvc-small wpmvc-mb-3">
                <?php echo esc_html__( 'Source:' ); ?> <code><?php echo esc_html( $data['wordpress']['path'] ); ?></code>
            </p>
            <?php $wpmvc_debug_render_controls( $data['wordpress']['levels'], 'wordpress' ); ?>
            <?php $wpmvc_debug_render_entries( $data['wordpress']['entries'], false ); ?>
        <?php endif; ?>
    </div>

    <div data-wpmvc-debug-subpane="wpmvc" class="wpmvc-d-none">
        <?php if ( ! $data['wpmvc']['available'] ) : ?>
            <div class="wpmvc-alert wpmvc-alert-info wpmvc-small" role="alert">
                <?php echo esc_html__( 'No WPMVC logger component is configured in any application.' ); ?>
            </div>
        <?php else : ?>
            <p class="wpmvc-text-body-secondary wpmvc-small wpmvc-mb-3">
                <?php echo esc_html__( 'Source:' ); ?>
                <?php foreach ( $data['wpmvc']['sources'] as $wpmvc_debug_source ) : ?>
                    <code><?php echo esc_html( $wpmvc_debug_source ); ?></code>
                <?php endforeach; ?>
            </p>
            <?php $wpmvc_debug_render_controls( $data['wpmvc']['levels'], 'logger' ); ?>
            <?php $wpmvc_debug_render_entries( $data['wpmvc']['entries'], true ); ?>
        <?php endif; ?>
    </div>
</div>
