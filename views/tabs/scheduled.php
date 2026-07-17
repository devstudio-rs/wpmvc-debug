<?php
/**
 * Scheduled Jobs tab view — WP-Cron events with run/delete actions.
 *
 * @var array $data See tabs\Scheduled_Jobs_Tab::get_data().
 */

$wpmvc_debug_now      = time();
$wpmvc_debug_can_edit = current_user_can( 'manage_options' );

?>
<div class="wpmvc-d-flex wpmvc-align-items-start wpmvc-flex-wrap wpmvc-gap-2 wpmvc-mb-4">
    <div class="wpmvc-me-auto">
        <h5 class="wpmvc-mb-1 wpmvc-d-flex wpmvc-align-items-center wpmvc-gap-2">
            <?php echo esc_html__( 'Scheduled Jobs' ); ?>
            <span class="wpmvc-text-body-secondary wpmvc-fs-6 wpmvc-fw-normal">(<?php echo esc_html__( 'WP Cron' ); ?>)</span>
            <span class="wpmvc-badge wpmvc-rounded-pill wpmvc-text-bg-primary"><?php echo esc_html( count( $data['events'] ) ); ?></span>
        </h5>
        <p class="wpmvc-text-body-secondary wpmvc-mb-0"><?php echo esc_html__( 'Monitor and manage WordPress scheduled tasks.' ); ?></p>
    </div>

    <input
        type="search"
        class="wpmvc-form-control wpmvc-form-control-sm"
        style="width: 14rem;"
        placeholder="<?php echo esc_attr__( 'Search jobs…' ); ?>"
        data-wpmvc-debug-search
    >
</div>

<?php if ( $data['cron_disabled'] ) : ?>
    <div class="wpmvc-alert wpmvc-alert-info wpmvc-small" role="alert">
        <?php echo esc_html__( 'DISABLE_WP_CRON is on — jobs do not run automatically on page loads. Use the run action, or a real cron calling wp-cron.php.' ); ?>
    </div>
<?php endif; ?>

<div class="wpmvc-row wpmvc-row-cols-2 wpmvc-row-cols-xl-5 wpmvc-g-3 wpmvc-mb-4">
    <?php foreach ( $data['stats'] as $stat ) : ?>
        <div class="wpmvc-col">
            <div class="wpmvc-card wpmvc-h-100">
                <div class="wpmvc-card-body">
                    <div class="wpmvc-fs-4 wpmvc-fw-bold"><?php echo esc_html( $stat['value'] ); ?></div>
                    <div class="wpmvc-fw-semibold wpmvc-small"><?php echo esc_html( $stat['label'] ); ?></div>
                    <div class="wpmvc-text-body-secondary wpmvc-small"><?php echo esc_html( $stat['meta'] ); ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if ( empty( $data['events'] ) ) : ?>
    <p class="wpmvc-text-body-secondary wpmvc-small"><?php echo esc_html__( 'No scheduled jobs.' ); ?></p>
