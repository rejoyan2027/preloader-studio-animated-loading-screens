<?php
/** Core settings, animation markup, frontend lifecycle. */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class PRELSTAN_Studio {
    const OPTION = 'ps_studio_settings';
    const DESIGNS_OPTION = 'ps_studio_designs';
    private static $instance = null;

    /** @return array<string, mixed> */
    public static function defaults() {
        return array(
            'enabled'         => 1,
            'preset'          => 'orbit',
            'source'          => 'preset',
            'logo_id'         => 0,
            'custom_image_id' => 0,
            'image_effect'    => 'pulse',
            'background_style'=> 'solid',
            'gradient_color'  => '#ebdcff',
            'exit_effect'     => 'fade',
            'glow'            => 25,
            'font_size'       => 14,
            'letter_spacing'  => 1,
            'font_weight'     => '600',
            'layer_order'     => 'visual,text,percent',
            'size'            => 72,
            'mobile_custom' => 0, 'tablet_custom' => 0,
            'mobile_size' => 72, 'tablet_size' => 72,
            'mobile_speed' => 100, 'tablet_speed' => 100,
            'color'           => '#6657ed',
            'background'      => '#ffffff',
            'text_color'      => '#313648',
            'loading_text'    => 'Loading',
            'show_text'       => 0,
            'show_percent'    => 0,
            'speed'           => 100,
            'min_ms'          => 250,
            'max_ms'          => 4500,
            'fade_ms'         => 250,
            'show_on'         => 'every',
            'devices'         => 'all',
            'exclude_ids'     => '',
            'theme_override'  => 1, // Retained to migrate v1.0/v1.1 settings.
            'compat_mode'     => 'auto',
            'theme_selectors' => '',
            'performance_mode'=> 'balanced',
            'fast_skip_ms'    => 400,
            'skip_wc_cart'    => 1,
            'skip_wc_checkout'=> 1,
            'skip_wc_account' => 1,
            'skip_wc_shop'    => 0,
            'skip_wc_product' => 0,
        );
    }

    /** 36 deliberately different animation patterns. */
    public static function presets() {
        return array(
            'spinner'        => array( 'Spinner', 'Minimal', 1 ),
            'three-dots'     => array( 'Three Dots', 'Minimal', 3 ),
            'pulse-ring'     => array( 'Pulse Ring', 'Minimal', 2 ),
            'line-loader'    => array( 'Line Loader', 'Minimal', 1 ),
            'bouncing-ball'  => array( 'Bouncing Ball', 'Minimal', 1 ),
            'fading-circle'  => array( 'Fading Circle', 'Minimal', 12 ),
            'orbit'          => array( 'Orbit', 'Modern', 3 ),
            'liquid-blob'    => array( 'Liquid Blob', 'Modern', 1 ),
            'morph-square'   => array( 'Morph Square', 'Modern', 2 ),
            'dual-rings'     => array( 'Dual Rings', 'Modern', 2 ),
            'wave-bars'      => array( 'Wave Bars', 'Modern', 7 ),
            'elastic-loop'   => array( 'Elastic Loop', 'Modern', 1 ),
            'glass-orb'      => array( 'Glass Orb', 'Premium', 2 ),
            'neon-pulse'     => array( 'Neon Pulse', 'Premium', 2 ),
            'gradient-flow'  => array( 'Gradient Flow', 'Premium', 1 ),
            'particle-swirl' => array( 'Particle Swirl', 'Premium', 10 ),
            'infinity-loop'  => array( 'Infinity Loop', 'Premium', 2 ),
            'aurora-ring'    => array( 'Aurora Ring', 'Premium', 2 ),
            'cube-flip'      => array( 'Cube Flip', 'Creative', 4 ),
            'hexagon'        => array( 'Hexagon Motion', 'Creative', 2 ),
            'diamond'        => array( 'Diamond Rotate', 'Creative', 3 ),
            'dna-helix'      => array( 'DNA Helix', 'Creative', 8 ),
            'magnetic-dots'  => array( 'Magnetic Dots', 'Creative', 4 ),
            'folding-tiles'  => array( 'Folding Tiles', 'Creative', 4 ),
            'logo-reveal'    => array( 'Logo Reveal', 'Branding', 1 ),
            'logo-pulse'     => array( 'Logo Pulse', 'Branding', 2 ),
            'logo-draw'      => array( 'Logo Draw', 'Branding', 1 ),
            'text-typing'    => array( 'Text Typing', 'Branding', 1 ),
            'letter-build'   => array( 'Letter Assemble', 'Branding', 3 ),
            'brand-progress' => array( 'Brand Progress', 'Branding', 1 ),
            'circle-progress'=> array( 'Circular Progress', 'Advanced', 2 ),
            'digital-counter'=> array( 'Digital Counter', 'Advanced', 3 ),
            'segmented-ring' => array( 'Segmented Ring', 'Advanced', 12 ),
            'radar-scan'     => array( 'Radar Scan', 'Advanced', 2 ),
            'ripple-grid'    => array( 'Ripple Grid', 'Advanced', 9 ),
            'shutter'        => array( 'Shutter Reveal', 'Advanced', 5 ),
        );
    }

    public static function activate() {
        if ( false === get_option( self::OPTION, false ) ) {
            add_option( self::OPTION, self::defaults(), '', false );
        }
    }

    public static function settings() {
        $data = get_option( self::OPTION, array() );
        $data = is_array( $data ) ? $data : array();
        // Older versions used a binary theme_override preference.
        if ( ! isset( $data['compat_mode'] ) ) {
            $data['compat_mode'] = isset( $data['theme_override'] ) && ! $data['theme_override'] ? 'off' : 'auto';
        }
        return wp_parse_args( $data, self::defaults() );
    }

    /** Sanitize a comma/newline-separated list of simple ID and class CSS selectors. */
    public static function clean_selectors( $raw ) {
        $items = preg_split( '/[\r\n,]+/', (string) $raw );
        $valid = array();
        foreach ( $items as $candidate ) {
            $candidate = trim( $candidate );
            if ( preg_match( '/^[#.][a-zA-Z_-][a-zA-Z0-9_-]*$/D', $candidate ) ) {
                $valid[] = $candidate;
            }
        }
        return implode( "\n", array_slice( array_unique( $valid ), 0, 12 ) );
    }

    /** @return array<string, mixed> */
    public static function sanitize( $raw ) {
        $raw = is_array( $raw ) ? $raw : array();
        $d = self::defaults();
        $s = array();
        foreach ( array( 'enabled', 'show_text', 'show_percent', 'skip_wc_cart', 'skip_wc_checkout', 'skip_wc_account', 'skip_wc_shop', 'skip_wc_product', 'mobile_custom', 'tablet_custom' ) as $key ) {
            $s[ $key ] = ! empty( $raw[ $key ] ) ? 1 : 0;
        }
        $enums = array(
            'preset'       => array_keys( self::presets() ),
            'source'       => array( 'preset', 'site_logo', 'logo', 'custom_image' ),
            'image_effect' => array( 'pulse', 'spin', 'float', 'bounce', 'reveal', 'glow', 'swing', 'flip', 'breathe', 'pop', 'wobble', 'none' ),
            'background_style' => array( 'solid', 'gradient', 'aurora', 'glass' ),
            'exit_effect'  => array( 'fade', 'zoom-out', 'zoom-in', 'slide-up', 'slide-down', 'slide-left', 'slide-right', 'blur', 'rotate', 'curtain', 'split', 'shrink', 'wipe' ),
            'font_weight'  => array( '400', '500', '600', '700' ),
            'show_on'      => array( 'every', 'session', 'home' ),
            'devices'      => array( 'all', 'desktop', 'mobile' ),
            'compat_mode'  => array( 'auto', 'manual', 'off' ),
            'performance_mode' => array( 'balanced', 'fast', 'always' ),
        );
        // Saved v1.1 design configurations may not contain v1.2 fields.
        if ( ! isset( $raw['compat_mode'] ) && isset( $raw['theme_override'] ) ) {
            $raw['compat_mode'] = ! empty( $raw['theme_override'] ) ? 'auto' : 'off';
        }
        foreach ( $enums as $key => $values ) {
            $value = isset( $raw[ $key ] ) && is_scalar( $raw[ $key ] ) ? sanitize_key( $raw[ $key ] ) : '';
            $s[ $key ] = in_array( $value, $values, true ) ? $value : $d[ $key ];
        }
        $s['theme_override'] = 'off' === $s['compat_mode'] ? 0 : 1;
        foreach ( array( 'logo_id', 'custom_image_id' ) as $key ) {
            $id = isset( $raw[ $key ] ) ? absint( $raw[ $key ] ) : 0;
            $s[ $key ] = $id && wp_attachment_is_image( $id ) ? $id : 0;
        }
        foreach ( array( 'color', 'background', 'text_color', 'gradient_color' ) as $key ) {
            $value = isset( $raw[ $key ] ) && is_scalar( $raw[ $key ] ) ? sanitize_hex_color( $raw[ $key ] ) : false;
            $s[ $key ] = $value ? $value : $d[ $key ];
        }
        $ranges = array(
            'size' => array( 32, 180 ), 'speed' => array( 50, 200 ),
            'mobile_size' => array(32,180), 'tablet_size' => array(32,180),
            'mobile_speed' => array(50,200), 'tablet_speed' => array(50,200),
            'min_ms' => array( 0, 2000 ), 'max_ms' => array( 1500, 12000 ),
            'fade_ms' => array( 0, 1500 ), 'glow' => array( 0, 100 ),
            'font_size' => array( 10, 28 ), 'letter_spacing' => array( 0, 10 ),
            'fast_skip_ms' => array( 100, 1200 ),
        );
        foreach ( $ranges as $key => $range ) {
            $n = isset( $raw[ $key ] ) ? absint( $raw[ $key ] ) : $d[ $key ];
            $s[ $key ] = max( $range[0], min( $range[1], $n ) );
        }
        $s['min_ms'] = min( $s['min_ms'], $s['max_ms'] );
        $text = isset( $raw['loading_text'] ) && is_scalar( $raw['loading_text'] )
            ? sanitize_text_field( $raw['loading_text'] ) : $d['loading_text'];
        preg_match( '/^.{0,90}/us', $text, $truncated );
        $s['loading_text'] = isset( $truncated[0] ) ? $truncated[0] : $d['loading_text'];
        $ids = isset( $raw['exclude_ids'] ) && is_scalar( $raw['exclude_ids'] ) ? (string) $raw['exclude_ids'] : '';
        preg_match_all( '/\d+/', $ids, $matches );
        $s['exclude_ids'] = implode( ',', array_slice( array_unique( array_filter( array_map( 'absint', $matches[0] ) ) ), 0, 100 ) );
        $ordered = isset( $raw['layer_order'] ) && is_scalar( $raw['layer_order'] ) ? explode( ',', (string) $raw['layer_order'] ) : array();
        $layers = array();
        foreach ( $ordered as $layer ) {
            $layer = trim( $layer );
            if ( in_array( $layer, array( 'visual', 'text', 'percent' ), true ) && ! in_array( $layer, $layers, true ) ) {
                $layers[] = $layer;
            }
        }
        $s['layer_order'] = implode( ',', array_values( array_unique( array_merge( $layers, array( 'visual', 'text', 'percent' ) ) ) ) );
        $s['theme_selectors'] = isset( $raw['theme_selectors'] ) && is_scalar( $raw['theme_selectors'] )
            ? self::clean_selectors( $raw['theme_selectors'] ) : '';
        return $s;
    }

    public static function init() {
        if ( null !== self::$instance ) {
            return;
        }
        self::$instance = new self();
        if ( is_admin() ) {
            PRELSTAN_Studio_Admin::init();
        }
        add_action( 'wp_enqueue_scripts', array( self::$instance, 'enqueue_front' ) );
        add_action( 'wp_body_open', array( self::$instance, 'render_front' ), 1 );
        // Legacy-theme fallback (only if the theme does not call wp_body_open).
        add_action( 'wp_footer', array( self::$instance, 'render_front' ), 1 );
    }

    public static function should_show( $s ) {
        if ( empty( $s['enabled'] ) || is_admin() || is_feed() || is_embed() || wp_doing_ajax() || is_customize_preview() ) {
            return false;
        }
        if ( 'home' === $s['show_on'] && ! ( is_front_page() || is_home() ) ) {
            return false;
        }
        // All WooCommerce conditionals run after the WordPress query is ready.
        // WC Cart/Checkout Blocks are also covered by is_cart()/is_checkout().
        $wc_routes = array(
            'skip_wc_cart' => 'is_cart',
            'skip_wc_checkout' => 'is_checkout',
            'skip_wc_account' => 'is_account_page',
            'skip_wc_shop' => 'is_shop',
            'skip_wc_product' => 'is_product',
        );
        foreach ( $wc_routes as $setting => $conditional ) {
            if ( ! empty( $s[ $setting ] ) && function_exists( $conditional ) && call_user_func( $conditional ) ) {
                return false;
            }
        }
        if ( ! empty( $s['skip_wc_checkout'] ) && function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
            return false;
        }
        if ( is_singular() && in_array( get_queried_object_id(), array_map( 'absint', explode( ',', $s['exclude_ids'] ) ), true ) ) {
            return false;
        }
        return true;
    }

    public function enqueue_front() {
        $s = self::settings();
        if ( ! self::should_show( $s ) ) {
            return;
        }
        wp_enqueue_style( 'preloader-studio-front', PRELSTAN_STUDIO_URL . 'assets/css/frontend.css', array(), PRELSTAN_STUDIO_VERSION );
        wp_enqueue_script( 'preloader-studio-front', PRELSTAN_STUDIO_URL . 'assets/js/frontend.js', array(), PRELSTAN_STUDIO_VERSION, false );
    }

    /** True after first rendering, so footer fallback never prints duplicate overlays. */
    private $rendered = false;

    public function render_front() {
        if ( $this->rendered ) {
            return;
        }
        $s = self::settings();
        if ( ! self::should_show( $s ) ) {
            return;
        }
        $this->rendered = true;
        $image = self::selected_image_url( $s );
        $source = 'preset' === $s['source'] || '' === $image ? 'preset' : $s['source'];
        $attrs = array(
            'data-min'       => $s['min_ms'],
            'data-max'       => $s['max_ms'],
            'data-fade'      => $s['fade_ms'],
            'data-show-on'   => $s['show_on'],
            'data-devices'   => $s['devices'],
            'data-theme'     => 'off' !== $s['compat_mode'] ? '1' : '0',
            'data-theme-mode'=> $s['compat_mode'],
            'data-performance'=> $s['performance_mode'],
            'data-fast-skip' => $s['fast_skip_ms'],
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only diagnostics for administrators, not a form submission.
            'data-debug'     => isset( $_GET['ps_studio_diagnostics'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['ps_studio_diagnostics'] ) ) && current_user_can( 'manage_options' ) ? '1' : '0',
            'data-selectors' => str_replace( "\n", ',', $s['theme_selectors'] ),
            'data-source'    => $source,
            'data-exit'      => $s['exit_effect'],
        );
        $style = sprintf(
            '--ps-bg:%s;--ps-color:%s;--ps-text:%s;--ps-size:%dpx;--ps-speed:%s;--ps-fade:%dms;--ps-gradient:%s;--ps-glow:%d;--ps-font-size:%dpx;--ps-letter-spacing:%dpx;--ps-font-weight:%s;--ps-device-mobile-size:%dpx;--ps-device-tablet-size:%dpx;--ps-device-mobile-speed:%s;--ps-device-tablet-speed:%s;',
            esc_attr( $s['background'] ), esc_attr( $s['color'] ), esc_attr( $s['text_color'] ),
            absint( $s['size'] ), esc_attr( (string) ( 100 / $s['speed'] ) ), absint( $s['fade_ms'] ),
            esc_attr( $s['gradient_color'] ), absint( $s['glow'] ), absint( $s['font_size'] ), absint( $s['letter_spacing'] ), esc_attr( $s['font_weight'] ),
            absint($s['mobile_size']),absint($s['tablet_size']),esc_attr((string)(100 / $s['mobile_speed'])),esc_attr((string)(100 / $s['tablet_speed']))
        );
        echo '<div id="ps-studio-overlay" class="ps-overlay ps-bg--' . esc_attr( $s['background_style'] ) . ' ps-exit--' . esc_attr( $s['exit_effect'] ) . (!empty($s['mobile_custom'])?' ps-tuned-mobile':'') . (!empty($s['tablet_custom'])?' ps-tuned-tablet':'') . '" style="' . esc_attr( $style ) . '"';
        foreach ( $attrs as $key => $value ) {
            echo ' ' . esc_attr( $key ) . '="' . esc_attr( (string) $value ) . '"';
        }
        echo ' role="status" aria-live="polite" aria-label="' . esc_attr__( 'Page loading', 'preloader-studio-animated-loading-screens' ) . '">';
        echo '<div class="ps-content">';
        // Visual layer order is always a permutation of these three fixed elements.
        foreach ( explode( ',', $s['layer_order'] ) as $layer ) {
            if ( 'visual' === $layer ) {
                echo '<div class="ps-layer ps-layer--visual">';
                if ( 'preset' !== $source ) {
                    echo '<span class="ps-custom-image ps-image--' . esc_attr( $s['image_effect'] ) . '"><img src="' . esc_url( $image ) . '" width="' . absint( $s['size'] ) . '" height="' . absint( $s['size'] ) . '" alt="" decoding="async" /></span>';
                } else {
                    self::render_animation( $s['preset'], false, $s['loading_text'] );
                }
                echo '</div>';
            } elseif ( 'text' === $layer && $s['show_text'] ) {
                echo '<div class="ps-layer ps-layer--text"><span class="ps-loading-label">' . esc_html( $s['loading_text'] ) . '</span></div>';
            } elseif ( 'percent' === $layer && $s['show_percent'] ) {
                echo '<div class="ps-layer ps-layer--percent"><span class="ps-loading-percent" aria-hidden="true">0%</span></div>';
            }
        }
        echo '</div></div>';
    }

    public static function selected_image_url( $s ) {
        $id = 'site_logo' === $s['source'] ? absint( get_theme_mod( 'custom_logo', 0 ) )
            : ( 'logo' === $s['source'] ? absint( $s['logo_id'] )
            : ( 'custom_image' === $s['source'] ? absint( $s['custom_image_id'] ) : 0 ) );
        if ( ! $id || ! wp_attachment_is_image( $id ) ) {
            return '';
        }
        $url = wp_get_attachment_url( $id ); // Original file preserves GIF animation.
        return $url ? $url : '';
    }

    /** Reusable rendering for both gallery and front-end; span structures are controlled by static preset registry. */
    public static function render_animation( $preset, $preview = false, $brand_text = 'Loading' ) {
        $all = self::presets();
        if ( ! isset( $all[ $preset ] ) ) {
            $preset = 'orbit';
        }
        $count = $all[ $preset ][2];
        echo '<span class="ps-visual ps-visual--' . esc_attr( $preset ) . '" aria-hidden="true">';
        for ( $i = 1; $i <= $count; $i++ ) {
            echo '<i class="ps-particle ps-particle--' . absint( $i ) . '"></i>';
        }
        if ( 'text-typing' === $preset || 'logo-reveal' === $preset || 'letter-build' === $preset ) {
            echo '<b class="ps-visual-word">' . esc_html( $preview ? 'BRAND' : ( function_exists( 'mb_substr' ) ? mb_substr( $brand_text, 0, 14 ) : substr( $brand_text, 0, 14 ) ) ) . '</b>';
        }
        if ( 'brand-progress' === $preset || 'digital-counter' === $preset ) {
            echo '<b class="ps-visual-counter">0%</b>';
        }
        echo '</span>';
    }
}
