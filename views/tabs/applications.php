<?php
/**
 * Applications tab view — list of all initialized WPMVC applications.
 *
 * @var array $data See tabs\Applications_Tab::get_data().
 */

?>
<h5 class="wpmvc-mb-1 wpmvc-d-flex wpmvc-align-items-center wpmvc-gap-2">
    <?php echo esc_html( 'Applications' ); ?>
    <span class="wpmvc-badge wpmvc-rounded-pill wpmvc-text-bg-secondary"><?php echo esc_html( count( $data['apps'] ) ); ?></span>
</h5>
<p class="wpmvc-text-body-secondary wpmvc-mb-4"><?php echo esc_html( 'List of all loaded WPMVC applications.' ); ?></p>

<div class="wpmvc-d-flex wpmvc-flex-column wpmvc-gap-3">
    <?php foreach ( $data['apps'] as $app ) : ?>
        <div class="wpmvc-card">
            <div class="wpmvc-card-body wpmvc-d-flex wpmvc-align-items-center wpmvc-gap-3 wpmvc-flex-wrap">

                <span class="wpmvc-debug-logo" aria-hidden="true"><?php echo esc_html( strtoupper( substr( $app['name'], 0, 1 ) ) ); ?></span>

                <div class="wpmvc-me-auto">
                    <div class="wpmvc-fw-semibold">
                        <?php echo esc_html( $app['name'] ); ?>
                        <span class="wpmvc-badge wpmvc-bg-success-subtle wpmvc-text-success-emphasis wpmvc-ms-1"><?php echo esc_html( 'Active' ); ?></span>
                    </div>
                    <div class="wpmvc-text-body-secondary wpmvc-small"><code><?php echo esc_html( $app['class'] ); ?></code></div>
                    <?php if ( $app['root'] ) : ?>
                        <div class="wpmvc-text-body-secondary wpmvc-small"><?php echo esc_html( $app['root'] ); ?></div>
                    <?php endif; ?>
                </div>

                <div class="wpmvc-debug-app-stats">
                    <div>
                        <div class="wpmvc-text-body-secondary wpmvc-small"><?php echo esc_html( 'Components' ); ?></div>
                        <div class="wpmvc-fw-semibold" title="<?php echo esc_attr( implode( ', ', $app['components'] ) ); ?>"><?php echo esc_html( count( $app['components'] ) ); ?></div>
                    </div>

                    <div>
                        <div class="wpmvc-text-body-secondary wpmvc-small"><?php echo esc_html( 'Loaded' ); ?></div>
                        <div class="wpmvc-fw-semibold" title="<?php echo esc_attr( implode( ', ', $app['loaded'] ) ); ?>"><?php echo esc_html( count( $app['loaded'] ) ); ?></div>
                    </div>

                    <div>
                        <div class="wpmvc-text-body-secondary wpmvc-small"><?php echo esc_html( 'Bootstrap' ); ?></div>
                        <div class="wpmvc-fw-semibold" title="<?php echo esc_attr( implode( ', ', $app['bootstrap'] ) ); ?>"><?php echo esc_html( count( $app['bootstrap'] ) ); ?></div>
                    </div>

                    <div>
                        <div class="wpmvc-text-body-secondary wpmvc-small"><?php echo esc_html( 'Routes' ); ?></div>
                        <div class="wpmvc-fw-semibold"><?php echo esc_html( null !== $app['routes'] ? $app['routes'] : '—' ); ?></div>
                    </div>

                    <div>
                        <div class="wpmvc-text-body-secondary wpmvc-small"><?php echo esc_html( 'Domain' ); ?></div>
                        <div class="wpmvc-fw-semibold wpmvc-text-truncate" title="<?php echo esc_attr( $app['domain'] ); ?>"><?php echo esc_html( $app['domain'] ); ?></div>
                    </div>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
</div>
