<?php
/**
 * Components tab view — searchable, filterable accordion list of all
 * declared components across applications.
 *
 * @var array $data See tabs\Components_Tab::get_data().
 */

?>
<div class="wpmvc-d-flex wpmvc-align-items-start wpmvc-flex-wrap wpmvc-gap-2 wpmvc-mb-4">
    <div class="wpmvc-me-auto">
        <h5 class="wpmvc-mb-1 wpmvc-d-flex wpmvc-align-items-center wpmvc-gap-2">
            <?php echo esc_html( 'Components' ); ?>
            <span class="wpmvc-badge wpmvc-rounded-pill wpmvc-text-bg-primary"><?php echo esc_html( count( $data['components'] ) ); ?></span>
        </h5>
        <p class="wpmvc-text-body-secondary wpmvc-mb-0"><?php echo esc_html( 'List of all registered components across applications.' ); ?></p>
    </div>

    <input
        type="search"
        class="wpmvc-form-control wpmvc-form-control-sm"
        style="width: 14rem;"
        placeholder="<?php echo esc_attr( 'Search components…' ); ?>"
        data-wpmvc-debug-search
    >

    <select class="wpmvc-form-select wpmvc-form-select-sm wpmvc-w-auto" data-wpmvc-debug-group-filter>
        <option value=""><?php echo esc_html( 'All Applications' ); ?></option>
        <?php foreach ( $data['apps'] as $app ) : ?>
            <option value="<?php echo esc_attr( $app ); ?>"><?php echo esc_html( $app ); ?></option>
        <?php endforeach; ?>
    </select>
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

<div class="wpmvc-debug-components-head wpmvc-text-body-secondary wpmvc-small">
    <span class="wpmvc-debug-components-row">
        <span><?php echo esc_html( 'Component' ); ?></span>
        <span><?php echo esc_html( 'Class' ); ?></span>
        <span><?php echo esc_html( 'Application' ); ?></span>
        <span><?php echo esc_html( 'Bootstrap' ); ?></span>
        <span><?php echo esc_html( 'Status' ); ?></span>
    </span>
</div>

<div class="wpmvc-accordion">
    <?php foreach ( $data['components'] as $index => $component ) : ?>
        <div class="wpmvc-accordion-item" data-wpmvc-debug-item="<?php echo esc_attr( $component['app'] ); ?>">
            <h2 class="wpmvc-accordion-header">
                <button type="button" class="wpmvc-accordion-button wpmvc-collapsed" data-wpmvc-debug-accordion>
                    <span class="wpmvc-debug-components-row">
                        <span class="wpmvc-fw-semibold"><?php echo esc_html( $component['id'] ); ?></span>
                        <span class="wpmvc-text-body-secondary wpmvc-text-truncate"><code><?php echo esc_html( $component['class'] ); ?></code></span>
                        <span><span class="wpmvc-badge wpmvc-text-bg-secondary"><?php echo esc_html( $component['app'] ); ?></span></span>
                        <span>
                            <?php if ( $component['bootstrap'] ) : ?>
                                <span class="wpmvc-badge wpmvc-bg-info-subtle wpmvc-text-info-emphasis"><?php echo esc_html( 'Yes' ); ?></span>
                            <?php else : ?>
                                <span class="wpmvc-text-body-secondary">—</span>
                            <?php endif; ?>
                        </span>
                        <span>
                            <?php if ( $component['loaded'] ) : ?>
                                <span class="wpmvc-badge wpmvc-bg-success-subtle wpmvc-text-success-emphasis"><?php echo esc_html( 'Loaded' ); ?></span>
                            <?php else : ?>
                                <span class="wpmvc-badge wpmvc-bg-warning-subtle wpmvc-text-warning-emphasis"><?php echo esc_html( 'Lazy' ); ?></span>
                            <?php endif; ?>
                        </span>
                    </span>
                </button>
            </h2>

            <div class="wpmvc-accordion-collapse wpmvc-collapse">
                <div class="wpmvc-accordion-body">
                    <div class="wpmvc-row wpmvc-g-4">
                        <div class="wpmvc-col-12 wpmvc-col-lg-5">
                            <div class="wpmvc-fw-semibold wpmvc-mb-2"><?php echo esc_html( 'Details' ); ?></div>

                            <?php
                            $details = array(
                                'ID / Alias'  => $component['id'],
                                'Class'       => $component['class'],
                                'Application' => $component['app'],
                                'Status'      => $component['loaded'] ? 'Loaded' : 'Lazy',
                                'Bootstrap'   => $component['bootstrap'] ? 'Yes' : 'No',
                            );
                            ?>
                            <?php foreach ( $details as $label => $value ) : ?>
                                <div class="wpmvc-d-flex wpmvc-justify-content-between wpmvc-gap-3 wpmvc-py-1 wpmvc-small">
                                    <span class="wpmvc-text-body-secondary wpmvc-text-nowrap"><?php echo esc_html( $label ); ?></span>
                                    <span class="wpmvc-fw-medium wpmvc-text-end wpmvc-text-truncate" title="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $value ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="wpmvc-col-12 wpmvc-col-lg-7">
                            <div class="wpmvc-fw-semibold wpmvc-mb-2"><?php echo esc_html( 'Declared Config' ); ?></div>

                            <?php if ( empty( $component['config'] ) ) : ?>
                                <p class="wpmvc-text-body-secondary wpmvc-small wpmvc-mb-0"><?php echo esc_html( 'No additional config — class only.' ); ?></p>
                            <?php else : ?>
                                <?php foreach ( $component['config'] as $key => $value ) : ?>
                                    <div class="wpmvc-d-flex wpmvc-justify-content-between wpmvc-gap-3 wpmvc-py-1 wpmvc-small wpmvc-border-bottom">
                                        <span class="wpmvc-text-body-secondary wpmvc-text-nowrap"><?php echo esc_html( $key ); ?></span>
                                        <code class="wpmvc-text-end wpmvc-text-truncate" title="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $value ); ?></code>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
