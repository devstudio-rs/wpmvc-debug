<?php
/**
 * Cache tab view — object cache, transients and autoloaded options, as
 * sub-tabs.
 *
 * @var array $data See tabs\Cache_Tab::get_data().
 */

$wpmvc_debug_status_badges = array(
    'active'  => 'success',
    'expired' => 'danger',
    'none'    => 'secondary',
);

$wpmvc_debug_status_labels = array(
    'active'  => 'Active',
    'expired' => 'Expired',
    'none'    => 'No Expiry',
);

/**
 * Render a sortable column-header button (shared with Events/Database).
 */
$wpmvc_debug_sort_button = static function ( $label, $key ) {
    ?>
    <button type="button" class="wpmvc-debug-sort" data-wpmvc-debug-sort="<?php echo esc_attr( $key ); ?>">
        <?php echo esc_html( $label ); ?>
        <svg width="11" height="11" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
            <path class="wpmvc-debug-sort-up" d="M11.5 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L11 2.707V14.5a.5.5 0 0 0 .5.5z"/>
            <path class="wpmvc-debug-sort-down" d="M4.5 1a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L4 13.293V1.5a.5.5 0 0 1 .5-.5z"/>
        </svg>
    </button>
    <?php
};

?>
<div class="wpmvc-me-auto wpmvc-mb-4">
    <h5 class="wpmvc-mb-1"><?php echo esc_html( 'Cache' ); ?></h5>
    <p class="wpmvc-text-body-secondary wpmvc-mb-0"><?php echo esc_html( 'The object cache, transients and autoloaded options for this request.' ); ?></p>
</div>

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

