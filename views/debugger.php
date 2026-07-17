<?php
/**
 * Debugger root markup: floating action button + bottom panel.
 *
 * All Bootstrap classes must use the `wpmvc-` prefix (see the asset build).
 * `data-bs-theme` on the root switches the color mode for the debugger only;
 * JS toggles it, together with the `wpmvc-debug-open` state class.
 */

use wpmvc\debug\Debug;

?>
<div class="wpmvc-debug" data-bs-theme="dark">

    <button
        type="button"
        class="wpmvc-debug-fab"
        data-wpmvc-debug-toggle
        aria-label="<?php echo esc_attr( 'Toggle WPMVC Debugger' ); ?>"
    >WPMVC</button>

    <section class="wpmvc-debug-panel" role="dialog" aria-label="<?php echo esc_attr( 'WPMVC Debugger' ); ?>">

        <header class="wpmvc-debug-header">
            <span class="wpmvc-debug-logo" aria-hidden="true">W</span>
            <strong><?php echo esc_html( 'WPMVC Debugger' ); ?></strong>
            <span class="wpmvc-badge wpmvc-bg-success-subtle wpmvc-text-success-emphasis">v<?php echo esc_html( Debug::VERSION ); ?></span>

            <span class="wpmvc-ms-auto wpmvc-d-flex wpmvc-align-items-center wpmvc-gap-2">
                <span class="wpmvc-d-none wpmvc-d-lg-flex wpmvc-align-items-center wpmvc-gap-2">
                    <?php foreach ( $chips as $chip ) : ?>
                        <span class="wpmvc-debug-chip">
                            <span class="wpmvc-text-body-secondary"><?php echo esc_html( $chip['label'] ); ?></span>
                            <span class="wpmvc-fw-semibold"><?php echo esc_html( $chip['value'] ); ?></span>
                        </span>
                    <?php endforeach; ?>
                </span>

                <span class="wpmvc-vr wpmvc-d-none wpmvc-d-lg-inline-block" aria-hidden="true"></span>

                <button
                    type="button"
                    class="wpmvc-debug-theme-switch"
                    data-wpmvc-debug-theme-toggle
                    role="switch"
                    aria-checked="true"
                    aria-label="<?php echo esc_attr( 'Toggle color mode' ); ?>"
                >
                    <span class="wpmvc-debug-theme-switch-check">
                        <svg class="wpmvc-debug-theme-switch-sun" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/></svg>
                        <svg class="wpmvc-debug-theme-switch-moon" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/></svg>
                    </span>
                </button>

                <span class="wpmvc-vr" aria-hidden="true"></span>

                <button
                    type="button"
                    class="wpmvc-debug-icon-btn"
                    data-wpmvc-debug-pin
                    aria-label="<?php echo esc_attr( 'Keep open after refresh' ); ?>"
                >
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M9.828.722a.5.5 0 0 1 .354.146l4.95 4.95a.5.5 0 0 1 0 .707c-.48.48-1.072.588-1.503.588-.177 0-.335-.018-.46-.039l-3.134 3.134a5.927 5.927 0 0 1 .16 1.013c.046.702-.032 1.687-.72 2.375a.5.5 0 0 1-.707 0l-2.829-2.828-3.182 3.182c-.195.195-1.219.902-1.414.707-.195-.195.512-1.22.707-1.414l3.182-3.182-2.828-2.829a.5.5 0 0 1 0-.707c.688-.688 1.673-.767 2.375-.72a5.922 5.922 0 0 1 1.013.16l3.134-3.133a2.772 2.772 0 0 1-.04-.461c0-.43.108-1.022.589-1.503a.5.5 0 0 1 .353-.146z"/></svg>
                </button>

                <button
                    type="button"
                    class="wpmvc-debug-icon-btn"
                    data-wpmvc-debug-close
                    aria-label="<?php echo esc_attr( 'Close' ); ?>"
                >
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/></svg>
                </button>
            </span>
        </header>

        <div class="wpmvc-debug-body">
            <nav class="wpmvc-debug-sidebar wpmvc-nav wpmvc-nav-pills wpmvc-flex-md-column">
                <?php foreach ( $tabs as $index => $tab ) : ?>
                    <button
                        type="button"
                        class="wpmvc-nav-link wpmvc-text-start wpmvc-d-flex wpmvc-align-items-center wpmvc-gap-2<?php echo 0 === $index ? ' wpmvc-active' : ''; ?>"
                        data-wpmvc-debug-tab="<?php echo esc_attr( $tab->get_id() ); ?>"
                    >
                        <?php if ( $tab->get_icon() ) : ?>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><?php echo $tab->get_icon(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG markup from the tab class. ?></svg>
                        <?php endif; ?>

                        <?php echo esc_html( $tab->get_label() ); ?>

                        <?php if ( null !== $tab->get_badge() ) : ?>
                            <span class="wpmvc-badge wpmvc-rounded-pill wpmvc-text-bg-secondary wpmvc-ms-auto"><?php echo esc_html( $tab->get_badge() ); ?></span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>

                <div class="wpmvc-debug-sidebar-footer wpmvc-d-none wpmvc-d-md-block wpmvc-small">
                    <div class="wpmvc-fw-semibold"><?php echo esc_html( 'WPMVC Framework' ); ?></div>
                    <div class="wpmvc-text-body-secondary"><?php echo esc_html( 'Version ' . \wpmvc\App::$version ); ?></div>
                    <a
                        class="wpmvc-text-body-secondary"
                        href="https://wpmvc.devstudio.rs"
                        target="_blank"
                        rel="noopener noreferrer"
                    >https://wpmvc.devstudio.rs</a>
                </div>
            </nav>

            <div class="wpmvc-debug-content">
                <?php foreach ( $tabs as $index => $tab ) : ?>
                    <div
                        data-wpmvc-debug-pane="<?php echo esc_attr( $tab->get_id() ); ?>"
                        <?php echo 0 !== $index ? 'class="wpmvc-d-none"' : ''; ?>
                    >
                        <?php $tab->render(); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </section>

</div>
