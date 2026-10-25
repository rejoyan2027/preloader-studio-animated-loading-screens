<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<section class="ps-panel" id="ps-brand-panel">
                  <div class="ps-panel-head"><div><h2><?php esc_html_e( 'Your Branding', 'preloader-studio-animated-loading-screens' ); ?></h2><p><?php esc_html_e( 'Use a built-in animation, your site logo, or an uploaded GIF/image as the loader.', 'preloader-studio-animated-loading-screens' ); ?></p></div></div>
                  <div class="ps-fields-grid">
                  <?php self::select( 'source', __( 'Loader source', 'preloader-studio-animated-loading-screens' ), array( 'preset' => '36 built-in animations', 'site_logo' => 'Use my WordPress site logo', 'logo' => 'Upload/select my logo', 'custom_image' => 'My custom image / animated GIF' ), $s ); ?>
                  <?php self::select( 'image_effect', __( 'Uploaded image animation', 'preloader-studio-animated-loading-screens' ), array( 'pulse' => 'Soft pulse', 'spin' => 'Continuous spin', 'float' => 'Floating', 'bounce' => 'Bouncing', 'reveal' => 'Reveal', 'glow' => 'Soft glow', 'swing' => 'Swing', 'flip' => 'Flip', 'breathe' => 'Breathe', 'pop' => 'Pop', 'wobble' => 'Wobble', 'none' => 'None (preserve GIF animation)' ), $s ); ?>
                  <?php self::image_picker( 'logo_id', __( 'Your logo', 'preloader-studio-animated-loading-screens' ), $s ); ?>
                  <?php self::image_picker( 'custom_image_id', __( 'Custom loader image / GIF', 'preloader-studio-animated-loading-screens' ), $s ); ?>
                  <?php self::field( 'loading_text', __( 'Loading / brand text', 'preloader-studio-animated-loading-screens' ), $s ); ?>
                  </div>
                  <div class="ps-options-row"><?php self::toggle( 'show_text', __( 'Show loading text', 'preloader-studio-animated-loading-screens' ), $s ); ?><?php self::toggle( 'show_percent', __( 'Show visual progress estimate', 'preloader-studio-animated-loading-screens' ), $s, __( 'Estimated percentage, not a measured download progress.', 'preloader-studio-animated-loading-screens' ) ); ?></div>
                </section>