<?php else : ?>
    <div class="wpmvc-debug-components-head wpmvc-text-body-secondary wpmvc-small">
        <span class="wpmvc-debug-jobs-row">
            <span><?php echo esc_html__( 'Hook' ); ?></span>
            <span><?php echo esc_html__( 'Next Run' ); ?></span>
            <span><?php echo esc_html__( 'Recurrence' ); ?></span>
            <span><?php echo esc_html__( 'Status' ); ?></span>
        </span>
    </div>

    <div class="wpmvc-accordion">
        <?php foreach ( $data['events'] as $job ) : ?>
            <div class="wpmvc-accordion-item" data-wpmvc-debug-item="<?php echo esc_attr( $job['schedule'] ? $job['schedule'] : 'oneoff' ); ?>">
                <h2 class="wpmvc-accordion-header">
                    <button type="button" class="wpmvc-accordion-button wpmvc-collapsed" data-wpmvc-debug-accordion>
                        <span class="wpmvc-debug-jobs-row">
                            <span class="wpmvc-fw-semibold wpmvc-text-truncate" title="<?php echo esc_attr( $job['hook'] ); ?>"><?php echo esc_html( $job['hook'] ); ?></span>
                            <span class="wpmvc-small">
                                <div><?php echo esc_html( $job['due'] ? __( 'due now' ) : human_time_diff( $wpmvc_debug_now, $job['timestamp'] ) ); ?></div>
                                <div class="wpmvc-text-body-secondary"><?php echo esc_html( wp_date( 'Y-m-d H:i:s', $job['timestamp'] ) ); ?></div>
                            </span>
                            <span class="wpmvc-small wpmvc-text-truncate" title="<?php echo esc_attr( $job['recurrence'] ); ?>"><?php echo esc_html( $job['recurrence'] ); ?></span>
                            <span>
                                <?php if ( $job['due'] ) : ?>
                                    <span class="wpmvc-badge wpmvc-bg-warning-subtle wpmvc-text-warning-emphasis"><?php echo esc_html__( 'Due' ); ?></span>
                                <?php else : ?>
                                    <span class="wpmvc-badge wpmvc-bg-info-subtle wpmvc-text-info-emphasis"><?php echo esc_html__( 'Scheduled' ); ?></span>
                                <?php endif; ?>
                            </span>
                        </span>
                    </button>
                </h2>

                <div class="wpmvc-accordion-collapse wpmvc-collapse">
                    <div class="wpmvc-accordion-body">
                        <?php
                        $details = array(
                            'Hook'       => $job['hook'],
                            'Next Run'   => wp_date( 'Y-m-d H:i:s', $job['timestamp'] ) . ' (' . ( $job['due'] ? __( 'due now' ) : human_time_diff( $wpmvc_debug_now, $job['timestamp'] ) ) . ')',
                            'Recurrence' => $job['recurrence'] . ( $job['schedule'] ? ' (' . $job['schedule'] . ')' : '' ),
                            'Arguments'  => empty( $job['args'] ) ? '—' : wp_json_encode( $job['args'] ),
                        );
                        ?>
                        <?php foreach ( $details as $label => $value ) : ?>
                            <div class="wpmvc-d-flex wpmvc-justify-content-between wpmvc-gap-3 wpmvc-py-1 wpmvc-small wpmvc-border-bottom">
                                <span class="wpmvc-text-body-secondary wpmvc-text-nowrap"><?php echo esc_html( $label ); ?></span>
                                <span class="wpmvc-fw-medium wpmvc-text-end wpmvc-text-break"><?php echo esc_html( $value ); ?></span>
                            </div>
                        <?php endforeach; ?>

                        <?php if ( $wpmvc_debug_can_edit ) : ?>
                            <div class="wpmvc-d-flex wpmvc-gap-2 wpmvc-mt-3">
                                <button
                                    type="button"
                                    class="wpmvc-btn wpmvc-btn-sm wpmvc-btn-outline-success wpmvc-d-inline-flex wpmvc-align-items-center wpmvc-gap-1"
                                    data-wpmvc-debug-job-action="run"
                                    data-timestamp="<?php echo esc_attr( $job['timestamp'] ); ?>"
                                    data-hook="<?php echo esc_attr( $job['hook'] ); ?>"
                                    data-key="<?php echo esc_attr( $job['key'] ); ?>"
                                >
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393z"/></svg>
                                    <?php echo esc_html__( 'Run now' ); ?>
                                </button>
                                <button
                                    type="button"
                                    class="wpmvc-btn wpmvc-btn-sm wpmvc-btn-outline-danger wpmvc-d-inline-flex wpmvc-align-items-center wpmvc-gap-1"
                                    data-wpmvc-debug-job-action="delete"
                                    data-timestamp="<?php echo esc_attr( $job['timestamp'] ); ?>"
                                    data-hook="<?php echo esc_attr( $job['hook'] ); ?>"
                                    data-key="<?php echo esc_attr( $job['key'] ); ?>"
                                >
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/></svg>
                                    <?php echo esc_html__( 'Delete' ); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
