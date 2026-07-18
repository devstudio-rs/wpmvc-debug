<?php
/**
 * Events tab view — hooks fired during the request, as sub-tabs:
 * the aggregated event list and a timeline.
 *
 * @var array $data See tabs\Events_Tab::get_data().
 */

$wpmvc_debug_type_badges = array(
    'action' => 'success',
    'filter' => 'info',
);

$wpmvc_debug_duration_ms = $data['duration'] * 1000;

?>
<div class="wpmvc-me-auto wpmvc-mb-4">
    <h5 class="wpmvc-mb-1 wpmvc-d-flex wpmvc-align-items-center wpmvc-gap-2">
        <?php echo esc_html( 'Events' ); ?>
        <span class="wpmvc-badge wpmvc-rounded-pill wpmvc-text-bg-primary"><?php echo esc_html( count( $data['events'] ) ); ?></span>
    </h5>
    <p class="wpmvc-text-body-secondary wpmvc-mb-1"><?php echo esc_html( 'WordPress actions and filters fired during this request.' ); ?></p>
    <p class="wpmvc-text-body-secondary wpmvc-small wpmvc-mb-0">
        <?php echo esc_html( 'Capture starts when the debug component boots — hooks fired earlier in the bootstrap are not recorded. High-frequency hooks (translations, escaping, option/transient reads) are excluded.' ); ?>
    </p>
</div>

<?php if ( ! $data['started'] ) : ?>
    <div class="wpmvc-alert wpmvc-alert-warning wpmvc-small" role="alert">
        <?php echo esc_html( 'Hook capture did not run for this request.' ); ?>
    </div>
<?php endif; ?>

<?php if ( $data['dropped'] > 0 ) : ?>
    <div class="wpmvc-alert wpmvc-alert-warning wpmvc-small" role="alert">
        <?php echo esc_html( sprintf( 'Recording is capped at %d unique hooks — %d more unique hooks fired but were not recorded.', $data['cap'], $data['dropped'] ) ); ?>
    </div>
<?php endif; ?>

<div class="wpmvc-row wpmvc-row-cols-2 wpmvc-row-cols-xl-4 wpmvc-g-3 wpmvc-mb-4">
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
    <p class="wpmvc-text-body-secondary wpmvc-small wpmvc-mb-0"><?php echo esc_html( 'No events recorded.' ); ?></p>
