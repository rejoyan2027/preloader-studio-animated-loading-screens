<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<section class="ps-panel" id="ps-style-panel">
                  <div class="ps-panel-head"><div><h2><?php esc_html_e( 'Style & Timing', 'preloader-studio-animated-loading-screens' ); ?></h2><p><?php esc_html_e( 'Customize colors, scale, speed, and the display window.', 'preloader-studio-animated-loading-screens' ); ?></p></div></div>
                  <div class="ps-fields-grid">
                  <?php self::field( 'color', __( 'Animation color', 'preloader-studio-animated-loading-screens' ), $s, 'color' ); ?>
                  <?php self::field( 'background', __( 'Background color', 'preloader-studio-animated-loading-screens' ), $s, 'color' ); ?>
                  <?php self::field( 'text_color', __( 'Text color', 'preloader-studio-animated-loading-screens' ), $s, 'color' ); ?>
                  <?php self::select( 'background_style', __( 'Background effect', 'preloader-studio-animated-loading-screens' ), array( 'solid' => 'Solid color', 'gradient' => 'Gradient blend', 'aurora' => 'Animated aurora', 'glass' => 'Soft glass glow' ), $s ); ?>
                  <?php self::field( 'gradient_color', __( 'Secondary color', 'preloader-studio-animated-loading-screens' ), $s, 'color' ); ?>
                  <?php self::field( 'glow', __( 'Glow intensity (%)', 'preloader-studio-animated-loading-screens' ), $s, 'number', 0, 100 ); ?>
                  <?php self::select( 'exit_effect', __( 'Exit transition', 'preloader-studio-animated-loading-screens' ), array( 'fade' => 'Fade', 'zoom-out' => 'Zoom out', 'zoom-in' => 'Zoom in', 'slide-up' => 'Slide up', 'slide-down' => 'Slide down', 'slide-left' => 'Slide left', 'slide-right' => 'Slide right', 'blur' => 'Blur dissolve', 'rotate' => 'Rotate away', 'curtain' => 'Curtain close', 'split' => 'Split reveal', 'shrink' => 'Shrink to center', 'wipe' => 'Wipe reveal' ), $s ); ?>
                  <?php self::field( 'font_size', __( 'Brand text size (px)', 'preloader-studio-animated-loading-screens' ), $s, 'number', 10, 28 ); ?>
                  <?php self::select( 'font_weight', __( 'Brand text weight', 'preloader-studio-animated-loading-screens' ), array( '400' => 'Regular', '500' => 'Medium', '600' => 'Semibold', '700' => 'Bold' ), $s ); ?>
                  <?php self::field( 'letter_spacing', __( 'Letter spacing (px)', 'preloader-studio-animated-loading-screens' ), $s, 'number', 0, 10 ); ?>
                  <?php self::field( 'size', __( 'Loader size (px)', 'preloader-studio-animated-loading-screens' ), $s, 'number', 32, 180 ); ?>
                  <?php self::field( 'speed', __( 'Animation speed (%)', 'preloader-studio-animated-loading-screens' ), $s, 'number', 50, 200 ); ?>
                  <?php self::field( 'min_ms', __( 'Minimum display (ms)', 'preloader-studio-animated-loading-screens' ), $s, 'number', 0, 2000 ); ?>
                  <?php self::field( 'max_ms', __( 'Safety timeout (ms)', 'preloader-studio-animated-loading-screens' ), $s, 'number', 1500, 12000 ); ?>
                  <?php self::field( 'fade_ms', __( 'Fade out (ms)', 'preloader-studio-animated-loading-screens' ), $s, 'number', 0, 1500 ); ?>
                  </div>
                  <div class="ps-device-style-settings">
                    <h3>Device-specific loader sizing</h3>
                    <p class="description">Keep the override off to inherit the global Loader Size and Animation Speed. Enable it only where needed. Use the Mobile / Tablet preview buttons to inspect the result.</p>
                    <?php foreach(array('mobile'=>'Mobile (up to 767px)','tablet'=>'Tablet (768–1024px)') as $ps_studio_device=>$ps_studio_label): ?>
                      <div class="ps-device-style-card">
                        <label class="ps-device-style-toggle"><input type="checkbox" name="ps_settings[<?php echo esc_attr($ps_studio_device); ?>_custom]" value="1" <?php echo checked(!empty($s[$ps_studio_device.'_custom']),true,false); ?> /> <strong><?php echo esc_html($ps_studio_label); ?>: custom loader size &amp; speed</strong></label>
                        <div class="ps-fields-grid">
                          <?php self::field($ps_studio_device.'_size','Loader size (px)',$s,'number',32,180); ?>
                          <?php self::field($ps_studio_device.'_speed','Animation speed (%)',$s,'number',50,200); ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </section>
