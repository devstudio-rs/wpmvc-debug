<?php
/**
 * Environment tab view.
 *
 * @var array $data See tabs\Environment_Tab::get_data().
 */

?>
<h5 class="wpmvc-mb-1"><?php echo esc_html( 'Environment' ); ?></h5>
<p class="wpmvc-text-body-secondary wpmvc-mb-4"><?php echo esc_html( 'Information about the current server environment and configuration.' ); ?></p>

<div class="wpmvc-row wpmvc-row-cols-1 wpmvc-row-cols-md-2 wpmvc-row-cols-xxl-4 wpmvc-g-3 wpmvc-mb-3">
    <?php foreach ( $data['sections'] as $section ) : ?>
        <div class="wpmvc-col">
            <div class="wpmvc-card wpmvc-h-100">
                <div class="wpmvc-card-body">
                    <div class="wpmvc-fw-semibold wpmvc-mb-2 wpmvc-d-flex wpmvc-align-items-center wpmvc-gap-2">
                        <?php if ( ! empty( $section['icon'] ) ) : ?>
                            <span class="wpmvc-text-body-secondary wpmvc-d-inline-flex">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><?php echo $section['icon']; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG markup from the tab class. ?></svg>
                            </span>
                        <?php endif; ?>

                        <?php echo esc_html( $section['title'] ); ?>
                    </div>

                    <?php foreach ( $section['items'] as $item ) : ?>
                        <div class="wpmvc-d-flex wpmvc-justify-content-between wpmvc-align-items-center wpmvc-gap-3 wpmvc-py-1 wpmvc-small">
                            <span class="wpmvc-text-body-secondary wpmvc-text-nowrap"><?php echo esc_html( $item['label'] ); ?></span>

                            <?php if ( ! empty( $item['badge'] ) ) : ?>
                                <span class="wpmvc-badge wpmvc-bg-<?php echo esc_attr( $item['badge'] ); ?>-subtle wpmvc-text-<?php echo esc_attr( $item['badge'] ); ?>-emphasis"><?php echo esc_html( $item['value'] ); ?></span>
                            <?php else : ?>
                                <span class="wpmvc-fw-medium wpmvc-text-end wpmvc-text-truncate" title="<?php echo esc_attr( $item['value'] ); ?>"><?php echo esc_html( $item['value'] ); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="wpmvc-row wpmvc-g-3">

    <div class="wpmvc-col-12 wpmvc-col-xl-3">
        <div class="wpmvc-card wpmvc-h-100">
            <div class="wpmvc-card-body">
                <div class="wpmvc-fw-semibold wpmvc-mb-3"><?php echo esc_html( 'Loaded Extensions' ); ?> (<?php echo esc_html( count( $data['extensions'] ) ); ?>)</div>

                <div class="wpmvc-debug-scroll wpmvc-d-flex wpmvc-flex-wrap wpmvc-gap-2 wpmvc-align-content-start">
                    <?php foreach ( $data['extensions'] as $extension ) : ?>
                        <span class="wpmvc-badge wpmvc-bg-success-subtle wpmvc-text-success-emphasis"><?php echo esc_html( $extension ); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="wpmvc-col-12 wpmvc-col-xl-4">
        <div class="wpmvc-card wpmvc-h-100">
            <div class="wpmvc-card-body">
                <div class="wpmvc-fw-semibold wpmvc-mb-3"><?php echo esc_html( 'Environment Variables' ); ?> (<?php echo esc_html( count( $data['env'] ) ); ?>)</div>

                <div class="wpmvc-debug-scroll">
                    <?php foreach ( $data['env'] as $key => $value ) : ?>
                        <div class="wpmvc-d-flex wpmvc-justify-content-between wpmvc-gap-3 wpmvc-py-1 wpmvc-small wpmvc-border-bottom">
                            <span class="wpmvc-text-body-secondary wpmvc-text-truncate" title="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $key ); ?></span>
                            <span class="wpmvc-fw-medium wpmvc-text-end wpmvc-text-truncate" title="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $value ); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="wpmvc-col-12 wpmvc-col-xl-5">
        <div class="wpmvc-card wpmvc-h-100">
            <div class="wpmvc-card-body">
                <div class="wpmvc-fw-semibold wpmvc-mb-3"><?php echo esc_html( 'Paths' ); ?></div>

                <div class="wpmvc-debug-scroll">
                    <?php foreach ( $data['paths'] as $label => $path ) : ?>
                        <div class="wpmvc-d-flex wpmvc-justify-content-between wpmvc-gap-3 wpmvc-py-1 wpmvc-small">
                            <span class="wpmvc-text-body-secondary wpmvc-text-nowrap"><?php echo esc_html( $label ); ?></span>
                            <code class="wpmvc-text-end wpmvc-text-truncate" title="<?php echo esc_attr( $path ); ?>"><?php echo esc_html( $path ); ?></code>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

</div>
