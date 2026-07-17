<?php
/**
 * Debug toolbar markup. All Bootstrap classes must use the `wpmvc-` prefix
 * (see the asset build); `data-bs-theme` switches the color mode for the
 * toolbar only.
 */
?>
<div class="wpmvc-debug" data-bs-theme="light">
    <div class="wpmvc-d-flex wpmvc-align-items-center wpmvc-gap-3 wpmvc-px-3 wpmvc-py-2">
        <strong>WPMVC Debug</strong>

        <button
            type="button"
            class="wpmvc-btn wpmvc-btn-sm wpmvc-btn-outline-secondary wpmvc-ms-auto"
            data-wpmvc-debug-theme-toggle
        ><?php echo esc_html( 'Toggle theme' ); ?></button>
    </div>
</div>
