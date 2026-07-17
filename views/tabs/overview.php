<?php
/**
 * Overview tab — general information about the current request.
 * Values are read directly for now; they move to collectors as those land.
 */

$wpmvc_debug_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
$wpmvc_debug_path   = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
$wpmvc_debug_user   = wp_get_current_user();

$wpmvc_debug_cards = array(
    array(
        'label' => 'Request',
        'value' => trim( $wpmvc_debug_method . ' ' . $wpmvc_debug_path ),
        'meta'  => date_i18n( 'H:i:s' ),
    ),
    array(
        'label' => 'User',
        'value' => $wpmvc_debug_user->exists() ? $wpmvc_debug_user->user_login : 'Guest',
        'meta'  => $wpmvc_debug_user->exists() ? implode( ', ', $wpmvc_debug_user->roles ) : '—',
    ),
    array(
        'label' => 'Environment',
        'value' => wp_get_environment_type(),
        'meta'  => 'Debug ' . ( WP_DEBUG ? 'true' : 'false' ),
    ),
    array(
        'label' => 'Versions',
        'value' => 'PHP ' . PHP_VERSION,
        'meta'  => 'WordPress ' . get_bloginfo( 'version' ),
    ),
    array(
        'label' => 'Performance',
        'value' => size_format( memory_get_peak_usage( true ) ),
        'meta'  => number_format( (float) timer_stop() * 1000, 1 ) . ' ms',
    ),
);

?>
<h5 class="wpmvc-mb-1"><?php echo esc_html( 'Overview' ); ?></h5>
<p class="wpmvc-text-body-secondary wpmvc-mb-4"><?php echo esc_html( 'General information about the current request' ); ?></p>

<div class="wpmvc-row wpmvc-row-cols-1 wpmvc-row-cols-md-3 wpmvc-row-cols-xxl-5 wpmvc-g-3">
    <?php foreach ( $wpmvc_debug_cards as $wpmvc_debug_card ) : ?>
        <div class="wpmvc-col">
            <div class="wpmvc-card wpmvc-h-100">
                <div class="wpmvc-card-body">
                    <div class="wpmvc-text-primary wpmvc-small wpmvc-fw-medium wpmvc-mb-2"><?php echo esc_html( $wpmvc_debug_card['label'] ); ?></div>
                    <div class="wpmvc-fw-semibold wpmvc-text-truncate" title="<?php echo esc_attr( $wpmvc_debug_card['value'] ); ?>"><?php echo esc_html( $wpmvc_debug_card['value'] ); ?></div>
                    <div class="wpmvc-text-body-secondary wpmvc-small wpmvc-mt-1"><?php echo esc_html( $wpmvc_debug_card['meta'] ); ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
