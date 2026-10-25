<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<aside class="ps-sidebar"><div class="ps-preview-panel">
                <div class="ps-preview-head"><div><h2><?php esc_html_e( 'Live Preview', 'preloader-studio-animated-loading-screens' ); ?></h2><p><?php esc_html_e( 'See changes instantly', 'preloader-studio-animated-loading-screens' ); ?></p></div><span class="ps-live-dot"></span></div>
                <div class="ps-device-bar" role="group" aria-label="<?php esc_attr_e( 'Device preview', 'preloader-studio-animated-loading-screens' ); ?>"><button type="button" class="ps-device is-active" data-device="desktop" aria-pressed="true"><span class="dashicons dashicons-desktop"></span> Desktop</button><button type="button" class="ps-device" data-device="tablet" aria-pressed="false"><span class="dashicons dashicons-tablet"></span> Tablet</button><button type="button" class="ps-device" data-device="mobile" aria-pressed="false"><span class="dashicons dashicons-smartphone"></span> Mobile</button><button type="button" class="ps-device-rotate" data-ps-rotate="1" title="Rotate portrait / landscape" aria-label="Rotate device preview">&#8635; Rotate</button></div>
                <div id="ps-device-stage" class="ps-device-stage ps-preloader-device-stage" data-device="desktop" aria-label="Realistic device screen preview"><button type="button" class="ps-device-close" data-ps-device-close aria-label="Close expanded preview">&times; Close</button>
                  <div id="ps-device-shell" class="ps-device-shell ps-device-shell--desktop">
                    <div id="ps-preview-screen" class="ps-preview-screen ps-bg--<?php echo esc_attr( $s['background_style'] ); ?>" style="--ps-color:<?php echo esc_attr( $s['color'] ); ?>;--ps-bg:<?php echo esc_attr( $s['background'] ); ?>;--ps-text:<?php echo esc_attr( $s['text_color'] ); ?>;--ps-size:<?php echo absint( $s['size'] ); ?>px;--ps-speed:<?php echo esc_attr( (string) ( 100 / $s['speed'] ) ); ?>">
                      <div class="ps-preview-center" id="ps-preview-center"></div>
                    </div>
                    <div class="ps-device-topbar" aria-hidden="true"><span class="ps-device-clock">9:41</span><span class="ps-device-notch"></span><span class="ps-device-signals">●●● ▰</span></div>
                    <div class="ps-device-browser" aria-hidden="true"><span class="ps-device-window-dots"><i></i><i></i><i></i></span><span class="ps-device-address">example.com</span><span class="ps-device-browser-menu">⋮</span></div>
                    <div class="ps-device-home-bar" aria-hidden="true"></div>
                  </div>
                </div>
                <p id="ps-device-meta" class="ps-device-meta" role="status" aria-live="polite">Desktop · 1440 × 900 CSS px</p>
                <div class="ps-preview-footer"><span id="ps-current-preset"><?php echo esc_html( $presets[ $s['preset'] ][0] ); ?></span><span class="ps-preview-actions"><button type="button" class="button" id="ps-open-device" aria-expanded="false">Expand Device</button><button type="button" class="button" id="ps-open-preview"><?php esc_html_e( 'Fullscreen Preview', 'preloader-studio-animated-loading-screens' ); ?></button></span></div>
              </div><div class="ps-aside-tip"><span class="dashicons dashicons-shield"></span><div><b><?php esc_html_e( 'Built with safety in mind', 'preloader-studio-animated-loading-screens' ); ?></b><p><?php esc_html_e( 'Auto-exit timeout, reduced-motion support, and no external animation libraries.', 'preloader-studio-animated-loading-screens' ); ?></p></div></div></aside>
