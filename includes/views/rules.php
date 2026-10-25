<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<section class="ps-panel" id="ps-rules-panel">
                  <div class="ps-panel-head"><div><h2><?php esc_html_e( 'Display Rules & Theme Compatibility', 'preloader-studio-animated-loading-screens' ); ?></h2><p><?php esc_html_e( 'Control when the loader appears and prevent double preloaders.', 'preloader-studio-animated-loading-screens' ); ?></p></div></div>
                  <div class="ps-fields-grid">
                  <?php self::select( 'show_on', __( 'Display frequency', 'preloader-studio-animated-loading-screens' ), array( 'every' => 'Every page visit', 'session' => 'Once per browser tab session', 'home' => 'Home page only' ), $s ); ?>
                  <?php self::select( 'devices', __( 'Devices', 'preloader-studio-animated-loading-screens' ), array( 'all' => 'All devices', 'desktop' => 'Desktop only', 'mobile' => 'Mobile only' ), $s ); ?>
                  <?php self::field( 'exclude_ids', __( 'Excluded page/post IDs (comma-separated)', 'preloader-studio-animated-loading-screens' ), $s ); ?>
                  </div>
                  <div class="ps-fields-grid">
                  <?php self::select( 'compat_mode', __( 'Theme preloader compatibility', 'preloader-studio-animated-loading-screens' ), array( 'auto' => 'Automatic (safe full-screen detection)', 'manual' => 'Manual selectors only', 'off' => 'Off (do not change theme loaders)' ), $s ); ?>
                  <?php self::select( 'performance_mode', __( 'Performance policy', 'preloader-studio-animated-loading-screens' ), array( 'balanced' => 'Balanced (recommended)', 'fast' => 'Fast pages: skip if already loaded', 'always' => 'Always show when permitted' ), $s ); ?>
                  <?php self::field( 'fast_skip_ms', __( 'Fast-page threshold (ms)', 'preloader-studio-animated-loading-screens' ), $s, 'number', 100, 1200 ); ?>
                  </div>
                  <div class="ps-rule-info"><span class="dashicons dashicons-performance" aria-hidden="true"></span><p><?php esc_html_e( 'Balanced mode shows the animation without waiting for third-party scripts beyond the configured timeout. Fast-page mode can skip the screen when the page has already finished loading very quickly. Progress is still an estimate.', 'preloader-studio-animated-loading-screens' ); ?></p></div>
                  <fieldset class="ps-wc-rules"><legend><?php esc_html_e( 'WooCommerce display exclusions', 'preloader-studio-animated-loading-screens' ); ?></legend>
                    <p><?php echo esc_html( $wc_active ? __( 'WooCommerce detected. These rules are active.', 'preloader-studio-animated-loading-screens' ) : __( 'WooCommerce is not active. These rules will apply automatically if it is installed later.', 'preloader-studio-animated-loading-screens' ) ); ?></p>
                    <div class="ps-wc-grid">
                    <?php self::toggle( 'skip_wc_cart', __( 'Skip Cart', 'preloader-studio-animated-loading-screens' ), $s ); ?>
                    <?php self::toggle( 'skip_wc_checkout', __( 'Skip Checkout', 'preloader-studio-animated-loading-screens' ), $s ); ?>
                    <?php self::toggle( 'skip_wc_account', __( 'Skip My Account', 'preloader-studio-animated-loading-screens' ), $s ); ?>
                    <?php self::toggle( 'skip_wc_shop', __( 'Skip Shop', 'preloader-studio-animated-loading-screens' ), $s ); ?>
                    <?php self::toggle( 'skip_wc_product', __( 'Skip Product pages', 'preloader-studio-animated-loading-screens' ), $s ); ?>
                    </div>
                  </fieldset>
                  <div class="ps-theme-info"><span class="dashicons dashicons-info-outline"></span><div>
<?php /* translators: %s: format placeholder value in rule label. */ ?>
                    <strong><?php echo esc_html( sprintf( __( 'Current theme: %s', 'preloader-studio-animated-loading-screens' ), $theme->get( 'Name' ) ) ); ?></strong><p><?php esc_html_e( 'Automatic mode hides only detected fullscreen overlays at runtime, and never changes your saved theme settings. Manual mode uses only the selectors below. For best reliability, disable the original loader in your theme options when possible.', 'preloader-studio-animated-loading-screens' ); ?></p></div></div>
                  <label class="ps-field ps-selectors" for="ps-theme-selectors"><span><?php esc_html_e( 'Optional theme loader selectors (one #id or .class per line)', 'preloader-studio-animated-loading-screens' ); ?></span>
                    <textarea id="ps-theme-selectors" name="ps_settings[theme_selectors]" rows="3" placeholder="#theme-preloader"><?php echo esc_textarea( $s['theme_selectors'] ); ?></textarea>
                    <small><?php esc_html_e( 'Select IDs/classes you have verified. Only fullscreen overlays will be suppressed. In Off mode this field is ignored.', 'preloader-studio-animated-loading-screens' ); ?></small></label>
                  <div class="ps-diagnostics" aria-label="<?php esc_attr_e( 'Compatibility diagnostics', 'preloader-studio-animated-loading-screens' ); ?>">
                    <div><strong><?php esc_html_e( 'Site compatibility tools', 'preloader-studio-animated-loading-screens' ); ?></strong><p><?php esc_html_e( 'Run a browser-side scan while signed in as an administrator. It reports fullscreen candidates and actual matches; it cannot certify every third-party theme.', 'preloader-studio-animated-loading-screens' ); ?></p></div>
                    <a class="button button-secondary" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( $diagnostic_url ); ?>"><?php esc_html_e( 'Open live diagnostics', 'preloader-studio-animated-loading-screens' ); ?></a>
                  </div>
                </section>
