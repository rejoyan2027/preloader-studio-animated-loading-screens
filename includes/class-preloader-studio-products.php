<?php
/** WooCommerce Card Studio and opt-in single-product detail template. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class PRELSTAN_Studio_Products {
    const OPTION = 'ps_studio_product_layouts';
    const DESIGNS_OPTION = 'ps_studio_product_designs';
    public static function parts() { return array('category'=>'Category','title'=>'Product title','rating'=>'Rating','price'=>'Price','button'=>'Shop button'); }
    public static function hovers() { return array('classic'=>'Classic lift','none'=>'None','float'=>'Float up','image'=>'Image zoom','glow'=>'Soft glow','tilt'=>'Gentle tilt'); }

    public static function layouts() {
        return array(
            'atelier'=>'Atelier Editorial', 'prism'=>'Prism Glass', 'brutalist'=>'Brutalist Grid', 'aurora'=>'Aurora Drift',
            'magazine'=>'Magazine Split', 'terracotta'=>'Terracotta Craft', 'noir'=>'Noir Luxe', 'orbit'=>'Orbit Capsule',
            'bento'=>'Bento Stack', 'botanical'=>'Botanical Frame', 'neon'=>'Neon Commerce', 'paper'=>'Paper Cut',
            'monogram'=>'Monogram Plate', 'floating'=>'Floating Product', 'slanted'=>'Slanted Ribbon', 'mono'=>'Mono Index',
            'postcard'=>'Postcard Shop', 'spotlight'=>'Spotlight Stage', 'split'=>'Split Commerce', 'sunset'=>'Sunset Gradient',
            'sticker'=>'Sticker Pop', 'gallery'=>'Gallery Mat', 'ribbon'=>'Ribbon Edge', 'frosted'=>'Frosted Studio',
            'tech'=>'Tech Console', 'zen'=>'Zen Minimal', 'retro'=>'Retro Shop', 'tile'=>'Architect Tile',
            'luxury'=>'Luxury Serif', 'comic'=>'Comic Burst', 'ocean'=>'Ocean Wave', 'capsule'=>'Capsule Rail',
        );
    }
    public static function layout_group($id) {
        $groups=array(
            'editorial'=>array('atelier','magazine','monogram','mono','postcard','gallery','tile','luxury','zen','paper'),
            'modern'=>array('prism','aurora','bento','floating','frosted','capsule','orbit','split'),
            'bold'=>array('brutalist','neon','tech','comic','retro','sticker','noir'),
            'artistic'=>array('terracotta','botanical','slanted','sunset','ribbon','ocean','spotlight'),
        );
        foreach($groups as $group=>$members) if(in_array($id,$members,true)) return $group;
        return 'modern';
    }
    public static function defaults() {
        return array('enabled'=>0, 'single_enabled'=>0, 'single_mode'=>'force', 'scope'=>'all', 'layout'=>'atelier', 'accent'=>'#6754ed', 'columns'=>3, 'mobile_columns'=>2,
            'tablet_columns'=>2, 'gap'=>20, 'image_ratio'=>'4/5', 'show_rating'=>1, 'show_category'=>1,
            'show_badge'=>1, 'show_button'=>1, 'rounded'=>18,
            'hover'=>'classic','elements'=>'category,title,rating,price,button','category_rules'=>'{}');
    }
    public static function sanitize( $raw ) {
        $d=self::defaults(); if (!is_array($raw)) return $d;
        $d['enabled']=empty($raw['enabled'])?0:1;
        $d['single_enabled']=empty($raw['single_enabled'])?0:1;
        $d['single_mode']=isset($raw['single_mode']) && $raw['single_mode']==='compatible' ? 'compatible' : 'force';
        $d['scope']=isset($raw['scope']) && 'archives'===$raw['scope'] ? 'archives' : 'all';
        $d['layout']=isset($raw['layout']) && is_string($raw['layout']) && isset(self::layouts()[$raw['layout']]) ? $raw['layout'] : $d['layout'];
        $color=isset($raw['accent']) && is_string($raw['accent']) ? sanitize_hex_color($raw['accent']) : '';
        $d['accent']=$color ?: $d['accent'];
        foreach (array('columns'=>array(2,5),'mobile_columns'=>array(1,2),'tablet_columns'=>array(1,3),
                 'gap'=>array(8,40),'rounded'=>array(0,36)) as $k=>$range) {
            if(isset($raw[$k]) && is_scalar($raw[$k])) $d[$k]=max($range[0],min($range[1],(int)$raw[$k]));
        }
        $ratios=array('1/1','4/5','3/4','5/4');
        if(isset($raw['image_ratio']) && in_array($raw['image_ratio'],$ratios,true)) $d['image_ratio']=$raw['image_ratio'];
        foreach(array('show_rating','show_category','show_badge','show_button') as $k) $d[$k]=empty($raw[$k])?0:1;
        if(isset($raw['hover']) && is_string($raw['hover']) && isset(self::hovers()[$raw['hover']])) $d['hover']=$raw['hover'];
        $order=isset($raw['elements']) && is_string($raw['elements']) ? explode(',', $raw['elements']) : array_keys(self::parts());
        $order=array_values(array_unique(array_filter($order,function($v){return isset(self::parts()[$v]);})));
        foreach(array_keys(self::parts()) as $part) if(!in_array($part,$order,true)) $order[]=$part;
        $d['elements']=implode(',',$order);
        $json=isset($raw['category_rules']) ? $raw['category_rules'] : '{}';
        $rules=is_string($json)?json_decode($json,true):(is_array($json)?$json:array());
        $safe=array();
        if(is_array($rules)) foreach(array_slice($rules,0,60,true) as $term_id=>$layout) {
            if(!ctype_digit((string)$term_id)) continue;
            $id=absint($term_id);
            if(!$id || !is_string($layout) || !isset(self::layouts()[$layout])) continue;
            if(function_exists('term_exists') && !term_exists($id,'product_cat')) continue;
            $safe[(string)$id]=$layout;
        }
        $d['category_rules']=function_exists('wp_json_encode')?wp_json_encode((object)$safe):json_encode((object)$safe);
        return $d;
    }
    public static function settings() {
        // Option and category-rule validation are reused by multiple WooCommerce hooks
        // on the same request. Avoid revalidating up to 60 category terms per card.
        static $last_raw = null;
        static $last_settings = null;
        $raw = get_option(self::OPTION,array());
        // Detect changes made by settings saves in the same request, too.
        if (null !== $last_settings && $raw === $last_raw) return $last_settings;
        $last_raw = $raw;
        $last_settings = self::sanitize(is_array($raw) ? array_merge(self::defaults(),$raw):array());
        return $last_settings;
    }
    public static function init() {
        add_action('admin_post_ps_studio_products_save',array(__CLASS__,'save'));
        add_action('wp_ajax_ps_studio_product_preview',array(__CLASS__,'preview'));
        add_action('wp_ajax_ps_studio_product_designs',array(__CLASS__,'designs_ajax'));
        add_filter('wc_get_template_part',array(__CLASS__,'template_part'),999,3);
        // Alternative classic WooCommerce path used by some custom themes.
        add_filter('woocommerce_locate_template',array(__CLASS__,'locate_template'),999,4);
        add_filter('wc_get_template',array(__CLASS__,'wc_template'),999,5);
        add_shortcode('preloader_studio_products',array(__CLASS__,'product_shortcode'));
        add_action('wp_enqueue_scripts',array(__CLASS__,'frontend_assets'),20);
        add_filter('body_class',array(__CLASS__,'body_classes'));
        // Use a full, reversible plugin-owned template for WoodMart's custom single-product layout.
        // Force takeover happens before template_include, which theme builders can replace.
        // Scoped to explicitly enabled public single products; no theme files are changed.
        add_action('template_redirect',array(__CLASS__,'force_single_template'),9999);
        // Catch WooCommerce's full template loader as the last template_include filter.
        add_filter('template_include',array(__CLASS__,'single_template'),PHP_INT_MAX);
        // Some themes use the WordPress single_template filter before template_include.
        add_filter('single_template',array(__CLASS__,'single_template'),PHP_INT_MAX);
    }
    public static function wc_ready() { return class_exists('WooCommerce') && function_exists('wc_get_products'); }
    public static function is_archive() {
        if (!self::wc_ready()) return false;
        return (function_exists('is_shop') && is_shop()) || (function_exists('is_product_category') && is_product_category())
            || (function_exists('is_product_tag') && is_product_tag())
            || (function_exists('is_product_taxonomy') && is_product_taxonomy());
    }
    /** True only for public product requests; keep normal wp-admin and our AJAX editor untouched. */
    public static function frontend_request() {
        if (!is_admin()) return true;
        if (!wp_doing_ajax()) return false;
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only detection of WooCommerce AJAX product rendering.
        $action=isset($_REQUEST['action']) && is_scalar($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : '';
        // WoodMart's public AJAX product widgets and WooCommerce AJAX fragments only.
        return (bool) preg_match('/^(woodmart|wd_|woocommerce_|wc_)/', $action);
    }
    public static function enabled_here() {
        if (!self::wc_ready() || !self::frontend_request()) return false;
        $s=self::settings();
        if (empty($s['enabled'])) return false;
        if (function_exists('is_cart') && is_cart()) return false;
        if (function_exists('is_checkout') && is_checkout()) return false;
        return $s['scope']==='all' || self::is_archive();
    }
    /** Replace a classic WooCommerce loop item, including named hover parts from WoodMart. */
    public static function template_part($template,$slug,$name) {
        if ($slug!=='content' || !self::enabled_here()) return $template;
        if ($name==='product') return plugin_dir_path(PRELSTAN_STUDIO_FILE).'templates/content-product.php';
        if (is_string($name) && strpos($name,'product-')===0 && $name!=='product-single') {
            return plugin_dir_path(PRELSTAN_STUDIO_FILE).'templates/content-product-inner.php';
        }
        return $template;
    }
    /** Handle themes calling wc_get_template rather than wc_get_template_part. */
    public static function locate_template($template,$name,$path='',$default='') {
        if (!self::enabled_here() && !self::single_enabled_here()) return $template;
        if ($name==='single-product.php' && self::single_enabled_here()) {
            $single = plugin_dir_path(PRELSTAN_STUDIO_FILE).'templates/single-product.php';
            return is_readable($single) ? $single : $template;
        }
        if (!self::enabled_here()) return $template;
        if ($name==='content-product.php') return plugin_dir_path(PRELSTAN_STUDIO_FILE).'templates/content-product.php';
        if(is_string($name) && strpos($name,'content-product-')===0 && substr($name,-4)==='.php')
            return plugin_dir_path(PRELSTAN_STUDIO_FILE).'templates/content-product-inner.php';
        return $template;
    }
    public static function wc_template($template,$name,$args=array(),$path='',$default='') {
        return self::locate_template($template,$name,$path,$default);
    }
    /** Independently opt-in: preserve theme's single product until specifically enabled. */
    public static function single_enabled_here() {
        if (!self::wc_ready() || !self::frontend_request()) return false;
        $s=self::settings();
        return !empty($s['single_enabled'])
            && function_exists('is_singular') && is_singular('product');
    }
    /**
     * A strict opt-in route for themes (notably WoodMart Layouts Builder and
     * Elementor templates) whose template rendering bypasses PHP template filters.
     * A public product request is rendered by our normal WordPress header/footer
     * and plugin-owned main template *before* a competing theme template runs.
     * Called late so canonical redirects, authentication and WC cart processing
     * have an opportunity to finish first.
     */
    public static function is_single_editor_request() {
        if (is_admin() || (function_exists('wp_doing_ajax') && wp_doing_ajax())) return true;
        if (defined('REST_REQUEST') && REST_REQUEST) return true;
        if (function_exists('is_feed') && is_feed()) return true;
        if (function_exists('is_embed') && is_embed()) return true;
        if (function_exists('is_preview') && is_preview()) return true;
        foreach (array('elementor-preview','elementor_library','vc_editable','wpb-preview','wc-ajax') as $key) {
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only Elementor preview detection.
            if (isset($_GET[$key])) return true;
        }
        return false;
    }
    public static function force_single_allowed() {
        if (!self::single_enabled_here()) return false;
        $s=self::settings();
        if ($s['single_mode']!=='force' || self::is_single_editor_request()) return false;
        return (bool) apply_filters('ps_studio_force_single_allowed',true);
    }
    public static function force_single_template() {
        if (!self::force_single_allowed()) return;
        $file=plugin_dir_path(PRELSTAN_STUDIO_FILE).'templates/single-product.php';
        if (!is_readable($file)) return;
        // Elementor/WoodMart never get a chance to replace the main template.
        status_header(200);
        include $file;
        exit;
    }
    /** Priority higher than WooCommerce and theme template_include filters. No theme files edited. */
    public static function single_template($template) {
        if (!self::single_enabled_here() || self::is_single_editor_request()) return $template;
        $ours=plugin_dir_path(PRELSTAN_STUDIO_FILE).'templates/single-product.php';
        return is_readable($ours) ? $ours : $template;
    }
    /**
     * Accessible horizontal tabs, independent from WoodMart's accordion overrides.
     * All WooCommerce tab callbacks are preserved, including Reviews and 3rd-party tabs.
     * Below-the-fold images get lazy hints without deferring SEO-visible HTML or forms.
     */
    public static function render_single_tabs() {
        $tabs = apply_filters('woocommerce_product_tabs',array());
        if (!is_array($tabs) || !$tabs) return;
        $valid=array();
        foreach ($tabs as $key=>$tab) {
            if (!is_array($tab) || empty($tab['title']) || empty($tab['callback']) || !is_callable($tab['callback'])) continue;
            $slug=sanitize_key($key);
            if (!$slug || isset($valid[$slug])) continue;
            $valid[$slug]=$tab;
        }
        if (!$valid) return;
        $product_id=function_exists('get_the_ID') ? absint(get_the_ID()) : 0;
        echo '<section class="psp-detail-tabs" aria-label="'.esc_attr__('Product details','preloader-studio-animated-loading-screens').'">';
        echo '<div class="psp-detail-tabs__nav" role="tablist" aria-label="'.esc_attr__('Product information tabs','preloader-studio-animated-loading-screens').'">';
        $i=0;
        foreach($valid as $slug=>$tab) {
            $id='psp-tab-'.$product_id.'-'.$slug;
            $panel='psp-panel-'.$product_id.'-'.$slug;
            printf('<button type="button" id="%s" class="psp-detail-tabs__tab%s" role="tab" aria-controls="%s" aria-selected="%s" tabindex="%s">%s</button>',
                esc_attr($id), $i===0?' is-active':'', esc_attr($panel), $i===0?'true':'false', $i===0?'0':'-1', esc_html(wp_strip_all_tags($tab['title'])));
            $i++;
        }
        echo '</div>';
        $i=0;
        foreach($valid as $slug=>$tab) {
            $id='psp-tab-'.$product_id.'-'.$slug;
            $panel='psp-panel-'.$product_id.'-'.$slug;
            printf('<div id="%s" class="psp-detail-tabs__panel%s" role="tabpanel" aria-labelledby="%s" tabindex="0"%s>',
                esc_attr($panel), $i===0?' is-active':'',esc_attr($id),'');
            ob_start();
            call_user_func($tab['callback'],$slug,$tab);
            $html=ob_get_clean();
            if (class_exists('WP_HTML_Tag_Processor')) {
                $processor=new WP_HTML_Tag_Processor($html);
                while($processor->next_tag('IMG')) {
                    $processor->set_attribute('loading','lazy');
                    $processor->set_attribute('decoding','async');
                    $processor->set_attribute('fetchpriority','low');
                }
                // Heavy product-description embeds should not initialize before
                // their details tab is viewed/scrolled into view.
                while($processor->next_tag('IFRAME')) {
                    $processor->set_attribute('loading','lazy');
                }
                $html=$processor->get_updated_html();
            }
            // HTML is produced by WooCommerce/extension callbacks, not user input.
            echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</div>';
            $i++;
        }
        // All panels remain readable without JavaScript; enqueued JS controls visibility.
        echo '</section>';
    }

    public static function single_css_vars($s) {
        return self::css_vars($s).'--pss-accent:'.$s['accent'].';--pss-radius:'.$s['rounded'].'px;';
    }
    public static function body_classes($classes) {
        if(self::enabled_here()) { $s=self::settings(); $classes[]='ps-products-enabled'; $classes[]='ps-products--'.$s['layout']; }
        if(self::single_enabled_here()) $classes[]='psp-single-active';
        return $classes;
    }
    /** Stable, builder-independent fallback for Elementor shortcode widgets and custom theme pages. */
    public static function product_shortcode($attrs=array()) {
        if (!self::wc_ready()) return '';
        $atts=shortcode_atts(array('limit'=>'8','category'=>'','columns'=>''),$attrs,'preloader_studio_products');
        $limit=max(1,min(24,(int)$atts['limit']));
        $s=self::settings();
        // Page-builder shortcode widgets can be absent from post_content.
        if (function_exists('wp_enqueue_style')) wp_enqueue_style('ps-studio-product-cards',PRELSTAN_STUDIO_URL.'assets/css/product-cards.css',array(),PRELSTAN_STUDIO_VERSION);
        if ($atts['columns']!=='' && ctype_digit((string)$atts['columns'])) $s['columns']=max(1,min(5,(int)$atts['columns']));
        $args=array('limit'=>$limit,'status'=>'publish','orderby'=>'date','order'=>'DESC');
        $cat=sanitize_title((string)$atts['category']);
        if($cat!=='') $args['category']=array($cat);
        $products=wc_get_products($args);
        ob_start();
        echo '<div class="woocommerce psp-gallery-real psp-embedded-products" style="'.esc_attr(self::css_vars($s)).'"><ul class="products ps-product-grid">';
        foreach ($products as $product) {
            if (is_object($product) && method_exists($product,'is_visible') && $product->is_visible()) self::card($product,$s,false);
        }
        echo '</ul></div>';
        return ob_get_clean();
    }
    public static function css_vars($s) {
        $parts=explode(',', $s['elements']); $orders='';
        foreach($parts as $i=>$part) $orders.='--psp-order-'.$part.':'.($i+1).';';
        return $orders.'--psp-accent:'.$s['accent'].';--psp-columns:'.$s['columns'].';--psp-mobile-columns:'.$s['mobile_columns'].';--psp-tablet-columns:'.$s['tablet_columns'].';--psp-gap:'.$s['gap'].'px;--psp-radius:'.$s['rounded'].'px;--psp-ratio:'.$s['image_ratio'].';';
    }
    public static function frontend_assets() {
        if(!self::wc_ready() || is_admin()) return;
        $single = self::single_enabled_here();
        $cards = self::enabled_here();
        $page = function_exists('get_post') && function_exists('get_queried_object_id')
            ? get_post(get_queried_object_id()) : null;
        $shortcode = $page && isset($page->post_content) && function_exists('has_shortcode')
            && has_shortcode($page->post_content,'preloader_studio_products');
        // Keep all-loop CSS available for builders which render product grids on
        // arbitrary pages; omit it when overrides are disabled and unused.
        if (!$cards && !$single && !$shortcode) return;
        $s=self::settings();
        if ($cards || $shortcode) {
            wp_enqueue_style('ps-studio-product-cards',PRELSTAN_STUDIO_URL.'assets/css/product-cards.css',array(),PRELSTAN_STUDIO_VERSION);
            wp_add_inline_style('ps-studio-product-cards','body.ps-products-enabled{'.self::css_vars($s).'}');
        }
        if ($single) {
            // Single-page styling has no dependency on the 32-card gallery CSS.
            wp_enqueue_style('ps-studio-single-product',PRELSTAN_STUDIO_URL.'assets/css/single-product.css',array(),PRELSTAN_STUDIO_VERSION);
            wp_enqueue_script('ps-studio-single-tabs',PRELSTAN_STUDIO_URL.'assets/js/single-tabs.js',array(),PRELSTAN_STUDIO_VERSION,true);
        }
    }
    public static function save() {
        if(!current_user_can('manage_options')) wp_die(esc_html__('Permission denied.','preloader-studio-animated-loading-screens'),403);
        check_admin_referer('ps_studio_products_save','ps_products_nonce');
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- check_admin_referer verified before reading; settings sanitized by self::sanitize.
        $raw=isset($_POST['ps_products']) && is_array($_POST['ps_products']) ? wp_unslash($_POST['ps_products']) : array();
        $settings=self::sanitize($raw);
        if(!self::wc_ready()) $settings['enabled']=0;
        update_option(self::OPTION,$settings,false);
        wp_safe_redirect(add_query_arg(array('page'=>'preloader-studio-products','updated'=>'products'),admin_url('admin.php')));
        exit;
    }
    public static function enqueue() {
        wp_enqueue_style('ps-studio-product-cards',PRELSTAN_STUDIO_URL.'assets/css/product-cards.css',array(),PRELSTAN_STUDIO_VERSION);
        wp_enqueue_style('ps-studio-product-admin',PRELSTAN_STUDIO_URL.'assets/css/product-admin.css',array('ps-studio-product-cards'),PRELSTAN_STUDIO_VERSION);
        wp_enqueue_script('ps-studio-product-admin',PRELSTAN_STUDIO_URL.'assets/js/product-admin.js',array(),PRELSTAN_STUDIO_VERSION,true);
        wp_localize_script('ps-studio-product-admin','prelstan_product_studio',array(
            'previewUrl'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('ps_studio_product_preview'),
            'designNonce'=>wp_create_nonce('ps_studio_product_designs'),
            'savedDesigns'=>self::saved_designs()));
    }
    /** Local demo photography, used only in editor/preview; no remote calls. */
    public static function demo_catalog() {
        return array(
            array('name'=>'Studio Wireless Headphones','price'=>'$149.00','old'=>'$189.00','category'=>'Audio & Sound','image'=>'headphones.webp','sale'=>true),
            array('name'=>'Everyday Leather Sneakers','price'=>'$89.00','old'=>'','category'=>'Footwear','image'=>'sneakers.webp','sale'=>false),
            array('name'=>'Handcrafted Ceramic Vase','price'=>'$59.00','old'=>'','category'=>'Home Decor','image'=>'vase.webp','sale'=>false),
            array('name'=>'Minimalist Classic Watch','price'=>'$179.00','old'=>'$220.00','category'=>'Accessories','image'=>'watch.webp','sale'=>true),
            array('name'=>'Everyday Digital Camera','price'=>'$299.00','old'=>'','category'=>'Electronics','image'=>'camera.webp','sale'=>false),
            array('name'=>'Warm Glow Table Lamp','price'=>'$119.00','old'=>'','category'=>'Lighting','image'=>'lamp.webp','sale'=>false),
        );
    }
    public static function gallery_product() {
        if (!self::wc_ready()) return null;
        $items=wc_get_products(array('limit'=>12,'status'=>'publish','orderby'=>'date','order'=>'DESC'));
        if (!is_array($items)) return null;
        foreach($items as $item) {
            if(is_object($item) && method_exists($item,'is_visible') && $item->is_visible() && $item->get_image_id()) return $item;
        }
        return null;
    }
    /** Gallery renders the identical product card component used by the AJAX preview and shop. */
    public static function gallery_card($layout,$index,$s,$real_product=null) {
        $s['layout']=$layout;
        ob_start();
        if($real_product) self::card($real_product,$s,true,true);
        else {
            $catalog=self::demo_catalog();
            self::demo_card($catalog[$index % count($catalog)]['name'],$index % count($catalog),$s);
        }
        // These are demonstration cards inside a selectable gallery, not shopping actions.
        $html=ob_get_clean();
        return preg_replace('/<a\b(?![^>]*\btabindex=)/i', '<a tabindex="-1"', $html);
    }
    /** Draft presets: they never publish or change the live shop until Save Product Layout. */
    public static function saved_designs() {
        $list=get_option(self::DESIGNS_OPTION,array());
        if(!is_array($list)) return array();
        return array_slice($list,0,20,true);
    }
    public static function designs_ajax() {
        if(!current_user_can('manage_options')) wp_send_json_error(array('message'=>'Permission denied.'),403);
        check_ajax_referer('ps_studio_product_designs','nonce');
        $op=isset($_POST['operation']) ? sanitize_key(wp_unslash($_POST['operation'])) : '';
        $saved=self::saved_designs();
        if($op==='save') {
            $name=isset($_POST['name']) && is_scalar($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
            $name=substr($name,0,60);
            if($name==='' || count($saved)>=20) wp_send_json_error(array('message'=>'Choose a name; at most 20 drafts are supported.'),400);
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- check_ajax_referer verified; self::sanitize validates every field.
            $raw=isset($_POST['settings']) && is_array($_POST['settings']) ? wp_unslash($_POST['settings']) : array();
            $design=self::sanitize($raw);
            $design['enabled']=0;
            $id='d'.str_replace('.','',uniqid('',true));
            $saved[$id]=array('name'=>$name,'settings'=>$design);
        } elseif($op==='delete') {
            $id=isset($_POST['id']) && is_scalar($_POST['id']) ? sanitize_key(wp_unslash($_POST['id'])) : '';
            if(!isset($saved[$id])) wp_send_json_error(array('message'=>'Design not found.'),404);
            unset($saved[$id]);
        } else wp_send_json_error(array('message'=>'Unknown operation.'),400);
        update_option(self::DESIGNS_OPTION,$saved,false);
        wp_send_json_success(array('designs'=>$saved));
    }
    public static function layout_for_product($product,$settings) {
        $rules=json_decode($settings['category_rules'],true);
        if(!is_array($rules) || !$rules || !method_exists($product,'get_category_ids')) return $settings['layout'];
        foreach($product->get_category_ids() as $id) if(isset($rules[(string)$id])) return $rules[(string)$id];
        return $settings['layout'];
    }
    public static function admin_page() {
        $s=self::settings(); $ready=self::wc_ready();
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only admin notice after nonce-protected save.
        if(isset($_GET['updated']) && 'products'===sanitize_key(wp_unslash($_GET['updated']))) echo '<div class="notice notice-success"><p>Product Layout settings saved.</p></div>';
        if(!$ready) echo '<div class="notice notice-warning"><p><strong>WooCommerce is not active.</strong> Preview uses demo sample products. Activate WooCommerce before enabling a live override.</p></div>';
        if($ready && function_exists('wp_is_block_theme') && wp_is_block_theme()) echo '<div class="notice notice-info"><p><strong>Block-theme compatibility:</strong> this override affects classic WooCommerce product loops, not Product Collection blocks. Check your Shop template before publishing.</p></div>';
        ?>
        <div class="psp-workspace">
          <div class="psp-intro"><div><span class="psp-kicker">PRODUCT DESIGN STUDIO · 32 LAYOUTS</span><h2>Make every product feel premium.</h2><p>Select a truly distinct shop-card design, fine-tune the grid and preview products in their actual card markup. Publish when ready.</p></div><span class="psp-live-indicator"><?php echo !empty($s['enabled'])&&$ready?($s['scope']==='all'?'● Product loops enabled':'● Shop archives enabled'):'○ Draft / disabled'; ?></span></div>
          <div class="psp-detail-status" role="status"><span class="psp-detail-status-dot <?php echo !empty($s['single_enabled']) ? 'on' : 'off'; ?>" aria-hidden="true"></span><div><strong>Single Product Page: <?php echo !empty($s['single_enabled']) ? 'Plugin layout ENABLED' : 'Theme layout is still ACTIVE'; ?></strong><span><?php echo !empty($s['single_enabled']) ? 'Your selected product style will be used on the detail page.' : 'To replace WoodMart when you open a product, turn on the Single Product checkbox in Publish &amp; Theme Override.'; ?></span></div><a href="#psp-publish">Change this setting →</a></div>
          <nav class="psp-studio-jump" aria-label="Product Studio sections"><a href="#psp-gallery">32 Layouts</a><a href="#psp-builder">Visual Builder</a><a href="#psp-rules">Category Rules</a><a href="#psp-designs">My Designs</a><a href="#psp-publish">Publish</a></nav>
          <form id="psp-editor" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="ps_studio_products_save" />
            <?php wp_nonce_field('ps_studio_products_save','ps_products_nonce'); ?>
            <input type="hidden" id="psp-layout-id" name="ps_products[layout]" value="<?php echo esc_attr($s['layout']); ?>" />
            <?php $gallery_product=self::gallery_product(); ?>
            <section class="psp-panel psp-gallery-panel"><div class="psp-panel-heading"><div><h3>01 · Explore 32 Real Product Card Designs</h3><p class="psp-gallery-intro">Every design shows the actual product photo, category, rating, price and shopping button — not a decorative placeholder.</p></div><span class="psp-gallery-source"><?php echo $gallery_product?'Using your WooCommerce product':'Using included demo product photos'; ?></span></div>
              <div class="psp-gallery-toolbar"><span>Choose a design to see it on your entire shop in the live preview.</span><span class="psp-gallery-counter" id="psp-gallery-counter">32 / 32 designs</span></div>
              <div class="psp-gallery-filters" role="group" aria-label="Filter product layouts">
                <button type="button" data-psp-filter="all" class="is-active" aria-pressed="true">All <span>32</span></button>
                <button type="button" data-psp-filter="editorial" aria-pressed="false">Editorial <span>10</span></button>
                <button type="button" data-psp-filter="modern" aria-pressed="false">Modern <span>8</span></button>
                <button type="button" data-psp-filter="bold" aria-pressed="false">Bold <span>7</span></button>
                <button type="button" data-psp-filter="artistic" aria-pressed="false">Artistic <span>7</span></button>
              </div>
              <div class="psp-gallery" id="psp-gallery">
                <?php $idx=0; foreach(self::layouts() as $id=>$name): ?>
                <div role="button" tabindex="0" class="psp-style-option <?php echo $id===$s['layout']?'is-selected':'';?>" data-layout="<?php echo esc_attr($id); ?>" data-group="<?php echo esc_attr(self::layout_group($id)); ?>" aria-label="Select <?php echo esc_attr($name); ?> product layout" aria-pressed="<?php echo $id===$s['layout']?'true':'false'; ?>">
                  <div class="psp-gallery-real woocommerce" aria-hidden="true" style="<?php echo esc_attr(self::css_vars($s)); ?>"><ul class="psp-gallery-product-list"><?php echo wp_kses_post( self::gallery_card($id,$idx,$s,$gallery_product) ); ?></ul></div>
                  <span class="psp-style-caption"><strong><?php echo esc_html($name);?></strong><small><?php echo esc_html(str_pad((string)($idx+1),2,'0',STR_PAD_LEFT)); ?></small></span>
                </div>
                <?php $idx++; endforeach; ?>
              </div>
            </section>
            <div class="psp-editor-columns"><main class="psp-editor-main">
              <section class="psp-panel"><div class="psp-panel-heading"><h3>02 · Grid & Details</h3><span>All changes update the preview instantly</span></div>
                <div class="psp-controls">
                  <label>Accent Color <input type="color" name="ps_products[accent]" value="<?php echo esc_attr($s['accent']); ?>"></label>
                  <label>Desktop Columns <select name="ps_products[columns]"><?php foreach(array(2,3,4,5) as $i) echo '<option value="'.esc_attr( $i ).'" '.selected($s['columns'],$i,false).'>'.esc_html( $i ).' columns</option>'; ?></select></label>
                  <label>Tablet Columns <select name="ps_products[tablet_columns]"><?php foreach(array(1,2,3) as $i) echo '<option value="'.esc_attr( $i ).'" '.selected($s['tablet_columns'],$i,false).'>'.esc_html( $i ).' columns</option>'; ?></select></label>
                  <label>Mobile Columns <select name="ps_products[mobile_columns]"><?php foreach(array(1,2) as $i) echo '<option value="'.esc_attr( $i ).'" '.selected($s['mobile_columns'],$i,false).'>'.esc_html( $i ).' columns</option>'; ?></select></label>
                  <label>Image Aspect <select name="ps_products[image_ratio]"><?php foreach(array('1/1'=>'Square','4/5'=>'Portrait','3/4'=>'Tall portrait','5/4'=>'Landscape') as $k=>$label) echo '<option value="'.esc_attr($k).'" '.selected($s['image_ratio'],$k,false).'>'.esc_html($label).'</option>'; ?></select></label>
                  <label>Card Radius <input type="range" name="ps_products[rounded]" min="0" max="36" step="1" value="<?php echo esc_attr($s['rounded']); ?>"><output><?php echo esc_html($s['rounded']); ?>px</output></label>
                  <label>Grid Spacing <input type="range" name="ps_products[gap]" min="8" max="40" step="1" value="<?php echo esc_attr($s['gap']); ?>"><output><?php echo esc_html($s['gap']); ?>px</output></label>
                </div>
                <div class="psp-toggles">
                <?php foreach(array('show_rating'=>'Star rating','show_category'=>'Category','show_badge'=>'Sale badge','show_button'=>'Add to cart / product button') as $key=>$label): ?><label><input type="checkbox" name="ps_products[<?php echo esc_attr($key);?>]" value="1" <?php checked($s[$key],1);?>> <?php echo esc_html($label); ?></label><?php endforeach; ?>
                </div>
              </section>
              <section class="psp-panel" id="psp-builder"><div class="psp-panel-heading"><h3>03 · Visual Card Builder</h3><span>Drag elements or use arrows · preview before publishing</span></div>
                <p class="description">Arrange the order of product details. You can hide category, rating and button with the display switches above.</p>
                <input type="hidden" name="ps_products[elements]" id="psp-elements-input" value="<?php echo esc_attr($s['elements']); ?>">
                <ol class="psp-element-sort" id="psp-element-sort" aria-label="Card content order">
                <?php foreach(explode(',',$s['elements']) as $part): ?><li draggable="true" data-part="<?php echo esc_attr($part); ?>"><span class="psp-drag-handle" aria-hidden="true">⠿</span><strong><?php echo esc_html(self::parts()[$part]); ?></strong><span class="psp-sort-buttons"><button type="button" class="button psp-move-up" aria-label="Move up">↑</button><button type="button" class="button psp-move-down" aria-label="Move down">↓</button></span></li><?php endforeach; ?>
                </ol>
                <div class="psp-controls"><label>Hover Animation <select name="ps_products[hover]" id="psp-hover-effect"><?php foreach(self::hovers() as $key=>$name) echo '<option value="'.esc_attr($key).'" '.selected($s['hover'],$key,false).'>'.esc_html($name).'</option>'; ?></select></label></div>
              </section>
              <section class="psp-panel" id="psp-rules"><div class="psp-panel-heading"><h3>04 · Category Design Rules</h3><span>Use a different card style for each product category</span></div>
                <p class="description">Optional. Your main layout is the fallback for products without a matching rule. These rules affect classic WooCommerce product loops.</p>
                <input type="hidden" id="psp-category-rules" name="ps_products[category_rules]" value="<?php echo esc_attr($s['category_rules']); ?>">
                <div id="psp-rule-list" class="psp-rule-list"></div>
                <div class="psp-rule-actions"><select id="psp-rule-category" aria-label="Choose product category"><option value="">Select product category…</option>
                <?php if($ready) { $terms=get_terms(array('taxonomy'=>'product_cat','hide_empty'=>false,'number'=>100)); if(!is_wp_error($terms)) foreach($terms as $term) echo '<option value="'.absint($term->term_id).'">'.esc_html($term->name).'</option>'; } ?>
                </select><select id="psp-rule-layout" aria-label="Choose category layout"><?php foreach(self::layouts() as $key=>$name) echo '<option value="'.esc_attr($key).'">'.esc_html($name).'</option>'; ?></select><button type="button" class="button" id="psp-add-rule">Add / Update Rule</button></div>
                <?php if(!$ready) echo '<p class="description">Install and activate WooCommerce to manage real product categories.</p>'; ?>
              </section>
              <section class="psp-panel" id="psp-designs"><div class="psp-panel-heading"><h3>05 · My Product Designs</h3><span>Save up to 20 reusable draft configurations</span></div>
                <p class="description">Saving a design here never changes your live shop. Load a saved design into the editor and click Save Product Layout only when ready.</p>
                <div class="psp-presets"><input id="psp-design-name" type="text" maxlength="60" placeholder="e.g. Summer Collection" aria-label="New design name"><button type="button" id="psp-save-design" class="button button-secondary">Save Draft Design</button></div>
                <div class="psp-saved-list" id="psp-saved-list"></div><p class="psp-design-status" id="psp-design-status" role="status" aria-live="polite"></p>
              </section>
              <section class="psp-panel psp-publish psp-publish-v220" id="psp-publish"><div><h3>06 · Publish & Theme Override</h3><p>Replace the theme's default product cards for supported WooCommerce loops. No theme files are edited. Switch off to restore the theme cards.</p>
                 <label class="psp-enable"><input type="checkbox" name="ps_products[enabled]" value="1" <?php checked($s['enabled'],1); ?> <?php disabled(!$ready); ?>> Replace theme product cards with my layouts</label>
                 <div class="psp-single-override-option">
                   <label class="psp-enable"><input type="checkbox" name="ps_products[single_enabled]" value="1" <?php checked($s['single_enabled'],1); ?> <?php disabled(!$ready); ?>> Apply selected design to <strong>Single Product Pages</strong></label>
                   <p class="description"><strong>Important:</strong> Card designs and Single Product designs are controlled independently. To change the page after tapping a product, this Single Product checkbox MUST be ON and saved. When it is OFF, WoodMart keeps rendering its own product detail page. Enable it, click Save &amp; Apply Layout, and open the test product below. The WooCommerce Add to Cart/variation functionality remains handled by WooCommerce.</p>
                   <p class="description"><strong>Current Single Product status: <?php echo !empty($s['single_enabled']) ? 'ENABLED (' . esc_html($s['single_mode']==='force' ? 'FORCE takeover' : 'template filter') . ')' : 'DISABLED — enable to replace WoodMart details'; ?></strong></p>
                   <label class="psp-scope-control"><strong>Single Product Override Engine</strong><select name="ps_products[single_mode]">
                     <option value="force" <?php selected($s['single_mode'],'force'); ?>>Force takeover (recommended for WoodMart / Elementor)</option>
                     <option value="compatible" <?php selected($s['single_mode'],'compatible'); ?>>Compatible template filter (legacy mode)</option>
                   </select></label>
                   <p class="description"><strong>Force takeover:</strong> when the Single Product checkbox is ON, render the plugin detail page before WoodMart's competing layout builder. The shop header/footer and WooCommerce purchase hooks still run. Some theme-specific delivery, wishlist or sticky-cart widgets may not appear; test variations, checkout and extensions on staging. You can restore WoodMart by switching OFF Single Product and saving.</p>
                   <p class="description"><strong>WoodMart / Elementor:</strong> this plugin-owned product page intentionally replaces custom Single Product layouts while enabled. Review custom delivery, sticky cart, wishlist or addon elements on a staging site before switching on. No theme files are changed.</p>
                   <?php if($ready) { $example=wc_get_products(array('limit'=>1,'status'=>'publish')); if(!empty($example) && is_object($example[0])) echo '<a class="button button-secondary" target="_blank" rel="noopener noreferrer" href="'.esc_url(add_query_arg('psp_verify','1',get_permalink($example[0]->get_id()))).'">Open a Single Product to test ↗</a>'; } ?>
                 </div>
                 <label class="psp-scope-control"><strong>Apply layouts to</strong><select name="ps_products[scope]">
                 <option value="all" <?php selected($s['scope'],'all'); ?>>All classic WooCommerce product grids — Shop, Home, Elementor/WoodMart widgets</option>
                 <option value="archives" <?php selected($s['scope'],'archives'); ?>>Only Shop / Category / Tag archives</option>
                 </select></label>
                 <div class="psp-theme-help"><strong>Theme compatibility &amp; recovery</strong>
                   <?php $theme=function_exists('wp_get_theme')?wp_get_theme():null; $theme_name=$theme? $theme->get('Name') : 'your theme'; ?>
                   <p>Current theme: <b><?php echo esc_html($theme_name); ?></b>. Classic WooCommerce product templates and WoodMart-style product-hover templates can be replaced automatically. Some Elementor custom markup and WooCommerce Product Collection blocks bypass PHP templates.</p>
                   <p><strong>WoodMart tip:</strong> Theme Settings → Product archive → Products loop lets you choose a standard or custom product layout. Avoid selecting a competing custom loop item; if your widget bypasses WooCommerce's PHP loop, use the shortcode below in an Elementor Shortcode element instead.</p>
                   <label for="psp-shortcode">Guaranteed plugin-owned grid (place in a shortcode-enabled page or Elementor Shortcode widget)</label>
                   <div class="psp-shortcode-copy"><input id="psp-shortcode" type="text" readonly value="[preloader_studio_products limit=&quot;8&quot;]" onclick="this.select()"><button type="button" class="button" id="psp-copy-shortcode">Copy</button></div>
                   <p><strong>Restore:</strong> Uncheck the override and Save. The shortcode itself is independent and should be removed from pages where you no longer want plugin cards.</p>
                   <?php if($ready && function_exists('wc_get_page_permalink')) echo '<a class="button button-secondary" href="'.esc_url(wc_get_page_permalink('shop')).'" target="_blank" rel="noopener noreferrer">Open live Shop ↗</a>'; ?>
                 </div></div><button type="submit" class="button button-primary button-large">Save &amp; Apply Layout</button></section>
            </main><aside class="psp-preview-aside"><section class="psp-panel psp-preview-panel"><div class="psp-panel-heading"><h3>Real Product Preview</h3><span id="psp-preview-label">Mobile · 390px</span></div>
              <label class="psp-product-pick">Preview product <select name="ps_products[preview_product]" id="psp-product-picker"><option value="0">Latest products / sample catalog</option><?php if($ready){$choices=wc_get_products(array('limit'=>40,'status'=>'publish','orderby'=>'date','order'=>'DESC'));foreach($choices as $product){if($product->is_visible()) echo '<option value="'.absint($product->get_id()).'">'.esc_html($product->get_name()).'</option>';}} ?></select></label><div class="psp-device-choices" role="group" aria-label="Device preview"><button type="button" data-device="mobile" class="is-active" aria-pressed="true">Mobile</button><button type="button" data-device="tablet" aria-pressed="false">Tablet</button><button type="button" data-device="desktop" aria-pressed="false">Desktop</button></div>
              <div class="psp-preview-stage" id="psp-preview-stage"><div id="psp-preview-shell" class="psp-preview-shell"><iframe id="psp-preview-iframe" title="Product layout preview using real WooCommerce product cards" loading="lazy"></iframe></div></div>
              <button type="button" class="button" id="psp-expand-preview">Expand Preview</button><p class="description">Preview uses the same product card template and CSS as the published layout. Published WooCommerce products and prices are shown when available; otherwise the included product photography is clearly labeled as a demo. Theme-specific outer-page styling can differ.</p>
              <p class="psp-preview-error" id="psp-preview-error" role="status" aria-live="polite"></p></section></aside></div>
          </form>
        </div>
        <?php
    }
    public static function preview() {
        if(!current_user_can('manage_options')) wp_die('Permission denied',403);
        check_ajax_referer('ps_studio_product_preview','nonce');
        $s=self::settings();
        $raw=array();
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- check_ajax_referer verified before reading; settings sanitized by self::sanitize.
        foreach(array_keys(self::defaults()) as $k) if(isset($_GET[$k]) && is_scalar($_GET[$k])) $raw[$k]=wp_unslash($_GET[$k]);
        $s=self::sanitize(array_merge($s,$raw));
        $device=isset($_GET['device']) && is_string($_GET['device']) ? sanitize_key(wp_unslash($_GET['device'])) : 'mobile';
        if ( ! in_array( $device, array('mobile','tablet','desktop'),true ) ) { $device = 'mobile'; }
        $vw=array('mobile'=>390,'tablet'=>820,'desktop'=>1440)[$device];
        $selected=isset($_GET['preview_product']) ? absint(wp_unslash($_GET['preview_product'])) : 0;
        nocache_headers();
        header('Content-Type: text/html; charset='.get_option('blog_charset','UTF-8'));
        header('X-Content-Type-Options: nosniff');
        header('Content-Security-Policy: default-src \'none\'; style-src \'self\' \'unsafe-inline\'; img-src \'self\' data: https: http:; font-src \'self\';');
        wp_enqueue_style( 'ps-studio-preview-products', PRELSTAN_STUDIO_URL . 'assets/css/product-cards.css', array(), PRELSTAN_STUDIO_VERSION );
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Product Layout Preview</title>';
        wp_print_styles( array( 'ps-studio-preview-products' ) );
        echo '</head><body class="woocommerce woocommerce-page ps-products-enabled psp-preview-mode ps-products--'.esc_attr($s['layout']).'" style="'.esc_attr(self::css_vars($s)).'">';
        echo '<div class="psp-preview-page"><header><small>YOUR SHOP / LIVE CARD PREVIEW</small><h1>Discover the collection.</h1><p>Product cards rendered with your selected layout.</p></header><ul class="products ps-product-grid">';
        $products=self::wc_ready()?wc_get_products(array('limit'=>6,'status'=>'publish','orderby'=>'date','order'=>'DESC')):array();
        if($selected && self::wc_ready()) {
            $single=wc_get_product($selected);
            if($single && $single->is_visible() && $single->get_status()==='publish') {
                $products=array_merge(array($single),array_filter($products,function($p)use($selected){return $p->get_id()!==$selected;}));
                $products=array_slice($products,0,6);
            }
        }
        $shown=0;
        foreach($products as $item) {
            if(is_object($item) && method_exists($item,'is_visible') && $item->is_visible()) { self::card($item,$s,true); $shown++; }
        }
        if(!$shown){ foreach(self::demo_catalog() as $i=>$item) self::demo_card($item['name'],$i,$s); }
        echo '</ul><footer>'.(!$shown?'Photographic demo products — add WooCommerce products to preview your real catalog.':'Showing published products from this WooCommerce store.').'</footer></div></body></html>';
        exit;
    }
    public static function card($product,$s=null,$preview=false,$force_layout=false,$nested=false) {
        if(!$s) $s=self::settings();
        if(!is_object($product) || !method_exists($product,'is_visible') || !$product->is_visible()) return;
        $layout=$force_layout ? $s['layout'] : self::layout_for_product($product,$s); $link=get_permalink($product->get_id());
        $cat_names=wc_get_product_category_list($product->get_id(),', ','','');
        if($nested) {
            echo '<div class="ps-product-card psp-product-layout-'.esc_attr($layout).' psp-hover-'.esc_attr($s['hover']).'" data-product-id="'.absint($product->get_id()).'"><div class="psp-card-inner">';
        } else {
            ob_start();
            wc_product_class( 'ps-product-card psp-product-layout-' . sanitize_html_class( $layout ) . ' psp-hover-' . sanitize_html_class( $s['hover'] ), $product );
            $psp_classes = ob_get_clean();
            $psp_class_value = preg_match( '/class="([^"]*)"/', $psp_classes, $psp_matches ) ? $psp_matches[1] : 'product';
            echo '<li class="' . esc_attr( $psp_class_value ) . '"><div class="psp-card-inner">';
        }
        echo '<div class="psp-media"><a class="psp-photo" href="'.esc_url($link).'" aria-label="'.esc_attr($product->get_name()).'">'.wp_kses_post( $product->get_image('woocommerce_thumbnail') ).'</a>';
        if($s['show_badge'] && $product->is_on_sale()) echo '<span class="psp-badge">SALE</span>';
        echo '<span class="psp-image-index" aria-hidden="true">0'.(absint($product->get_id())%9+1).'</span></div><div class="psp-info">';
        if($s['show_category'] && $cat_names) echo '<div class="psp-category">'.wp_kses_post($cat_names).'</div>';
        echo '<h3 class="psp-name"><a href="'.esc_url($link).'">'.esc_html($product->get_name()).'</a></h3>';
        if($s['show_rating'] && $product->get_rating_count()) echo '<div class="psp-rating">'.wp_kses_post(wc_get_rating_html($product->get_average_rating(),$product->get_rating_count())).'</div>';
        echo '<div class="psp-price">'.wp_kses_post($product->get_price_html()).'</div>';
        if($s['show_button']) { echo '<div class="psp-actions">';
            if($preview) {
                // In preview the button must never submit an add-to-cart request.
                echo '<span class="button">View product →</span>';
            } else {
                // Retain WooCommerce's cart/variable-product behavior on the LIVE storefront.
                $original=isset($GLOBALS['product'])?$GLOBALS['product']:null;
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This is WooCommerce's core product global, not a new plugin global.
                $GLOBALS['product']=$product;
                woocommerce_template_loop_add_to_cart();
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Restore WooCommerce's core product global after rendering.
                $GLOBALS['product']=$original;
            }
            echo '</div>'; }
        echo $nested ? '</div></div></div>' : '</div></div></li>';
    }
    public static function demo_card($name,$index,$s) {
        $catalog=self::demo_catalog();
        $item=$catalog[$index % count($catalog)];
        $image=PRELSTAN_STUDIO_URL.'assets/img/product-demo/'.$item['image'];
        echo '<li class="product ps-product-card psp-product-layout-'.esc_attr($s['layout']).' psp-hover-'.esc_attr($s['hover']).'"><div class="psp-card-inner"><div class="psp-media">';
        echo '<span class="psp-photo"><img loading="lazy" decoding="async" src="'.esc_url($image).'" alt="'.esc_attr($item['name']).'" width="440" height="440"></span>';
        if($s['show_badge'] && $item['sale']) echo '<span class="psp-badge">SALE</span>';
        echo '<span class="psp-image-index" aria-hidden="true">0' . esc_html( (string) ( ( absint( $index ) % 6 ) + 1 ) ) . '</span></div><div class="psp-info">';
        if($s['show_category']) echo '<div class="psp-category">'.esc_html($item['category']).'</div>';
        echo '<h3 class="psp-name">'.esc_html($name).'</h3>';
        if($s['show_rating']) echo '<div class="psp-rating psp-demo-rating" aria-label="Rated 5 out of 5 stars">★★★★★ <small>(24)</small></div>';
        echo '<div class="psp-price">';
        if($item['old']) echo '<del>'.esc_html($item['old']).'</del> ';
        echo '<ins>'.esc_html($item['price']).'</ins></div>';
        if($s['show_button']) echo '<div class="psp-actions"><span class="button">View product →</span></div>';
        echo '</div></div></li>';
    }

}
