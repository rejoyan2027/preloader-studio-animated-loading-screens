<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div id="ps-fullscreen" class="ps-fullscreen ps-bg--<?php echo esc_attr( $s['background_style'] ); ?>" hidden role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Fullscreen preloader preview', 'preloader-studio-animated-loading-screens' ); ?>"><button type="button" id="ps-close-preview" class="ps-close-preview" aria-label="<?php esc_attr_e( 'Close preview', 'preloader-studio-animated-loading-screens' ); ?>">&times;</button><div id="ps-fullscreen-content"></div><button type="button" id="ps-test-exit" class="button ps-test-exit"><?php esc_html_e( 'Play exit transition', 'preloader-studio-animated-loading-screens' ); ?></button></div>