<div data-wpmvc-debug-subtabs>
    <nav class="wpmvc-nav wpmvc-nav-tabs wpmvc-mb-3">
        <button type="button" class="wpmvc-nav-link wpmvc-active" data-wpmvc-debug-subtab="object">
            <?php echo esc_html( 'Object Cache' ); ?>
        </button>
        <button type="button" class="wpmvc-nav-link" data-wpmvc-debug-subtab="transients">
            <?php echo esc_html( 'Transients' ); ?>
            <span class="wpmvc-badge wpmvc-rounded-pill wpmvc-text-bg-primary wpmvc-ms-1"><?php echo esc_html( $data['transients']['total'] ); ?></span>
        </button>
        <button type="button" class="wpmvc-nav-link" data-wpmvc-debug-subtab="options">
            <?php echo esc_html( 'Autoloaded Options' ); ?>
        </button>
    </nav>

    <div data-wpmvc-debug-subpane="object">
        <p class="wpmvc-text-body-secondary wpmvc-small wpmvc-mb-3">
            <?php echo esc_html( 'Backend:' ); ?>
            <code><?php echo esc_html( $data['class'] ? $data['class'] : 'unknown' ); ?></code>
            <?php if ( $data['external'] ) : ?>
                <span class="wpmvc-badge wpmvc-bg-info-subtle wpmvc-text-info-emphasis"><?php echo esc_html( 'Persistent drop-in' ); ?></span>
            <?php else : ?>
                <span class="wpmvc-badge wpmvc-text-bg-secondary"><?php echo esc_html( 'In-memory, per request' ); ?></span>
            <?php endif; ?>
        </p>

        <?php if ( null === $data['groups'] ) : ?>
            <div class="wpmvc-alert wpmvc-alert-info wpmvc-small" role="alert">
                <?php echo esc_html( 'The active object-cache backend does not keep a readable in-memory store, so its contents cannot be listed.' ); ?>
            </div>
        <?php else : ?>
            <div class="wpmvc-d-flex wpmvc-flex-wrap wpmvc-align-items-center wpmvc-gap-2 wpmvc-mb-3">
                <input
                    type="search"
                    class="wpmvc-form-control wpmvc-form-control-sm"
                    style="width: 14rem;"
                    placeholder="<?php echo esc_attr( 'Search groups…' ); ?>"
                    data-wpmvc-debug-search
                >

                <?php if ( $data['external'] && current_user_can( 'manage_options' ) ) : ?>
                    <button
                        type="button"
                        class="wpmvc-btn wpmvc-btn-sm wpmvc-btn-outline-danger wpmvc-ms-auto"
                        data-wpmvc-debug-cache-action="flush-object"
                    >
                        <?php echo esc_html( 'Flush Object Cache' ); ?>
                    </button>
                <?php endif; ?>
            </div>

            <div class="wpmvc-table-responsive">
                <table class="wpmvc-table wpmvc-table-sm wpmvc-small wpmvc-align-middle">
                    <thead>
                        <tr class="wpmvc-text-body-secondary">
                            <th class="wpmvc-fw-normal"><?php echo esc_html( 'Group' ); ?></th>
                            <th class="wpmvc-fw-normal wpmvc-text-end"><?php $wpmvc_debug_sort_button( 'Items', 'items' ); ?></th>
                            <th class="wpmvc-fw-normal wpmvc-text-end"><?php $wpmvc_debug_sort_button( 'Size', 'size' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $data['groups'] as $group ) : ?>
                            <tr
                                data-wpmvc-debug-item="group"
                                data-wpmvc-debug-sort-items="<?php echo esc_attr( $group['items'] ); ?>"
                                data-wpmvc-debug-sort-size="<?php echo esc_attr( $group['size'] ); ?>"
                            >
                                <td><code><?php echo esc_html( $group['group'] ); ?></code></td>
                                <td class="wpmvc-text-end"><?php echo esc_html( number_format( $group['items'] ) ); ?></td>
                                <td class="wpmvc-text-end"><?php echo esc_html( $group['size'] > 0 ? size_format( $group['size'] ) : '0 B' ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div data-wpmvc-debug-subpane="transients" class="wpmvc-d-none">
        <div class="wpmvc-d-flex wpmvc-flex-wrap wpmvc-align-items-center wpmvc-gap-2 wpmvc-mb-3">
            <input
                type="search"
                class="wpmvc-form-control wpmvc-form-control-sm"
                style="width: 14rem;"
                placeholder="<?php echo esc_attr( 'Search transients…' ); ?>"
                data-wpmvc-debug-search
            >

            <select class="wpmvc-form-select wpmvc-form-select-sm wpmvc-w-auto" data-wpmvc-debug-group-filter>
                <option value=""><?php echo esc_html( 'All Statuses' ); ?></option>
                <?php foreach ( $wpmvc_debug_status_labels as $wpmvc_debug_status => $wpmvc_debug_label ) : ?>
                    <option value="<?php echo esc_attr( $wpmvc_debug_status ); ?>"><?php echo esc_html( $wpmvc_debug_label ); ?></option>
                <?php endforeach; ?>
            </select>

            <?php if ( current_user_can( 'manage_options' ) && $data['transients']['expired'] > 0 ) : ?>
                <button
                    type="button"
                    class="wpmvc-btn wpmvc-btn-sm wpmvc-btn-outline-danger wpmvc-ms-auto"
                    data-wpmvc-debug-cache-action="delete-expired-transients"
                >
                    <?php echo esc_html( 'Delete Expired' ); ?>
                </button>
            <?php endif; ?>
        </div>

        <?php if ( wp_using_ext_object_cache() ) : ?>
            <div class="wpmvc-alert wpmvc-alert-info wpmvc-small" role="alert">
                <?php echo esc_html( 'A persistent object cache is active — transients are stored there, so this list only shows leftover database rows.' ); ?>
            </div>
        <?php endif; ?>

        <?php if ( empty( $data['transients']['entries'] ) ) : ?>
            <p class="wpmvc-text-body-secondary wpmvc-small wpmvc-mb-0"><?php echo esc_html( 'No transients in the database.' ); ?></p>
        <?php else : ?>
            <?php if ( $data['transients']['total'] > count( $data['transients']['entries'] ) ) : ?>
                <p class="wpmvc-text-body-secondary wpmvc-small wpmvc-mb-3">
                    <?php echo esc_html( sprintf( 'Showing the %d largest of %d transients.', count( $data['transients']['entries'] ), $data['transients']['total'] ) ); ?>
                </p>
            <?php endif; ?>

            <div class="wpmvc-table-responsive">
                <table class="wpmvc-table wpmvc-table-sm wpmvc-small wpmvc-align-middle">
                    <thead>
                        <tr class="wpmvc-text-body-secondary">
                            <th class="wpmvc-fw-normal"><?php echo esc_html( 'Transient' ); ?></th>
                            <th class="wpmvc-fw-normal"><?php echo esc_html( 'Scope' ); ?></th>
                            <th class="wpmvc-fw-normal"><?php echo esc_html( 'Status' ); ?></th>
                            <th class="wpmvc-fw-normal"><?php echo esc_html( 'Expires' ); ?></th>
                            <th class="wpmvc-fw-normal wpmvc-text-end"><?php $wpmvc_debug_sort_button( 'Size', 'size' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $data['transients']['entries'] as $wpmvc_debug_transient ) : ?>
                            <?php $wpmvc_debug_badge = $wpmvc_debug_status_badges[ $wpmvc_debug_transient['status'] ]; ?>
                            <tr
                                data-wpmvc-debug-item="<?php echo esc_attr( $wpmvc_debug_transient['status'] ); ?>"
                                data-wpmvc-debug-sort-size="<?php echo esc_attr( $wpmvc_debug_transient['size'] ); ?>"
                            >
                                <td class="wpmvc-text-break"><code><?php echo esc_html( $wpmvc_debug_transient['name'] ); ?></code></td>
                                <td><span class="wpmvc-badge wpmvc-text-bg-secondary wpmvc-text-capitalize"><?php echo esc_html( $wpmvc_debug_transient['scope'] ); ?></span></td>
                                <td><span class="wpmvc-badge wpmvc-bg-<?php echo esc_attr( $wpmvc_debug_badge ); ?>-subtle wpmvc-text-<?php echo esc_attr( $wpmvc_debug_badge ); ?>-emphasis"><?php echo esc_html( $wpmvc_debug_status_labels[ $wpmvc_debug_transient['status'] ] ); ?></span></td>
                                <td class="wpmvc-text-body-secondary"><?php echo esc_html( $wpmvc_debug_transient['expires'] ); ?></td>
                                <td class="wpmvc-text-end"><?php echo esc_html( size_format( $wpmvc_debug_transient['size'] ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div data-wpmvc-debug-subpane="options" class="wpmvc-d-none">
        <p class="wpmvc-text-body-secondary wpmvc-small wpmvc-mb-3">
            <?php echo esc_html( sprintf( 'All %d autoloaded options load on every request (%s in total). The %d largest:', $data['options']['total'], size_format( $data['options']['size'] ), count( $data['options']['entries'] ) ) ); ?>
        </p>

        <div class="wpmvc-d-flex wpmvc-flex-wrap wpmvc-align-items-center wpmvc-gap-2 wpmvc-mb-3">
            <input
                type="search"
                class="wpmvc-form-control wpmvc-form-control-sm"
                style="width: 14rem;"
                placeholder="<?php echo esc_attr( 'Search options…' ); ?>"
                data-wpmvc-debug-search
            >
        </div>

        <div class="wpmvc-table-responsive">
            <table class="wpmvc-table wpmvc-table-sm wpmvc-small wpmvc-align-middle">
                <thead>
                    <tr class="wpmvc-text-body-secondary">
                        <th class="wpmvc-fw-normal"><?php echo esc_html( 'Option' ); ?></th>
                        <th class="wpmvc-fw-normal wpmvc-text-end"><?php $wpmvc_debug_sort_button( 'Size', 'size' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $data['options']['entries'] as $wpmvc_debug_option ) : ?>
                        <tr
                            data-wpmvc-debug-item="option"
                            data-wpmvc-debug-sort-size="<?php echo esc_attr( $wpmvc_debug_option['size'] ); ?>"
                        >
                            <td class="wpmvc-text-break"><code><?php echo esc_html( $wpmvc_debug_option['name'] ); ?></code></td>
                            <td class="wpmvc-text-end"><?php echo esc_html( size_format( $wpmvc_debug_option['size'] ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
