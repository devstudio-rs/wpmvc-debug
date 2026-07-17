<?php
/**
 * Overview tab view.
 *
 * @var array $data See tabs\Overview_Tab::get_data().
 */

?>
<h5 class="wpmvc-mb-1"><?php echo esc_html__( 'Overview' ); ?></h5>
<p class="wpmvc-text-body-secondary wpmvc-mb-4"><?php echo esc_html__( 'General information about the current request' ); ?></p>

<div class="wpmvc-row wpmvc-row-cols-1 wpmvc-row-cols-md-3 wpmvc-row-cols-xxl-5 wpmvc-g-3 wpmvc-mb-3">
    <?php foreach ( $data['cards'] as $card ) : ?>
        <div class="wpmvc-col">
            <div class="wpmvc-card wpmvc-h-100">
                <div class="wpmvc-card-body">
                    <div class="wpmvc-d-flex wpmvc-align-items-center wpmvc-gap-2 wpmvc-mb-2">
                        <?php if ( ! empty( $card['icon'] ) ) : ?>
                            <span class="wpmvc-text-primary wpmvc-d-inline-flex" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><?php echo $card['icon']; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG markup from the tab class. ?></svg>
                            </span>
                        <?php endif; ?>
                        <span class="wpmvc-small wpmvc-fw-semibold wpmvc-text-body-emphasis"><?php echo esc_html( $card['label'] ); ?></span>
                    </div>
                    <div class="wpmvc-fw-semibold wpmvc-text-truncate" title="<?php echo esc_attr( $card['value'] ); ?>"><?php echo esc_html( $card['value'] ); ?></div>
                    <div class="wpmvc-text-body-secondary wpmvc-small wpmvc-mt-1"><?php echo esc_html( $card['meta'] ); ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="wpmvc-row wpmvc-g-3">

    <div class="wpmvc-col-12 wpmvc-col-xl-6">
        <div class="wpmvc-card wpmvc-h-100">
            <div class="wpmvc-card-body wpmvc-p-0">
                <div class="wpmvc-fw-semibold wpmvc-px-3 wpmvc-pt-3 wpmvc-pb-2">
                    <?php echo esc_html__( 'Loaded Applications' ); ?>
                    <span class="wpmvc-text-body-secondary">(<?php echo esc_html( count( $data['applications'] ) ); ?>)</span>
                </div>

                <?php foreach ( $data['applications'] as $app ) : ?>
                    <div class="wpmvc-d-flex wpmvc-align-items-center wpmvc-gap-3 wpmvc-px-3 wpmvc-py-2 wpmvc-border-bottom">
                        <span class="wpmvc-debug-logo" aria-hidden="true"><?php echo esc_html( strtoupper( substr( $app['name'], 0, 1 ) ) ); ?></span>

                        <div class="wpmvc-me-auto wpmvc-min-w-0">
                            <div class="wpmvc-fw-medium"><?php echo esc_html( $app['name'] ); ?></div>
                            <div class="wpmvc-text-body-secondary wpmvc-small"><code><?php echo esc_html( $app['class'] ); ?></code></div>
                            <?php if ( $app['root'] ) : ?>
                                <div class="wpmvc-text-body-secondary wpmvc-small wpmvc-text-truncate" title="<?php echo esc_attr( $app['root'] ); ?>"><?php echo esc_html( $app['root'] ); ?></div>
                            <?php endif; ?>
                        </div>

                        <span class="wpmvc-badge wpmvc-bg-success-subtle wpmvc-text-success-emphasis"><?php echo esc_html__( 'Active' ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="wpmvc-card-footer p-0">
                <button type="button" class="wpmvc-btn wpmvc-btn-link wpmvc-w-100 wpmvc-text-decoration-none" data-wpmvc-debug-goto="applications">
                    <?php echo esc_html__( 'View all applications' ); ?> &rarr;
                </button>
            </div>
        </div>
    </div>

    <div class="wpmvc-col-12 wpmvc-col-xl-6">
        <div class="wpmvc-card wpmvc-h-100">
            <div class="wpmvc-card-body wpmvc-p-0">
                <div class="wpmvc-fw-semibold wpmvc-px-3 wpmvc-pt-3 wpmvc-pb-2">
                    <?php echo esc_html__( 'Core Components' ); ?>
                    <span class="wpmvc-text-body-secondary">(<?php echo esc_html( $data['components_total'] ); ?>)</span>
                </div>

                <?php foreach ( $data['components'] as $component ) : ?>
                    <div class="wpmvc-d-flex wpmvc-align-items-center wpmvc-gap-3 wpmvc-px-3 wpmvc-py-2 wpmvc-border-bottom">
                        <span class="wpmvc-text-body-secondary wpmvc-d-inline-flex" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5 8 5.961 14.154 3.5 8.186 1.113zM15 4.239l-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/></svg>
                        </span>

                        <div class="wpmvc-me-auto wpmvc-min-w-0">
                            <div class="wpmvc-fw-medium"><?php echo esc_html( $component['id'] ); ?></div>
                            <div class="wpmvc-text-body-secondary wpmvc-small"><?php echo esc_html( $component['app'] ); ?></div>
                        </div>

                        <?php if ( $component['loaded'] ) : ?>
                            <span class="wpmvc-badge wpmvc-bg-success-subtle wpmvc-text-success-emphasis"><?php echo esc_html__( 'Loaded' ); ?></span>
                        <?php else : ?>
                            <span class="wpmvc-badge wpmvc-bg-warning-subtle wpmvc-text-warning-emphasis"><?php echo esc_html__( 'Lazy' ); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="wpmvc-card-footer p-0">
                <button type="button" class="wpmvc-btn wpmvc-btn-link wpmvc-w-100 wpmvc-text-decoration-none" data-wpmvc-debug-goto="components">
                    <?php echo esc_html__( 'View all components' ); ?> &rarr;
                </button>
            </div>
        </div>
    </div>

</div>
