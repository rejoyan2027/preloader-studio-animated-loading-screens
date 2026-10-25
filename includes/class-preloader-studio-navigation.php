<?php
/** Independent, opt-in mobile/tablet bottom navigation. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class PRELSTAN_Studio_Navigation {
    const OPTION = 'ps_studio_navigation';

    public static function styles() {
        return array(
            'soft-pill'=>'Soft Pill', 'glass-dock'=>'Glass Dock', 'neon-night'=>'Neon Night',
            'minimal-line'=>'Minimal Line', 'floating-island'=>'Floating Island', 'gradient-wave'=>'Gradient Wave',
            'rounded-cards'=>'Rounded Cards', 'bold-tab'=>'Bold Tab', 'outline-rail'=>'Outline Rail',
            'bubble-pop'=>'Bubble Pop', 'frosted-slate'=>'Frosted Slate', 'sunset-glow'=>'Sunset Glow',
            'prism-arc'=>'Prism Arc', 'citrus-pop'=>'Citrus Pop', 'ocean-tide'=>'Ocean Tide',
            'bento-dock'=>'Bento Dock', 'cyber-circuit'=>'Cyber Circuit', 'clay-bubble'=>'Clay Bubble',
            'sakura-petal'=>'Sakura Petal', 'emerald-capsule'=>'Emerald Capsule', 'retro-pixel'=>'Retro Pixel',
            'royal-velvet'=>'Royal Velvet', 'orbit-halo'=>'Orbit Halo', 'mono-editorial'=>'Mono Editorial',
        );
    }
    /** Trusted local SVG path data, not user-provided markup. */
    public static function icons() {
        return array(
            'home'=>'<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><path d="M9 21v-8h6v8"/>',
            'search'=>'<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
            'grid'=>'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
            'shopping-bag'=>'<path d="M4 8h16l-1 13H5z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/>',
            'cart'=>'<circle cx="9" cy="20" r="1"/><circle cx="19" cy="20" r="1"/><path d="M2 3h2l3 13h12l3-10H5"/>',
            'user'=>'<circle cx="12" cy="8" r="4"/><path d="M4 21c0-5 3-7 8-7s8 2 8 7"/>',
            'heart'=>'<path d="M20.8 4.6a5.3 5.3 0 0 0-7.5 0L12 5.9l-1.3-1.3a5.3 5.3 0 0 0-7.5 7.5L12 21l8.8-8.9a5.3 5.3 0 0 0 0-7.5z"/>',
            'bell'=>'<path d="M18 8a6 6 0 0 0-12 0c0 8-3 8-3 10h18c0-2-3-2-3-10z"/><path d="M10 21h4"/>',
            'menu'=>'<path d="M4 6h16M4 12h16M4 18h16"/>',
            'settings'=>'<circle cx="12" cy="12" r="3"/><path d="M12 2v3m0 14v3M2 12h3m14 0h3M4.9 4.9 7 7m10 10 2.1 2.1M19.1 4.9 17 7M7 17l-2.1 2.1"/>',
            'phone'=>'<path d="M6 3h4l1 5-2 2a16 16 0 0 0 5 5l2-2 5 1v4a3 3 0 0 1-3 3C10 21 3 14 3 6a3 3 0 0 1 3-3z"/>',
            'mail'=>'<rect x="2" y="5" width="20" height="14" rx="2"/><path d="m3 7 9 7 9-7"/>',
            'star'=>'<path d="m12 2 3.1 6.3L22 9.3l-5 4.9 1.2 6.8L12 17.8 5.8 21 7 14.2 2 9.3l6.9-1z"/>',
            'compass'=>'<circle cx="12" cy="12" r="10"/><path d="m15 9-2 5-4 1 2-5z"/>',
            'book'=>'<path d="M12 5a8 8 0 0 0-9-1v15a8 8 0 0 1 9 1 8 8 0 0 1 9-1V4a8 8 0 0 0-9 1zM12 5v15"/>',
            'calendar'=>'<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 10h18"/>',
            'gift'=>'<rect x="3" y="10" width="18" height="11" rx="1"/><path d="M2 7h20v4H2zM12 7v14M12 7c-8 0-7-7-3-6 2 .5 3 4 3 6zm0 0c8 0 7-7 3-6-2 .5-3 4-3 6z"/>',
            'location'=>'<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="2.5"/>',
            'briefcase'=>'<rect x="3" y="7" width="18" height="14" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 12h18"/>',
            'sparkles'=>'<path d="m12 2 2.3 6.7L21 11l-6.7 2.3L12 20l-2.3-6.7L3 11l6.7-2.3zM20 19l1 2m-2-1 2-1"/>',
            'message'=>'<path d="M20 11a8 8 0 0 1-8 8 9 9 0 0 1-4-.9L3 20l1.9-5A8 8 0 1 1 20 11z"/>',
            'globe'=>'<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a16 16 0 0 0 0 20m0-20a16 16 0 0 1 0 20"/>',
            'tag'=>'<path d="M21 13 13 21 3 11V3h8z"/><circle cx="7" cy="7" r="1"/>',
            'play'=>'<circle cx="12" cy="12" r="10"/><path d="m10 8 6 4-6 4z"/>',
        );
    }
    /** Font Awesome Free 5.15 Solid icon artwork (CC BY 4.0); see THIRD-PARTY-LICENSES.txt. */
    public static function fa_icons() {
        static $icons = null;
        if ( null !== $icons ) { return $icons; }
        $path = dirname( __DIR__ ) . '/assets/icons/font-awesome-free-solid.json';
        $json = is_readable($path) ? file_get_contents($path) : '';
        $parsed = $json ? json_decode($json, true) : array();
        $icons = is_array($parsed) ? $parsed : array();
        return $icons;
    }
    /** Preserve all v1.4.x icon IDs as well as new fa-* IDs. */
    public static function all_icons() {
        return array_merge(self::icons(), self::fa_icons());
    }
    public static function size_rules() {
        return array(
            'menu_height' => array(54,110,72),
            'menu_width' => array(70,100,100),
            'icon_size' => array(16,38,24),
            'label_size' => array(9,17,11),
            'item_gap' => array(0,16,5),
            'menu_radius' => array(0,36,22),
            'item_radius' => array(4,30,14),
            'bottom_offset' => array(0,34,0),
        );
    }
    /** Per-device controls use the global sizes until explicitly enabled. */
    public static function device_size_keys() {
        return array('menu_height', 'menu_width', 'icon_size', 'label_size', 'bottom_offset');
    }
    public static function motion_styles() {
        return array('none'=>'None', 'lift'=>'Gentle lift', 'pulse'=>'Soft pulse', 'bounce'=>'Spring bounce', 'glow'=>'Glow');
    }
    public static function indicators() {
        return array('none'=>'None', 'dot'=>'Dot below', 'underline'=>'Underline', 'top-line'=>'Top line');
    }
    public static function css_variables($s) {
        $map = array(
            '--psn-accent' => $s['accent'], '--psn-bg' => $s['background'],
            '--psn-text' => $s['text_color'], '--psn-active' => $s['active_color'],
        );
        foreach (self::size_rules() as $key => $rules) {
            $value = isset($s[$key]) ? (int)$s[$key] : $rules[2];
            $value = max($rules[0], min($rules[1], $value));
            $cssname = '--psn-' . str_replace('_','-',$key);
            $map[$cssname] = $value . ($key === 'menu_width' ? '%' : 'px');
        }
        foreach (array('mobile', 'tablet') as $device) {
            foreach (self::device_size_keys() as $key) {
                $ruleset = self::size_rules()[$key];
                $option = $device . '_' . $key;
                $n = isset($s[$option]) ? (int)$s[$option] : $ruleset[2];
                $n = max($ruleset[0], min($ruleset[1], $n));
                $map['--psn-' . $device . '-' . str_replace('_','-',$key)] = $n . ($key === 'menu_width' ? '%' : 'px');
            }
        }
        $map['--psn-motion-ms'] = max(150,min(900,(int)$s['motion_ms'])) . 'ms';
        $rules = array();
        foreach($map as $key=>$value) { $rules[]=$key.':'.$value; }
        return implode(';', $rules) . ';';
    }
    public static function svg( $name ) {
        $fa = (0 === strpos((string)$name, 'fa-')) ? self::fa_icons() : array();
        if (isset($fa[$name]) && is_array($fa[$name])) {
            $data = $fa[$name];
            return '<svg class="psn-icon psn-fa-icon" viewBox="'.esc_attr($data['viewBox']).'" aria-hidden="true" focusable="false" fill="currentColor" stroke="none"><path d="'.esc_attr($data['d']).'"/></svg>';
        }
        $icons = self::icons();
        $content = isset($icons[$name]) ? $icons[$name] : $icons['home'];
        return '<svg class="psn-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $content . '</svg>';
    }
    public static function defaults() {
        return array(
            'enabled'=>0, 'style'=>'soft-pill', 'devices'=>'both', 'accent'=>'#6657ed',
            'background'=>'#ffffff', 'text_color'=>'#5d6476', 'active_color'=>'#ffffff',
            'labels'=>1, 'override'=>'guide', 'selectors'=>'', 'hide_woodmart'=>1,
            'skip_checkout'=>1, 'skip_cart'=>1,
            'menu_height'=>72, 'menu_width'=>100, 'icon_size'=>24, 'label_size'=>11,
            'item_gap'=>5, 'menu_radius'=>22, 'item_radius'=>14, 'bottom_offset'=>0,
            'mobile_custom'=>0, 'tablet_custom'=>0,
            'mobile_menu_height'=>72,'mobile_menu_width'=>100,'mobile_icon_size'=>24,'mobile_label_size'=>11,'mobile_bottom_offset'=>0,
            'tablet_menu_height'=>72,'tablet_menu_width'=>100,'tablet_icon_size'=>24,'tablet_label_size'=>11,'tablet_bottom_offset'=>0,
            'motion'=>'none', 'indicator'=>'none', 'motion_ms'=>260, 'scroll_behavior'=>'hide-down',
            'items'=>array(
                array('label'=>'Home','icon'=>'home','type'=>'home','page_id'=>0,'url'=>'','new_tab'=>0),
                array('label'=>'Search','icon'=>'search','type'=>'search','page_id'=>0,'url'=>'','new_tab'=>0),
            ),
        );
    }
    public static function settings() {
        $saved = get_option( self::OPTION, array() );
        return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
    }
    public static function sanitize( $raw ) {
        $raw = is_array( $raw ) ? $raw : array();
        $d = self::defaults();
        $s = array();
        foreach ( array( 'enabled','labels','skip_checkout','skip_cart','mobile_custom','tablet_custom','hide_woodmart' ) as $key ) {
            $s[$key] = empty($raw[$key]) ? 0 : 1;
        }
        foreach ( array('style'=>array_keys(self::styles()), 'devices'=>array('mobile','tablet','both'), 'override'=>array('guide','auto','manual','off'), 'motion'=>array_keys(self::motion_styles()), 'indicator'=>array_keys(self::indicators()), 'scroll_behavior'=>array('always','hide-down')) as $key=>$choices ) {
            $value = isset($raw[$key]) && is_scalar($raw[$key]) ? sanitize_key($raw[$key]) : '';
            $s[$key] = in_array($value,$choices,true) ? $value : $d[$key];
        }
        foreach ( array('accent','background','text_color','active_color') as $key ) {
            $v = isset($raw[$key]) && is_scalar($raw[$key]) ? sanitize_hex_color($raw[$key]) : false;
            $s[$key] = $v ? $v : $d[$key];
        }
        foreach (self::size_rules() as $key=>$rules) {
            $input = isset($raw[$key]) && is_scalar($raw[$key]) ? (int)$raw[$key] : $rules[2];
            $s[$key] = max($rules[0], min($rules[1], $input));
        }
        foreach (array('mobile','tablet') as $device) {
            foreach (self::device_size_keys() as $key) {
                $r = self::size_rules()[$key];
                $name = $device . '_' . $key;
                $n = isset($raw[$name]) && is_scalar($raw[$name]) ? (int)$raw[$name] : $r[2];
                $s[$name] = max($r[0], min($r[1], $n));
            }
        }
        $s['motion_ms'] = max(150,min(900,isset($raw['motion_ms']) && is_scalar($raw['motion_ms']) ? (int)$raw['motion_ms'] : $d['motion_ms']));
        $s['selectors'] = isset($raw['selectors']) && is_scalar($raw['selectors']) ? PRELSTAN_Studio::clean_selectors($raw['selectors']) : '';
        $s['items'] = array();
        $raw_items = isset($raw['items']) && is_array($raw['items']) ? array_slice(array_values($raw['items']),0,6) : array();
        foreach ( $raw_items as $item ) {
            if ( ! is_array($item) ) { continue; }
            $label = isset($item['label']) && is_scalar($item['label']) ? sanitize_text_field($item['label']) : '';
            $label = function_exists('mb_substr') ? mb_substr($label,0,22) : substr($label,0,22);
            $icon = isset($item['icon']) && is_scalar($item['icon']) ? sanitize_key($item['icon']) : 'home';
            $type = isset($item['type']) && is_scalar($item['type']) ? sanitize_key($item['type']) : 'page';
            if ( ! isset(self::icons()[$icon]) && ! isset(self::fa_icons()[$icon]) ) { $icon='home'; }
            if ( ! in_array($type,array('home','search','page','url'),true) ) { $type='page'; }
            $page_id = isset($item['page_id']) ? absint($item['page_id']) : 0;
            if ( $page_id && ('publish' !== get_post_status($page_id) || 'page' !== get_post_type($page_id)) ) { $page_id=0; }
            $url = isset($item['url']) && is_scalar($item['url']) ? trim((string)$item['url']) : '';
            $url = preg_match('#^(https?://|/[^/])#i',$url) ? esc_url_raw($url,array('http','https')) : '';
            $s['items'][] = array('label'=>$label,'icon'=>$icon,'type'=>$type,'page_id'=>$page_id,'url'=>$url,'new_tab'=>empty($item['new_tab'])?0:1);
        }
        return $s;
    }
    public static function resolved_items( $settings ) {
        $out = array();
        foreach ( $settings['items'] as $item ) {
            if ( ! is_array($item) || empty($item['label']) ) { continue; }
            $href = '';
            $active = false;
            if ( $item['type'] === 'home' ) { $href = home_url('/'); $active = is_front_page(); }
            elseif ( $item['type'] === 'search' ) { $href = home_url('/?s='); $active = is_search(); }
            elseif ( $item['type'] === 'page' && ! empty($item['page_id']) ) {
                if ( get_post_status($item['page_id']) !== 'publish' ) { continue; }
                $href = get_permalink($item['page_id']);
                $active = (int)get_queried_object_id() === (int)$item['page_id'];
            } elseif ( $item['type'] === 'url' ) { $href = $item['url']; }
            if ( ! $href ) { continue; }
            $item['href'] = $href;
            $item['active'] = $active;
            $out[] = $item;
        }
        // Multiple menu entries may intentionally link to the same WordPress
        // page. Only one can represent the current page at a time. JavaScript
        // restores the exact clicked entry after navigating to a shared URL.
        $found_active = false;
        foreach ( $out as &$resolved ) {
            if ( ! empty( $resolved['active'] ) ) {
                if ( $found_active ) {
                    $resolved['active'] = false;
                } else {
                    $found_active = true;
                }
            }
        }
        unset( $resolved );
        return $out;
    }
    /** Read-only overview of saved menu destinations. No outgoing HTTP requests. */
    public static function audit( $settings ) {
        $settings = wp_parse_args( is_array( $settings ) ? $settings : array(), self::defaults() );
        $saved = isset( $settings['items'] ) && is_array( $settings['items'] ) ? $settings['items'] : array();
        // Admin-only diagnostics should also work in small standalone test fixtures
        // which do not implement frontend conditional tags.
        if ( function_exists( 'is_front_page' ) && function_exists( 'is_search' ) ) {
            $items = self::resolved_items( $settings );
        } else {
            $items = array();
            foreach ( $saved as $item ) {
                if ( ! is_array( $item ) || empty( $item['label'] ) ) { continue; }
                $type = isset( $item['type'] ) ? $item['type'] : '';
                $href = '';
                if ( 'home' === $type ) { $href = home_url( '/' ); }
                elseif ( 'search' === $type ) { $href = home_url( '/?s=' ); }
                elseif ( 'page' === $type && ! empty( $item['page_id'] ) && function_exists( 'get_permalink' ) ) { $href = get_permalink( $item['page_id'] ); }
                elseif ( 'url' === $type ) { $href = isset( $item['url'] ) ? (string) $item['url'] : ''; }
                if ( $href ) { $item['href'] = $href; $items[] = $item; }
            }
        }
        $seen = array();
        $duplicates = array();
        $external = 0;
        $site_host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
        foreach ( $items as $item ) {
            $href = isset( $item['href'] ) ? (string) $item['href'] : '';
            $destination = strtolower( rtrim( $href, '/' ) );
            if ( isset( $seen[ $destination ] ) ) {
                $duplicates[] = array( $seen[ $destination ], (string) $item['label'] );
            } else {
                $seen[ $destination ] = (string) $item['label'];
            }
            $host = wp_parse_url( $href, PHP_URL_HOST );
            if ( $host && $site_host && strtolower( $host ) !== strtolower( $site_host ) ) { ++$external; }
        }
        return array(
            'valid' => count( $items ),
            'incomplete' => max( 0, count( $saved ) - count( $items ) ),
            'duplicate_groups' => $duplicates,
            'external' => $external,
            'ready' => count( $items ) >= 2,
        );
    }
    public static function should_show( $s ) {
        if ( empty($s['enabled']) || is_admin() || is_feed() || is_embed() || wp_doing_ajax() || is_customize_preview() ) { return false; }
        if ( ! empty($s['skip_checkout']) && function_exists('is_checkout') && is_checkout() ) { return false; }
        if ( ! empty($s['skip_checkout']) && function_exists('is_order_received_page') && is_order_received_page() ) { return false; }
        if ( ! empty($s['skip_cart']) && function_exists('is_cart') && is_cart() ) { return false; }
        return count(self::resolved_items($s)) >= 2;
    }
    /** WoodMart and child themes: only replace the theme's known mobile toolbar, not unrelated controls. */
    public static function woodmart_theme() {
        if (!function_exists('wp_get_theme')) return false;
        $theme = wp_get_theme();
        if (!$theme || !method_exists($theme, 'get')) return false;
        $name = strtolower((string)$theme->get('Name'));
        $parent = strtolower((string)(method_exists($theme, 'get_template') ? $theme->get_template() : $theme->get('Template')));
        return strpos($name,'woodmart') !== false || $parent === 'woodmart';
    }
    public static function replace_woodmart_toolbar($s) {
        return self::woodmart_theme() && !empty($s['hide_woodmart']) && ($s['override'] ?? 'guide') !== 'off' && self::should_show($s);
    }
    public static function body_class($classes) {
        if (self::replace_woodmart_toolbar(self::settings())) $classes[]='psn-replace-woodmart';
        return $classes;
    }
    public static function init() {
        add_filter('body_class',array(__CLASS__,'body_class'));
        add_action('wp_enqueue_scripts',array(__CLASS__,'enqueue'));
        add_action('wp_body_open',array(__CLASS__,'render'),20);
        add_action('wp_footer',array(__CLASS__,'render'),20);
    }
    public static function enqueue() {
        $s = self::settings();
        if ( ! self::should_show($s) ) { return; }
        wp_enqueue_style('ps-studio-navigation',PRELSTAN_STUDIO_URL.'assets/css/navigation.css',array(),PRELSTAN_STUDIO_VERSION);
        wp_enqueue_script('ps-studio-navigation',PRELSTAN_STUDIO_URL.'assets/js/navigation.js',array(),PRELSTAN_STUDIO_VERSION,true);
    }
    private static $rendered = false;
    public static function render() {
        if ( self::$rendered ) { return; }
        $s = self::settings();
        if ( ! self::should_show($s) ) { return; }
        self::$rendered = true;
        $items = self::resolved_items($s);
        $vars = self::css_variables($s);
        echo '<nav id="ps-studio-bottom-nav" class="psn-nav psn-style--'.esc_attr($s['style']).' psn-device--'.esc_attr($s['devices']).(empty($s['labels'])?' psn-no-label':'').(!empty($s['mobile_custom'])?' psn-tuned-mobile':'').(!empty($s['tablet_custom'])?' psn-tuned-tablet':'').' psn-motion--'.esc_attr($s['motion']).' psn-indicator--'.esc_attr($s['indicator']).'" style="'.esc_attr($vars).'" aria-label="'.esc_attr__('Mobile bottom navigation','preloader-studio-animated-loading-screens').'" data-scroll-behavior="'.esc_attr($s['scroll_behavior']).'" data-override="'.esc_attr($s['override']).'" data-selectors="'.esc_attr(str_replace("\n",',',$s['selectors'])).'">';
        foreach ($items as $index => $item) {
            if ( $item['type'] === 'search' ) {
                echo '<button type="button" class="psn-item'.($item['active']?' is-active':'').'" data-psn-index="'.esc_attr($index).'" data-psn-search-toggle="1" aria-expanded="false" aria-controls="psn-search-form"';
            } else {
                echo '<a class="psn-item'.($item['active']?' is-active':'').'" data-psn-index="'.esc_attr($index).'" href="'.esc_url($item['href']).'"';
            }
            if ($item['active']) { echo ' aria-current="page"'; }
            if (!empty($item['new_tab']) && $item['type']==='url') { echo ' target="_blank" rel="noopener noreferrer"'; }
            echo '><span class="psn-icon-shell">'.wp_kses( self::svg( $item['icon'] ), array( 'svg' => array( 'class' => true, 'viewbox' => true, 'aria-hidden' => true, 'focusable' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ), 'path' => array( 'd' => true, 'fill' => true ), 'circle' => array( 'cx' => true, 'cy' => true, 'r' => true ), 'rect' => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true ), 'line' => array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true ), 'polyline' => array( 'points' => true ), 'polygon' => array( 'points' => true ), 'ellipse' => array( 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true ) ) ).'</span><span class="psn-label">'.esc_html($item['label']).'</span>'.($item['type']==='search'?'</button>':'</a>');
        }
        if ( in_array('search',array_column($items,'type'),true) ) {
            echo '<form id="psn-search-form" class="psn-search-panel" role="search" action="'.esc_url(home_url('/')).'" method="get" hidden>';
            echo '<label for="psn-search-input">'.esc_html__('Search this website','preloader-studio-animated-loading-screens').'</label>';
            echo '<div class="psn-search-controls"><input id="psn-search-input" name="s" type="search" required placeholder="'.esc_attr__('Search pages and posts','preloader-studio-animated-loading-screens').'" />';
            echo '<button type="submit">'.esc_html__('Search','preloader-studio-animated-loading-screens').'</button></div></form>';
        }
        echo '</nav>'; 
    }
}
