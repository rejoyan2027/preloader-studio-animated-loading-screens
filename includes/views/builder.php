<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<section class="ps-panel ps-builder-panel" id="ps-builder-panel">
                  <div class="ps-panel-head"><div><h2><?php esc_html_e( 'Visual Design Builder', 'preloader-studio-animated-loading-screens' ); ?></h2><p><?php esc_html_e( 'Arrange the layers with drag and drop or the up/down controls. Customize each layer below.', 'preloader-studio-animated-loading-screens' ); ?></p></div><span class="ps-count">v1.3 Studio</span></div>
                  <input type="hidden" id="ps-layer-order" name="ps_settings[layer_order]" value="<?php echo esc_attr( $s['layer_order'] ); ?>" />
                  <ul class="ps-layer-list" id="ps-layer-list" aria-label="<?php esc_attr_e( 'Layer order', 'preloader-studio-animated-loading-screens' ); ?>">
                  <?php foreach ( explode( ',', $s['layer_order'] ) as $ps_studio_layer ) : ?>
                    <li class="ps-layer-row" data-layer="<?php echo esc_attr( $ps_studio_layer ); ?>" draggable="true"><span class="dashicons dashicons-move ps-drag-icon" aria-hidden="true"></span><strong><?php echo esc_html( array( 'visual' => 'Logo / Animation', 'text' => 'Brand text', 'percent' => 'Progress estimate' )[ $ps_studio_layer ] ); ?></strong><span class="ps-layer-controls"><button type="button" class="button ps-layer-up" aria-label="<?php esc_attr_e( 'Move layer up', 'preloader-studio-animated-loading-screens' ); ?>">↑</button><button type="button" class="button ps-layer-down" aria-label="<?php esc_attr_e( 'Move layer down', 'preloader-studio-animated-loading-screens' ); ?>">↓</button></span></li>
                  <?php endforeach; ?>
                  </ul>
                  <p class="description"><?php esc_html_e( 'The text and percentage layers appear when their Show toggles are enabled. Loading percentage is an estimate.', 'preloader-studio-animated-loading-screens' ); ?></p>
                </section>
