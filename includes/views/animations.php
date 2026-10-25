<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<section class="ps-panel" id="ps-gallery-panel">
                  <div class="ps-panel-head"><div><h2><?php esc_html_e( 'Animation Gallery', 'preloader-studio-animated-loading-screens' ); ?></h2><p><?php esc_html_e( 'Every preset has its own motion and visual structure.', 'preloader-studio-animated-loading-screens' ); ?></p></div><span class="ps-count">36 designs</span></div>
                  <div class="ps-gallery-tools"><input id="ps-search" type="search" placeholder="<?php esc_attr_e( 'Search animations...', 'preloader-studio-animated-loading-screens' ); ?>" aria-label="<?php esc_attr_e( 'Search animations', 'preloader-studio-animated-loading-screens' ); ?>" />
                    <div class="ps-filters" role="group" aria-label="<?php esc_attr_e( 'Filter categories', 'preloader-studio-animated-loading-screens' ); ?>">
                    <?php foreach ( array( 'All', 'Minimal', 'Modern', 'Premium', 'Creative', 'Branding', 'Advanced' ) as $ps_studio_category ) : ?>
                      <button type="button" class="ps-filter<?php echo 'All' === $ps_studio_category ? ' is-active' : ''; ?>" data-filter="<?php echo esc_attr( $ps_studio_category ); ?>"><?php echo esc_html( $ps_studio_category ); ?></button>
                    <?php endforeach; ?></div></div>
                  <div class="ps-gallery" id="ps-gallery">
                  <?php foreach ( $presets as $id => $ps_studio_info ) : ?>
                  <button type="button" class="ps-design<?php echo $s['preset'] === $id ? ' is-selected' : ''; ?>" data-preset="<?php echo esc_attr( $id ); ?>" data-category="<?php echo esc_attr( $ps_studio_info[1] ); ?>" data-name="<?php echo esc_attr( strtolower( $ps_studio_info[0] ) ); ?>" aria-pressed="<?php echo $s['preset'] === $id ? 'true' : 'false'; ?>">
                    <span class="ps-design-stage" style="--ps-color:<?php echo esc_attr( $s['color'] ); ?>;--ps-size:54px;--ps-speed:1;">
                      <?php PRELSTAN_Studio::render_animation( $id, true ); ?></span>
                    <span class="ps-design-bottom"><span class="ps-design-name"><?php echo esc_html( $ps_studio_info[0] ); ?></span><span class="ps-design-check dashicons dashicons-yes-alt"></span></span>
                    <span class="ps-design-category"><?php echo esc_html( $ps_studio_info[1] ); ?></span>
                  </button>
                  <?php endforeach; ?>
                  </div>
                  <p class="ps-empty" hidden><?php esc_html_e( 'No animations match your search.', 'preloader-studio-animated-loading-screens' ); ?></p>
                </section>
