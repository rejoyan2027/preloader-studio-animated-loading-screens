<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<section class="ps-panel" id="ps-library-panel">
                  <div class="ps-panel-head"><div><h2><?php esc_html_e( 'My Saved Designs', 'preloader-studio-animated-loading-screens' ); ?></h2><p><?php esc_html_e( 'Save up to 20 design configurations, load them into the builder, and publish only when ready.', 'preloader-studio-animated-loading-screens' ); ?></p></div></div>
                  <div class="ps-library-controls"><label class="ps-field" for="ps-design-name"><span><?php esc_html_e( 'New design name', 'preloader-studio-animated-loading-screens' ); ?></span><input type="text" id="ps-design-name" maxlength="55" placeholder="<?php esc_attr_e( 'e.g. Brand Aurora', 'preloader-studio-animated-loading-screens' ); ?>" /></label><button type="button" id="ps-save-design" class="button button-secondary"><?php esc_html_e( 'Save to My Designs', 'preloader-studio-animated-loading-screens' ); ?></button></div>
                  <div id="ps-design-message" class="ps-design-message" role="status" aria-live="polite"></div>
                  <div id="ps-saved-designs" class="ps-saved-designs" aria-label="<?php esc_attr_e( 'Saved designs', 'preloader-studio-animated-loading-screens' ); ?>"></div>
                </section>
