<?php
/** Plugin-owned, reversible WooCommerce account dashboard. */
if (!defined('ABSPATH')) { exit; }

final class PRELSTAN_Studio_Account_Dashboard {
    public static function enabled() {
        $s=PRELSTAN_Studio_Auth::settings();
        return !empty($s['account_dashboard']) && PRELSTAN_Studio_Auth_Suite::enabled('woocommerce')
            && function_exists('is_account_page') && is_account_page() && is_user_logged_in()
            && !is_admin() && !wp_doing_ajax() && function_exists('WC');
    }
    public static function init() {
        // WoodMart/Elementor can replace template_include. Resolve the logged-in
        // account page first, like our working Single Product force takeover.
        add_action('template_redirect',array(__CLASS__,'force_template'),999999);
        add_filter('template_include',array(__CLASS__,'template'),PHP_INT_MAX);
        add_action('wp_enqueue_scripts',array(__CLASS__,'enqueue'),1001);
    }
    /** Never intercept admin, REST, AJAX, feeds, embeds or visual-builder previews. */
    public static function safe_request() {
        if (is_admin() || wp_doing_ajax()) return false;
        if (defined('REST_REQUEST') && REST_REQUEST) return false;
        if ((function_exists('is_feed') && is_feed()) || (function_exists('is_embed') && is_embed())) return false;
        if (function_exists('is_preview') && is_preview()) return false;
        foreach (array('elementor-preview', 'elementor_library', 'vc_editable', 'wpb-preview', 'customize_changeset_uuid') as $key) {
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only editor-preview detection; no state-changing operation.
            if (isset($_GET[$key])) return false;
        }
        return true;
    }
    public static function force_allowed() {
        if (!self::safe_request() || !self::enabled()) return false;
        return (bool) apply_filters('ps_studio_account_force_allowed', true);
    }
    /** Called after redirects have been prepared, before the theme's content builder. */
    public static function force_template() {
        if (!self::force_allowed()) return;
        $file = PRELSTAN_STUDIO_DIR . 'templates/auth-dashboard.php';
        if (!is_readable($file)) return;
        // The template calls the active theme's header/footer but not its
        // WooCommerce account shortcode markup, preventing duplicate menus.
        status_header(200);
        include $file;
        exit;
    }
    public static function template($template) {
        if(!self::enabled() || !self::safe_request()) return $template;
        $own=PRELSTAN_STUDIO_DIR.'templates/auth-dashboard.php';
        return is_readable($own) ? $own : $template;
    }
    public static function enqueue() {
        if(!self::enabled() || !self::safe_request())return;
        wp_enqueue_style('psx-account-dashboard',PRELSTAN_STUDIO_URL.'assets/css/auth-dashboard.css',array(),PRELSTAN_STUDIO_VERSION);
    }
    private static function endpoint() {
        if(!function_exists('WC'))return '';
        $wc=WC();
        if(isset($wc->query) && is_object($wc->query) && method_exists($wc->query,'get_current_endpoint')) {
            return (string)$wc->query->get_current_endpoint();
        }
        return '';
    }
    private static function icon($endpoint) {
        $icons=array('dashboard'=>'◈','orders'=>'▤','downloads'=>'↓','edit-address'=>'⌂','edit-account'=>'◇','customer-logout'=>'↗','wishlist'=>'♡');
        return isset($icons[$endpoint])?$icons[$endpoint]:'◻';
    }
    public static function render() {
        if(!self::enabled())return;
        $s=PRELSTAN_Studio_Auth::settings(); $preset=PRELSTAN_Studio_Auth_Suite::chosen($s);
        $palette=PRELSTAN_Studio_Auth_Suite::templates()[$preset];
        $current=self::endpoint();
        $user=wp_get_current_user();
        $name=$user && !empty($user->display_name) ? $user->display_name : __('Friend','preloader-studio-animated-loading-screens');
        $first= trim((string)$name) !== '' ? (function_exists('mb_substr') ? mb_strtoupper(mb_substr((string)$name,0,1)) : strtoupper(substr((string)$name,0,1))) : 'A';
        $menus=function_exists('wc_get_account_menu_items')?wc_get_account_menu_items():array('dashboard'=>'Dashboard','orders'=>'Orders','downloads'=>'Downloads','edit-address'=>'Addresses','edit-account'=>'Account details','customer-logout'=>'Logout');
        $url=function_exists('wc_get_account_endpoint_url')?'wc_get_account_endpoint_url':null;
        $inline='--psx-accent:'.$s['accent'].';--psx-canvas:'.$s['background'].';--psx-panel:'.$palette[4].';--psx-ink:'.$palette[5].';';
        ?>
        <main id="psx-member" class="psx-member psx-<?php echo esc_attr($preset); ?>" style="<?php echo esc_attr($inline); ?>">
          <div class="psx-member-shell">
            <header class="psx-member-hero">
              <div><span class="psx-member-eyebrow"><?php esc_html_e('YOUR PERSONAL SPACE','preloader-studio-animated-loading-screens'); ?></span>
<?php /* translators: %s: account display name. */ ?>
                <h1><?php echo esc_html(sprintf(__('Welcome back, %s','preloader-studio-animated-loading-screens'),$name)); ?></h1>
                <p><?php esc_html_e('Everything about your orders, downloads and account, beautifully organized.','preloader-studio-animated-loading-screens'); ?></p>
              </div>
              <div class="psx-member-avatar" aria-hidden="true"><?php echo esc_html($first); ?></div>
            </header>
            <div class="psx-member-columns">
              <nav class="psx-member-menu" aria-label="<?php esc_attr_e('Account navigation','preloader-studio-animated-loading-screens'); ?>">
                <span class="psx-member-menu-label"><?php esc_html_e('MY ACCOUNT','preloader-studio-animated-loading-screens'); ?></span>
                <?php foreach($menus as $endpoint=>$label):
                    $active=($current==='' && $endpoint==='dashboard') || ($current!=='' && $current===$endpoint);
                    $href=$url?call_user_func($url,$endpoint):'#';
                    ?>
                    <a class="psx-member-link<?php echo $active?' is-active':''; ?>" href="<?php echo esc_url($href); ?>" <?php echo $active?'aria-current="page"':''; ?>>
                        <span class="psx-member-icon" aria-hidden="true"><?php echo esc_html(self::icon($endpoint)); ?></span><span><?php echo esc_html($label); ?></span><span class="psx-member-arrow" aria-hidden="true">›</span>
                    </a>
                <?php endforeach; ?>
              </nav>
              <section class="psx-member-main" aria-label="<?php esc_attr_e('Account content','preloader-studio-animated-loading-screens'); ?>">
                <?php if(function_exists('wc_print_notices'))wc_print_notices(); ?>
                <?php if($current==='' || $current==='dashboard'): ?>
                  <div class="psx-member-section-heading"><span><?php esc_html_e('YOUR DASHBOARD','preloader-studio-animated-loading-screens'); ?></span><h2><?php esc_html_e('Your account at a glance','preloader-studio-animated-loading-screens'); ?></h2></div>
                  <div class="psx-member-quick">
                  <?php foreach(array('orders'=>array(__('My Orders','preloader-studio-animated-loading-screens'),__('Track purchases','preloader-studio-animated-loading-screens')),'downloads'=>array(__('Downloads','preloader-studio-animated-loading-screens'),__('Your digital purchases','preloader-studio-animated-loading-screens')),'edit-address'=>array(__('Addresses','preloader-studio-animated-loading-screens'),__('Billing & delivery','preloader-studio-animated-loading-screens')),'edit-account'=>array(__('Account Details','preloader-studio-animated-loading-screens'),__('Your information','preloader-studio-animated-loading-screens'))) as $k=>$tile): ?>
                    <a href="<?php echo esc_url($url?call_user_func($url,$k):'#'); ?>" class="psx-member-tile"><span class="psx-member-tile-icon" aria-hidden="true"><?php echo esc_html(self::icon($k)); ?></span><strong><?php echo esc_html($tile[0]); ?></strong><small><?php echo esc_html($tile[1]); ?></small><span class="psx-member-tile-arrow" aria-hidden="true">↗</span></a>
                  <?php endforeach; ?>
                  </div>
                  <?php $fields=$s['register_fields']; if($fields): ?>
                    <section class="psx-member-extra"><h3><?php esc_html_e('Your registration details','preloader-studio-animated-loading-screens'); ?></h3><div class="psx-member-extra-grid">
                    <?php foreach($fields as $f): $value=get_user_meta(get_current_user_id(),'psx_field_'.$f['key'],true); if($value===''||!is_scalar($value))continue; ?>
                      <div><small><?php echo esc_html($f['label']); ?></small><strong><?php echo esc_html($value); ?></strong></div>
                    <?php endforeach; ?></div></section>
                  <?php endif; ?>
                <?php else: ?>
                  <div class="psx-member-section-heading"><span><?php esc_html_e('MY ACCOUNT','preloader-studio-animated-loading-screens'); ?></span><h2><?php echo esc_html(isset($menus[$current])?$menus[$current]:ucwords(str_replace('-',' ',$current))); ?></h2></div>
                  <div class="psx-member-endpoint">
                  <?php
                  // Preserve WooCommerce endpoint callbacks (orders, downloads, addresses,
                  // details, extensions) without rendering its original navigation markup.
                  if(has_action('woocommerce_account_content')) {
                      do_action('woocommerce_account_content');
                  } elseif(function_exists('woocommerce_account_content')) {
                      woocommerce_account_content();
                  }
                  ?>
                  </div>
                <?php endif; ?>
              </section>
            </div>
          </div>
        </main>
        <?php
    }
}
