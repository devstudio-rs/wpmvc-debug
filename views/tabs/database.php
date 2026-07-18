<?php
/**
 * Database tab view — query list with timing as an accordion.
 *
 * @var array $data See tabs\Database_Tab::get_data().
 */

$wpmvc_debug_type_badges = array(
    'SELECT' => 'success',
    'INSERT' => 'info',
    'UPDATE' => 'warning',
    'DELETE' => 'danger',
    'OTHER'  => 'secondary',
);

?>
<div class="wpmvc-d-flex wpmvc-align-items-start wpmvc-flex-wrap wpmvc-gap-2 wpmvc-mb-4">
    <div class="wpmvc-me-auto">
        <h5 class="wpmvc-mb-1 wpmvc-d-flex wpmvc-align-items-center wpmvc-gap-2">
            <?php echo esc_html( 'Database' ); ?>
            <span class="wpmvc-badge wpmvc-rounded-pill wpmvc-text-bg-primary"><?php echo esc_html( count( $data['queries'] ) ); ?></span>
        </h5>
        <p class="wpmvc-text-body-secondary wpmvc-mb-0"><?php echo esc_html( 'Database connection, queries and performance details.' ); ?></p>
    </div>

    <input
        type="search"
        class="wpmvc-form-control wpmvc-form-control-sm"
        style="width: 14rem;"
        placeholder="<?php echo esc_attr( 'Search queries…' ); ?>"
        data-wpmvc-debug-search
    >

    <select class="wpmvc-form-select wpmvc-form-select-sm wpmvc-w-auto" data-wpmvc-debug-group-filter>
        <option value=""><?php echo esc_html( 'All Queries' ); ?></option>
        <?php foreach ( array_keys( $wpmvc_debug_type_badges ) as $wpmvc_debug_type ) : ?>
            <option value="<?php echo esc_attr( $wpmvc_debug_type ); ?>"><?php echo esc_html( $wpmvc_debug_type ); ?></option>
        <?php endforeach; ?>
    </select>
</div>

<?php if ( ! $data['enabled'] ) : ?>
    <div class="wpmvc-alert wpmvc-alert-warning wpmvc-small" role="alert">
        <?php echo esc_html( 'Query logging is off — SAVEQUERIES is explicitly set to false. Remove that define, or set it to true, to capture queries:' ); ?>
        <code>define( 'SAVEQUERIES', true );</code>
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

<?php if ( $data['queries'] ) : ?>
    <div class="wpmvc-debug-components-head wpmvc-text-body-secondary wpmvc-small">
        <span class="wpmvc-debug-queries-row">
            <span>#</span>
            <span><?php echo esc_html( 'Type' ); ?></span>
            <span><?php echo esc_html( 'Query' ); ?></span>
            <span class="wpmvc-text-end">
                <button type="button" class="wpmvc-debug-sort" data-wpmvc-debug-sort="time">
                    <?php echo esc_html( 'Time' ); ?>
                    <svg width="11" height="11" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path class="wpmvc-debug-sort-up" d="M11.5 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L11 2.707V14.5a.5.5 0 0 0 .5.5z"/>
                        <path class="wpmvc-debug-sort-down" d="M4.5 1a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L4 13.293V1.5a.5.5 0 0 1 .5-.5z"/>
                    </svg>
                </button>
            </span>
        </span>
    </div>

    <div class="wpmvc-accordion">
        <?php foreach ( $data['queries'] as $index => $query ) : ?>
            <div
                class="wpmvc-accordion-item"
                data-wpmvc-debug-item="<?php echo esc_attr( $query['type'] ); ?>"
                data-wpmvc-debug-sort-time="<?php echo esc_attr( sprintf( '%.6F', $query['time'] ) ); ?>"
            >
                <h2 class="wpmvc-accordion-header">
                    <button type="button" class="wpmvc-accordion-button wpmvc-collapsed" data-wpmvc-debug-accordion>
                        <span class="wpmvc-debug-queries-row">
                            <span class="wpmvc-text-body-secondary"><?php echo esc_html( $index + 1 ); ?></span>
                            <span><span class="wpmvc-badge wpmvc-bg-<?php echo esc_attr( $wpmvc_debug_type_badges[ $query['type'] ] ); ?>-subtle wpmvc-text-<?php echo esc_attr( $wpmvc_debug_type_badges[ $query['type'] ] ); ?>-emphasis"><?php echo esc_html( $query['type'] ); ?></span></span>
                            <span class="wpmvc-text-truncate"><code><?php echo esc_html( $query['sql'] ); ?></code></span>
                            <span class="wpmvc-text-end wpmvc-fw-medium<?php echo $query['slow'] ? ' wpmvc-text-danger' : ''; ?>"><?php echo esc_html( number_format( $query['time'] * 1000, 2 ) ); ?> ms</span>
                        </span>
                    </button>
                </h2>

                <div class="wpmvc-accordion-collapse wpmvc-collapse">
                    <div class="wpmvc-accordion-body">
                        <div class="wpmvc-fw-semibold wpmvc-mb-2"><?php echo esc_html( 'Query' ); ?></div>
                        <pre class="wpmvc-debug-sql wpmvc-small"><code><?php echo esc_html( $query['sql'] ); ?></code></pre>

                        <div class="wpmvc-row wpmvc-g-4 wpmvc-mt-0">
                            <div class="wpmvc-col-12 wpmvc-col-lg-4">
                                <div class="wpmvc-fw-semibold wpmvc-mb-2"><?php echo esc_html( 'Timing' ); ?></div>
                                <div class="wpmvc-d-flex wpmvc-justify-content-between wpmvc-gap-3 wpmvc-py-1 wpmvc-small">
                                    <span class="wpmvc-text-body-secondary"><?php echo esc_html( 'Time' ); ?></span>
                                    <span class="wpmvc-fw-medium<?php echo $query['slow'] ? ' wpmvc-text-danger' : ''; ?>"><?php echo esc_html( number_format( $query['time'] * 1000, 3 ) ); ?> ms</span>
                                </div>
                                <div class="wpmvc-d-flex wpmvc-justify-content-between wpmvc-gap-3 wpmvc-py-1 wpmvc-small">
                                    <span class="wpmvc-text-body-secondary"><?php echo esc_html( 'Slow' ); ?></span>
                                    <span class="wpmvc-fw-medium"><?php echo esc_html( $query['slow'] ? 'Yes' : 'No' ); ?></span>
                                </div>
                            </div>

                            <div class="wpmvc-col-12 wpmvc-col-lg-8">
                                <div class="wpmvc-fw-semibold wpmvc-mb-2"><?php echo esc_html( 'Call Stack' ); ?></div>
                                <ol class="wpmvc-small wpmvc-text-body-secondary wpmvc-mb-0 wpmvc-ps-3">
                                    <?php foreach ( $query['chain'] as $call ) : ?>
                                        <li><code><?php echo esc_html( $call ); ?></code></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
