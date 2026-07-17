<?php
/**
 * Overview tab view.
 *
 * @var array $data See tabs\Overview_Tab::get_data().
 */

?>
<h5 class="wpmvc-mb-1"><?php echo esc_html( 'Overview' ); ?></h5>
<p class="wpmvc-text-body-secondary wpmvc-mb-4"><?php echo esc_html( 'General information about the current request' ); ?></p>

<div class="wpmvc-row wpmvc-row-cols-1 wpmvc-row-cols-md-3 wpmvc-row-cols-xxl-5 wpmvc-g-3">
    <?php foreach ( $data['cards'] as $card ) : ?>
        <div class="wpmvc-col">
            <div class="wpmvc-card wpmvc-h-100">
                <div class="wpmvc-card-body">
                    <div class="wpmvc-text-primary wpmvc-small wpmvc-fw-medium wpmvc-mb-2"><?php echo esc_html( $card['label'] ); ?></div>
                    <div class="wpmvc-fw-semibold wpmvc-text-truncate" title="<?php echo esc_attr( $card['value'] ); ?>"><?php echo esc_html( $card['value'] ); ?></div>
                    <div class="wpmvc-text-body-secondary wpmvc-small wpmvc-mt-1"><?php echo esc_html( $card['meta'] ); ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