<?php else : ?>
    <div data-wpmvc-debug-subtabs>
        <nav class="wpmvc-nav wpmvc-nav-tabs wpmvc-mb-3">
            <button type="button" class="wpmvc-nav-link wpmvc-active" data-wpmvc-debug-subtab="all">
                <?php echo esc_html( 'All Events' ); ?>
            </button>
            <button type="button" class="wpmvc-nav-link" data-wpmvc-debug-subtab="timeline">
                <?php echo esc_html( 'Timeline' ); ?>
            </button>
        </nav>

        <div data-wpmvc-debug-subpane="all">
            <div class="wpmvc-d-flex wpmvc-flex-wrap wpmvc-align-items-center wpmvc-gap-2 wpmvc-mb-3">
                <input
                    type="search"
                    class="wpmvc-form-control wpmvc-form-control-sm"
                    style="width: 14rem;"
                    placeholder="<?php echo esc_attr( 'Search events…' ); ?>"
                    data-wpmvc-debug-search
                >

                <select class="wpmvc-form-select wpmvc-form-select-sm wpmvc-w-auto" data-wpmvc-debug-group-filter>
                    <option value=""><?php echo esc_html( 'All Types' ); ?></option>
                    <option value="action"><?php echo esc_html( 'Actions' ); ?></option>
                    <option value="filter"><?php echo esc_html( 'Filters' ); ?></option>
                </select>
            </div>

            <div class="wpmvc-debug-components-head wpmvc-text-body-secondary wpmvc-small">
                <span class="wpmvc-debug-events-row pe-0">
                    <span>#</span>
                    <span><?php echo esc_html( 'Hook' ); ?></span>
                    <span><?php echo esc_html( 'Type' ); ?></span>
                    <span><?php echo esc_html( 'Fires' ); ?></span>
                    <span class="wpmvc-text-end">
                        <button type="button" class="wpmvc-debug-sort" data-wpmvc-debug-sort="time">
                            <?php echo esc_html( 'Time' ); ?>
                            <svg width="11" height="11" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                <path class="wpmvc-debug-sort-up" d="M11.5 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L11 2.707V14.5a.5.5 0 0 0 .5.5z"/>
                                <path class="wpmvc-debug-sort-down" d="M4.5 1a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L4 13.293V1.5a.5.5 0 0 1 .5-.5z"/>
                            </svg>
                        </button>
                    </span>
                    <span class="wpmvc-text-end"><?php echo esc_html( 'First Fired' ); ?></span>
                </span>
            </div>

            <div class="wpmvc-accordion">
                <?php foreach ( $data['events'] as $index => $event ) : ?>
                    <?php $wpmvc_debug_badge = $wpmvc_debug_type_badges[ $event['type'] ]; ?>
                    <div
                        class="wpmvc-accordion-item"
                        data-wpmvc-debug-item="<?php echo esc_attr( $event['type'] ); ?>"
                        data-wpmvc-debug-sort-time="<?php echo esc_attr( sprintf( '%.6F', $event['time'] ) ); ?>"
                    >
                        <h2 class="wpmvc-accordion-header">
                            <button type="button" class="wpmvc-accordion-button wpmvc-collapsed" data-wpmvc-debug-accordion>
                                <span class="wpmvc-debug-events-row">
                                    <span class="wpmvc-text-body-secondary"><?php echo esc_html( $index + 1 ); ?></span>
                                    <span class="wpmvc-text-truncate"><code><?php echo esc_html( $event['hook'] ); ?></code></span>
                                    <span><span class="wpmvc-badge wpmvc-bg-<?php echo esc_attr( $wpmvc_debug_badge ); ?>-subtle wpmvc-text-<?php echo esc_attr( $wpmvc_debug_badge ); ?>-emphasis wpmvc-text-capitalize"><?php echo esc_html( $event['type'] ); ?></span></span>
                                    <span class="wpmvc-text-body-secondary"><?php echo esc_html( $event['count'] ); ?>×</span>
                                    <span class="wpmvc-text-end wpmvc-fw-medium"><?php echo esc_html( number_format( $event['time'] * 1000, 2 ) ); ?> ms</span>
                                    <span class="wpmvc-text-end wpmvc-text-body-secondary"><?php echo esc_html( '+' . number_format( $event['first'] * 1000 ) ); ?> ms</span>
                                </span>
                            </button>
                        </h2>

                        <div class="wpmvc-accordion-collapse wpmvc-collapse">
                            <div class="wpmvc-accordion-body">
                                <div class="wpmvc-row wpmvc-g-4">
                                    <div class="wpmvc-col-12 wpmvc-col-lg-4">
                                        <div class="wpmvc-fw-semibold wpmvc-mb-2"><?php echo esc_html( 'Details' ); ?></div>

                                        <div class="wpmvc-d-flex wpmvc-justify-content-between wpmvc-gap-3 wpmvc-py-1 wpmvc-small">
                                            <span class="wpmvc-text-body-secondary"><?php echo esc_html( 'Type' ); ?></span>
                                            <span class="wpmvc-fw-medium wpmvc-text-capitalize"><?php echo esc_html( $event['type'] ); ?></span>
                                        </div>
                                        <div class="wpmvc-d-flex wpmvc-justify-content-between wpmvc-gap-3 wpmvc-py-1 wpmvc-small">
                                            <span class="wpmvc-text-body-secondary"><?php echo esc_html( 'Fires' ); ?></span>
                                            <span class="wpmvc-fw-medium"><?php echo esc_html( $event['count'] ); ?></span>
                                        </div>
                                        <div class="wpmvc-d-flex wpmvc-justify-content-between wpmvc-gap-3 wpmvc-py-1 wpmvc-small">
                                            <span class="wpmvc-text-body-secondary"><?php echo esc_html( 'Total Time' ); ?></span>
                                            <span class="wpmvc-fw-medium"><?php echo esc_html( number_format( $event['time'] * 1000, 3 ) ); ?> ms</span>
                                        </div>
                                        <div class="wpmvc-d-flex wpmvc-justify-content-between wpmvc-gap-3 wpmvc-py-1 wpmvc-small">
                                            <span class="wpmvc-text-body-secondary"><?php echo esc_html( 'Avg / Fire' ); ?></span>
                                            <span class="wpmvc-fw-medium"><?php echo esc_html( number_format( $event['time'] * 1000 / max( 1, $event['count'] ), 3 ) ); ?> ms</span>
                                        </div>
                                        <div class="wpmvc-d-flex wpmvc-justify-content-between wpmvc-gap-3 wpmvc-py-1 wpmvc-small">
                                            <span class="wpmvc-text-body-secondary"><?php echo esc_html( 'First Fired' ); ?></span>
                                            <span class="wpmvc-fw-medium"><?php echo esc_html( '+' . number_format( $event['first'] * 1000, 1 ) ); ?> ms</span>
                                        </div>
                                    </div>

                                    <div class="wpmvc-col-12 wpmvc-col-lg-8">
                                        <div class="wpmvc-fw-semibold wpmvc-mb-2">
                                            <?php echo esc_html( 'Callbacks' ); ?>
                                            <span class="wpmvc-text-body-secondary wpmvc-fw-normal wpmvc-small"><?php echo esc_html( '(registered at render time)' ); ?></span>
                                        </div>

                                        <?php if ( empty( $event['callbacks'] ) ) : ?>
                                            <p class="wpmvc-text-body-secondary wpmvc-small wpmvc-mb-0"><?php echo esc_html( 'No callbacks registered.' ); ?></p>
                                        <?php else : ?>
                                            <div class="wpmvc-table-responsive">
                                                <table class="wpmvc-table wpmvc-table-sm wpmvc-small wpmvc-mb-0">
                                                    <thead>
                                                        <tr class="wpmvc-text-body-secondary">
                                                            <th class="wpmvc-fw-normal"><?php echo esc_html( 'Priority' ); ?></th>
                                                            <th class="wpmvc-fw-normal"><?php echo esc_html( 'Callback' ); ?></th>
                                                            <th class="wpmvc-fw-normal"><?php echo esc_html( 'Source' ); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ( $event['callbacks'] as $wpmvc_debug_callback ) : ?>
                                                            <tr>
                                                                <td class="wpmvc-text-body-secondary"><?php echo esc_html( $wpmvc_debug_callback['priority'] ); ?></td>
                                                                <td><code><?php echo esc_html( $wpmvc_debug_callback['name'] ); ?></code></td>
                                                                <td>
                                                                    <span class="wpmvc-badge wpmvc-text-bg-secondary"><?php echo esc_html( $wpmvc_debug_callback['source'] ); ?></span>
                                                                    <?php if ( $wpmvc_debug_callback['file'] ) : ?>
                                                                        <div class="wpmvc-text-body-secondary wpmvc-small"><?php echo esc_html( $wpmvc_debug_callback['file'] ); ?></div>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div data-wpmvc-debug-subpane="timeline" class="wpmvc-d-none">
            <div class="wpmvc-d-flex wpmvc-flex-wrap wpmvc-align-items-center wpmvc-gap-3 wpmvc-mb-3 wpmvc-small">
                <input
                    type="search"
                    class="wpmvc-form-control wpmvc-form-control-sm"
                    style="width: 14rem;"
                    placeholder="<?php echo esc_attr( 'Search events…' ); ?>"
                    data-wpmvc-debug-search
                >

                <span class="wpmvc-d-inline-flex wpmvc-align-items-center wpmvc-gap-1 wpmvc-text-body-secondary">
                    <span class="wpmvc-debug-timeline-dot" aria-hidden="true"></span>
                    <?php echo esc_html( 'Actions' ); ?>
                </span>
                <span class="wpmvc-d-inline-flex wpmvc-align-items-center wpmvc-gap-1 wpmvc-text-body-secondary">
                    <span class="wpmvc-debug-timeline-dot wpmvc-debug-timeline-dot-filter" aria-hidden="true"></span>
                    <?php echo esc_html( 'Filters' ); ?>
                </span>

                <span class="wpmvc-ms-auto wpmvc-text-body-secondary">
                    <?php echo esc_html( 'Bar offset = first fire, width = total time in callbacks. Request: ' . number_format( $wpmvc_debug_duration_ms, 1 ) . ' ms' ); ?>
                </span>
            </div>

            <div>
                <?php foreach ( $data['events'] as $event ) : ?>
                    <?php
                    $wpmvc_debug_left  = $wpmvc_debug_duration_ms > 0 ? min( 99.7, $event['first'] * 1000 / $wpmvc_debug_duration_ms * 100 ) : 0;
                    $wpmvc_debug_width = $wpmvc_debug_duration_ms > 0 ? min( 100 - $wpmvc_debug_left, max( 0.3, $event['time'] * 1000 / $wpmvc_debug_duration_ms * 100 ) ) : 0.3;
                    ?>
                    <div class="wpmvc-debug-timeline-row" data-wpmvc-debug-item="<?php echo esc_attr( $event['type'] ); ?>">
                        <span class="wpmvc-text-truncate"><code><?php echo esc_html( $event['hook'] ); ?></code></span>
                        <span class="wpmvc-debug-timeline-track">
                            <span
                                class="wpmvc-debug-timeline-bar<?php echo 'filter' === $event['type'] ? ' wpmvc-debug-timeline-bar-filter' : ''; ?>"
                                style="left: <?php echo esc_attr( number_format( $wpmvc_debug_left, 2, '.', '' ) ); ?>%; width: <?php echo esc_attr( number_format( $wpmvc_debug_width, 2, '.', '' ) ); ?>%;"
                                title="<?php echo esc_attr( $event['hook'] . ' — first at +' . number_format( $event['first'] * 1000, 1 ) . ' ms, ' . number_format( $event['time'] * 1000, 2 ) . ' ms total' ); ?>"
                            ></span>
                        </span>
                        <span class="wpmvc-text-end wpmvc-text-body-secondary wpmvc-small"><?php echo esc_html( number_format( $event['time'] * 1000, 2 ) ); ?> ms</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
