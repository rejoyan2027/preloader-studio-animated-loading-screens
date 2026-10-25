<?php
/** Theme-independent visual treatment for core WordPress and WooCommerce authentication forms. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class PRELSTAN_Studio_Auth {
    const OPTION = 'ps_studio_auth';

    /** Twenty-four presets: distinct arrangements, color systems, and ornamental treatments. */
    public static function presets() {
        return array(
            'aurora-glass'     => array('Aurora Glass','Glass','#7865f6','#f1efff','#ffffff','#262141','glass'),
            'editorial-split'  => array('Editorial Split','Editorial','#9a6246','#f8eee5','#ffffff','#38261e','split'),
            'midnight-neon'   => array('Midnight Neon','Dark','#b585ff','#131426','#202038','#f8f4ff','dark'),
            'cloud-card'      => array('Cloud Card','Minimal','#4276dc','#edf4ff','#ffffff','#1f3658','float'),
            'terracotta'      => array('Terracotta Studio','Editorial','#c46a4a','#fcf0e8','#fffaf5','#513328','split'),
            'emerald-vault'   => array('Emerald Vault','Dark','#79dfb7','#112920','#18362c','#f4fff9','dark'),
            'monochrome'      => array('Mono Architect','Minimal','#242424','#f6f6f4','#ffffff','#191919','frame'),
            'sunset-wave'     => array('Sunset Wave','Gradient','#f3668f','#fff1e9','#fffaf8','#542a3c','wave'),
            'neon-circuit'    => array('Neon Circuit','Dark','#4ef6e1','#101c29','#162a38','#f2ffff','dark'),
            'velvet-royal'    => array('Velvet Royal','Luxury','#dfc28b','#22172d','#34223f','#fff9ed','luxury'),
            'paper-fold'      => array('Paper Fold','Editorial','#8d7152','#f2eade','#fffcf5','#413526','fold'),
            'cosmic-orbit'    => array('Cosmic Orbit','Gradient','#9098ff','#161c43','#222955','#f9faff','orbit'),
            'rose-atelier'    => array('Rose Atelier','Luxury','#c9678f','#fff0f5','#fffafd','#5c2c42','luxury'),
            'bento-tiles'     => array('Bento Tiles','Creative','#6c5bd6','#eae8fa','#ffffff','#272140','bento'),
            'brutalist-ink'   => array('Brutalist Ink','Creative','#f47a44','#fff3d5','#fffdf2','#201d19','brutal'),
            'ocean-tide'      => array('Ocean Tide','Gradient','#147faf','#e3f6ff','#ffffff','#194157','wave'),
            'forest-calm'     => array('Forest Calm','Minimal','#54896c','#edf5eb','#fcfff9','#2c4635','organic'),
            'prism-depth'     => array('Prism Depth','Glass','#6053ef','#e9e7ff','#ffffff','#211b52','glass'),
            'sakura-blossom'  => array('Sakura Blossom','Luxury','#cb7599','#fff0f4','#ffffff','#61364a','organic'),
            'retro-terminal'  => array('Retro Terminal','Creative','#9dea69','#1a241c','#27342b','#edffe5','terminal'),
            'golden-gallery'  => array('Golden Gallery','Luxury','#b78b37','#f8f3e6','#fffdfa','#43351a','frame'),
            'arctic-frost'    => array('Arctic Frost','Glass','#2c9abd','#eaf8ff','#ffffff','#17384d','float'),
            'lilac-ribbon'    => array('Lilac Ribbon','Creative','#a16cea','#f4edff','#ffffff','#472867','fold'),
            'citrus-energy'   => array('Citrus Energy','Gradient','#e27c24','#fff8df','#fffefa','#4a301d','bento'),
        );
    }

    public static function defaults() {
        return array('enabled'=>0,'independent_mode'=>0,'wp_login'=>1,'woocommerce'=>1,'native_wc_form'=>1,'preset'=>'aurora-glass',
            'accent'=>'#7865f6','background'=>'#f1efff','logo_id'=>0,
            'headline'=>'Welcome back','subtitle'=>'Sign in to continue your journey.',
            'account_dashboard'=>0,'login_title'=>'Sign in to your account','login_intro'=>'Pick up where you left off.',
            'register_title'=>'Create your account','register_intro'=>'Your new account is just a moment away.',
            'login_button'=>'Sign in securely','register_button'=>'Create account',
            'login_user_label'=>'Email address or username','login_password_label'=>'Password','register_fields'=>array());
    }
    public static function settings() {
        $raw = get_option(self::OPTION, array());
        return wp_parse_args(is_array($raw) ? $raw : array(), self::defaults());
    }
    public static function sanitize($raw) {
        $raw = is_array($raw) ? $raw : array();
        $styles = self::presets(); $d = self::defaults();
        $preset = isset($raw['preset']) && is_string($raw['preset']) && isset($styles[$raw['preset']]) ? $raw['preset'] : $d['preset'];
        $palette = $styles[$preset];
        $accent = isset($raw['accent']) && is_string($raw['accent']) ? sanitize_hex_color($raw['accent']) : false;
        $bg = isset($raw['background']) && is_string($raw['background']) ? sanitize_hex_color($raw['background']) : false;
        return array(
            'enabled' => empty($raw['enabled']) ? 0 : 1,
            'independent_mode' => empty($raw['independent_mode']) ? 0 : 1,
            'wp_login' => empty($raw['wp_login']) ? 0 : 1,
            'woocommerce' => empty($raw['woocommerce']) ? 0 : 1,
            'native_wc_form' => empty($raw['native_wc_form']) ? 0 : 1,
            'preset' => $preset,
            'accent' => $accent ?: $palette[2],
            'background' => $bg ?: $palette[3],
            'logo_id' => isset($raw['logo_id']) ? absint($raw['logo_id']) : 0,
            'headline' => self::trim_text(isset($raw['headline']) ? $raw['headline'] : $d['headline'], 75),
            'subtitle' => self::trim_text(isset($raw['subtitle']) ? $raw['subtitle'] : $d['subtitle'], 160),
            'account_dashboard' => empty($raw['account_dashboard']) ? 0 : 1,
            'login_title' => self::trim_text($raw['login_title'] ?? $d['login_title'], 90),
            'login_intro' => self::trim_text($raw['login_intro'] ?? $d['login_intro'], 160),
            'register_title' => self::trim_text($raw['register_title'] ?? $d['register_title'], 90),
            'register_intro' => self::trim_text($raw['register_intro'] ?? $d['register_intro'], 160),
            'login_button' => self::trim_text($raw['login_button'] ?? $d['login_button'], 45),
            'register_button' => self::trim_text($raw['register_button'] ?? $d['register_button'], 45),
            'login_user_label' => self::trim_text($raw['login_user_label'] ?? $d['login_user_label'], 65),
            'login_password_label' => self::trim_text($raw['login_password_label'] ?? $d['login_password_label'], 65),
            'register_fields' => self::sanitize_registration_fields($raw['register_fields_json'] ?? ($raw['register_fields'] ?? array())),
        );
    }

    /** Admin-designed metadata fields. No password or privilege fields are supported. */
    public static function sanitize_registration_fields($input) {
        if (is_string($input)) { $input=json_decode($input,true); }
        if (!is_array($input)) {return array();}
        $out=array(); $used=array();
        $types=array('text','email','tel','select','textarea','checkbox');
        $reserved=array('id','role','password','user_pass','user_login','username','email','user_email','action','nonce','wp_capabilities','billing_email');
        foreach($input as $entry) {
            if(count($out)>=12)break;
            if(!is_array($entry))continue;
            $key=isset($entry['key']) && is_scalar($entry['key']) ? sanitize_key((string)$entry['key']) : '';
            $key=substr($key,0,35);
            $label=isset($entry['label']) && is_scalar($entry['label']) ? self::trim_text($entry['label'],70) : '';
            $type=isset($entry['type']) && is_scalar($entry['type']) ? (string)$entry['type'] : 'text';
            if(!$key || !$label || !preg_match('/^[a-z][a-z0-9_]*$/',$key) || in_array($key,$reserved,true) || isset($used[$key]) || !in_array($type,$types,true))continue;
            $options=array();
            if($type==='select') {
                $raw_options=$entry['options'] ?? array();
                if(is_string($raw_options))$raw_options=preg_split('/[\r\n,]+/',$raw_options);
                if(is_array($raw_options))foreach(array_slice($raw_options,0,16) as $opt){if(!is_scalar($opt))continue;$opt=self::trim_text($opt,65);if($opt!==''&&!in_array($opt,$options,true))$options[]=$opt;}
                if(!$options)continue;
            }
            $out[]=array('key'=>$key,'label'=>$label,'type'=>$type,
                'required'=>!empty($entry['required'])?1:0,
                'placeholder'=>self::trim_text($entry['placeholder']??'',90),
                'options'=>$options);
            $used[$key]=1;
        }
        return $out;
    }

    private static function trim_text($text,$limit) {
        $text = is_scalar($text) ? sanitize_text_field((string)$text) : '';
        return function_exists('mb_substr') ? mb_substr($text,0,$limit) : substr($text,0,$limit);
    }
    public static function enabled_for($area) {
        $s = self::settings();
        return !empty($s['enabled']) && !empty($s[$area]);
    }
    public static function init() {
        add_action('admin_post_ps_studio_auth_save',array(__CLASS__,'save'));
        add_action('admin_post_ps_studio_auth_restore',array(__CLASS__,'restore'));
        PRELSTAN_Studio_Auth_Suite::init();
    }
    public static function save() {
        if (!current_user_can('manage_options')) { wp_die(esc_html__('Permission denied.','preloader-studio-animated-loading-screens'),403); }
        check_admin_referer('ps_studio_auth_save','ps_auth_nonce');
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- check_admin_referer verified; PRELSTAN_Studio_Auth::sanitize validates each submitted field.
        $raw = isset($_POST['ps_auth']) && is_array($_POST['ps_auth']) ? wp_unslash($_POST['ps_auth']) : array();
        update_option(self::OPTION,self::sanitize($raw),false);
        wp_safe_redirect(add_query_arg(array('page'=>'preloader-studio-auth','updated'=>'auth'),admin_url('admin.php')));
        exit;
    }
    public static function restore() {
        if (!current_user_can('manage_options')) { wp_die(esc_html__('Permission denied.','preloader-studio-animated-loading-screens'),403); }
        check_admin_referer('ps_studio_auth_restore','ps_auth_restore_nonce');
        $s = self::settings(); $s['enabled'] = 0; $s['independent_mode'] = 0;
        update_option(self::OPTION,$s,false);
        wp_safe_redirect(add_query_arg(array('page'=>'preloader-studio-auth','updated'=>'auth_restored'),admin_url('admin.php')));
        exit;
    }
    public static function login_class($classes,$action) {
        if (self::enabled_for('wp_login')) {
            $s = self::settings(); $classes[]='psa-active'; $classes[]='psa-style-'.$s['preset'];
        }
        return $classes;
    }
    public static function login_logo_url($url) {
        return self::enabled_for('wp_login') ? home_url('/') : $url;
    }
    /** WooCommerce's original GPL template preserves registration settings and all auth hooks.
     * Replace only the login template (not accounts, checkout, or password processing).
     * A theme-independent form can be switched off without editing any theme file.
     */
    public static function native_woocommerce_form($template,$template_name,$template_path='') {
        if ('myaccount/form-login.php' !== $template_name
            || !self::enabled_for('woocommerce') || !self::is_account_guest()
            || empty(self::settings()['native_wc_form']) || !function_exists('WC')) {
            return $template;
        }
        $wc=WC();
        if (!is_object($wc) || !method_exists($wc,'plugin_path')) { return $template; }
        $core=trailingslashit($wc->plugin_path()).'templates/myaccount/form-login.php';
        return is_readable($core) ? $core : $template;
    }
    private static function css_variables($s) {
        $styles=self::presets(); $p=$styles[$s['preset']];
        return '--psa-accent:'.$s['accent'].';--psa-background:'.$s['background'].';--psa-panel:'.$p[4].';--psa-ink:'.$p[5].';';
    }
    private static function enqueue_shared() {
        wp_enqueue_style('ps-studio-auth',PRELSTAN_STUDIO_URL.'assets/css/auth.css',array(),PRELSTAN_STUDIO_VERSION);
        wp_enqueue_style('ps-studio-auth-compat',PRELSTAN_STUDIO_URL.'assets/css/auth-compat.css',array('ps-studio-auth'),PRELSTAN_STUDIO_VERSION);
        wp_enqueue_script('ps-studio-auth-frontend',PRELSTAN_STUDIO_URL.'assets/js/auth-frontend.js',array(),PRELSTAN_STUDIO_VERSION,true);
        $s=self::settings();
        wp_add_inline_style('ps-studio-auth','body.psa-active,body.psa-wc-active, .psa-auth-shortcode{'.self::css_variables($s).'}');
        $logo=self::logo_url($s);
        if ($logo) {
            wp_add_inline_style('ps-studio-auth','body.login.psa-active #login h1 a{background-image:url('.wp_json_encode(esc_url_raw($logo)).');background-size:contain;width:120px;height:70px;}');
        }
    }
    public static function enqueue_wp_login() {
        if (self::enabled_for('wp_login')) { self::enqueue_shared(); }
    }
    private static function is_account_guest() {
        return function_exists('is_account_page') && is_account_page() && !is_user_logged_in();
    }
    public static function enqueue_woocommerce() {
        if (self::enabled_for('woocommerce') && self::is_account_guest()) { self::enqueue_shared(); return; }
        global $post;
        if ( !is_user_logged_in() && ( self::enabled_for('woocommerce') || self::enabled_for('wp_login') )
            && isset($post->post_content) && is_string($post->post_content)
            && has_shortcode($post->post_content, 'preloader_studio_auth') ) { self::enqueue_shared(); }
    }
    public static function woocommerce_body_class($classes) {
        if (self::enabled_for('woocommerce') && self::is_account_guest()) {
            $s = self::settings(); $classes[]='psa-wc-active'; $classes[]='psa-style-'.$s['preset'];
        }
        return $classes;
    }
    public static function woocommerce_intro() {
        if (!self::enabled_for('woocommerce') || !self::is_account_guest()) { return; }
        $s=self::settings();
        echo '<section class="psa-woo-hero" aria-label="'.esc_attr__('Account access','preloader-studio-animated-loading-screens').'">';
        if (self::logo_url($s)) { echo '<img class="psa-woo-logo" alt="" src="'.esc_url(self::logo_url($s)).'" loading="lazy" />'; }
        echo '<span class="psa-hero-kicker">'.esc_html__('YOUR ACCOUNT','preloader-studio-animated-loading-screens').'</span><h2>'.esc_html($s['headline']).'</h2><p>'.esc_html($s['subtitle']).'</p></section>';
        echo '<nav class="psa-account-jump" aria-label="'.esc_attr__('Account forms','preloader-studio-animated-loading-screens').'">';
        echo '<a href="#psa-account-login">'.esc_html__('Log in','preloader-studio-animated-loading-screens').'</a>';
        if ('yes' === get_option('woocommerce_enable_myaccount_registration','no')) {
            echo '<a href="#psa-account-register">'.esc_html__('Create account','preloader-studio-animated-loading-screens').'</a>';
        }
        echo '</nav>';
    }
    public static function shortcode($atts=array()) {
        // Rendering always delegates login credentials, nonces and registration rules to WP/WooCommerce.
        if (is_user_logged_in()) { return '<p>'.esc_html__('You are already signed in.','preloader-studio-animated-loading-screens').'</p>'; }
        if (class_exists('WooCommerce')) {
            $html = do_shortcode('[woocommerce_my_account]');
            return '<div class="psa-auth-shortcode">'.$html.'</div>';
        }
        $html=wp_login_form(array('echo'=>false,'remember'=>true));
        $html.='<p class="psa-shortcode-links"><a href="'.esc_url(wp_lostpassword_url()).'">'.esc_html__('Lost your password?','preloader-studio-animated-loading-screens').'</a>';
        if (get_option('users_can_register')) {
            $html.=' · <a href="'.esc_url(wp_registration_url()).'">'.esc_html__('Register','preloader-studio-animated-loading-screens').'</a>';
        }
        return '<div class="psa-auth-shortcode">'.$html.'</p></div>';
    }
    private static function logo_url($s) {
        $id = absint($s['logo_id']);
        if (!$id) { $id = absint(get_theme_mod('custom_logo',0)); }
        $url = $id ? wp_get_attachment_image_url($id,'medium') : '';
        return $url ? (string)$url : '';
    }
    public static function admin_enqueue() { PRELSTAN_Studio_Auth_Suite::admin_enqueue(); }
    public static function admin_page() {
        PRELSTAN_Studio_Auth_Suite::admin_page();
        return;

        $s=self::settings(); $styles=self::presets(); $logo=self::logo_url($s);
        ?>
        <div class="psa-studio" id="psa-studio">
            <header class="psa-editor-head"><div><span class="psa-eyebrow">ACCOUNT EXPERIENCE STUDIO · 24 DESIGNS</span><h2>Login &amp; Registration Studio</h2><p>Style WordPress login, registration and password recovery, plus WooCommerce My Account. Original authentication and security behavior stays in place.</p></div><span class="psa-status <?php echo !empty($s['enabled'])?'is-on':'is-off'; ?>"><?php echo !empty($s['enabled'])?'Live / Enabled':'Draft / Disabled'; ?></span></header>
            <?php
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin notice; no form processing or state change.
            if ( isset( $_GET['updated'] ) && 'auth_restored' === sanitize_key( wp_unslash( $_GET['updated'] ) ) ) : ?><div class="notice notice-success"><p>Default WordPress and WooCommerce login styling restored. Your saved design is kept for later.</p></div><?php endif; ?>
            <form id="psa-settings" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="ps_studio_auth_save" /><?php wp_nonce_field('ps_studio_auth_save','ps_auth_nonce'); ?>
                <section class="psa-editor-section"><div class="psa-section-head"><div><span>01 / PUBLISH</span><h3>Safe activation controls</h3></div></div>
                    <div class="psa-toggles">
                        <label><input type="checkbox" name="ps_auth[enabled]" value="1" <?php checked($s['enabled'],1); ?> /> <strong>Enable premium login design</strong><small>OFF by default. Switch OFF any time to restore the original forms.</small></label>
                        <label><input type="checkbox" name="ps_auth[wp_login]" value="1" <?php checked($s['wp_login'],1); ?> /><strong>WordPress Login / Register / Reset</strong><small>Uses wp-login.php and existing authentication processing.</small></label>
                        <label><input type="checkbox" name="ps_auth[woocommerce]" value="1" <?php checked($s['woocommerce'],1); ?> /><strong>WooCommerce My Account</strong><small>Styles guest login/register forms without replacing WooCommerce handlers.</small></label>
                        <label><input type="checkbox" name="ps_auth[native_wc_form]" value="1" <?php checked($s['native_wc_form'],1); ?> /><strong>WooCommerce Original Form Compatibility</strong><small>Recommended for WoodMart: use WooCommerce's core Login/Register form HTML with all core hooks, registration rules and nonces, then apply the premium design. Switch off to retain the theme's custom form template.</small></label>
                    </div>
                </section>
                <section class="psa-editor-section"><div class="psa-section-head"><div><span>02 / DESIGN GALLERY</span><h3>Choose your visual identity</h3></div><span>24 distinctly styled presets</span></div>
                    <div class="psa-gallery-filter" role="group" aria-label="Filter login designs"><button type="button" class="is-selected" data-psa-filter="all">All 24</button><button type="button" data-psa-filter="Minimal">Minimal</button><button type="button" data-psa-filter="Glass">Glass</button><button type="button" data-psa-filter="Dark">Dark</button><button type="button" data-psa-filter="Editorial">Editorial</button><button type="button" data-psa-filter="Luxury">Luxury</button><button type="button" data-psa-filter="Creative">Creative</button><button type="button" data-psa-filter="Gradient">Gradient</button></div>
                    <div class="psa-gallery" id="psa-gallery">
                    <?php foreach($styles as $slug=>$meta): ?>
                        <button type="button" class="psa-style-card <?php echo $s['preset']===$slug?'is-selected':''; ?>" data-style="<?php echo esc_attr($slug); ?>" data-category="<?php echo esc_attr($meta[1]); ?>" aria-pressed="<?php echo $s['preset']===$slug?'true':'false'; ?>">
                            <span class="psa-mini" style="--psa-accent:<?php echo esc_attr($meta[2]); ?>;--psa-background:<?php echo esc_attr($meta[3]); ?>;--psa-panel:<?php echo esc_attr($meta[4]); ?>;--psa-ink:<?php echo esc_attr($meta[5]); ?>"><i class="psa-mini-art"></i><i class="psa-mini-panel"><i></i><i></i><i class="psa-mini-cta"></i></i></span>
                            <strong><?php echo esc_html($meta[0]); ?></strong><small><?php echo esc_html($meta[1]); ?></small>
                        </button>
                    <?php endforeach; ?>
                    </div>
                    <input id="psa-preset" type="hidden" name="ps_auth[preset]" value="<?php echo esc_attr($s['preset']); ?>" />
                </section>
                <section class="psa-editor-section psa-editor-fields"><div class="psa-section-head"><div><span>03 / BRANDING</span><h3>Make it yours</h3></div></div>
                    <div class="psa-fields-grid">
                        <label>Accent color<input type="color" id="psa-accent" name="ps_auth[accent]" value="<?php echo esc_attr($s['accent']); ?>" /></label>
                        <label>Canvas color<input type="color" id="psa-background" name="ps_auth[background]" value="<?php echo esc_attr($s['background']); ?>" /></label>
                        <label>Welcome heading<input maxlength="75" id="psa-headline" name="ps_auth[headline]" value="<?php echo esc_attr($s['headline']); ?>" /></label>
                        <label>Supporting description<input maxlength="160" id="psa-subtitle" name="ps_auth[subtitle]" value="<?php echo esc_attr($s['subtitle']); ?>" /></label>
                    </div>
                    <div class="psa-upload-control"><span class="psa-upload-label">Your logo (or use the WordPress Site Logo)</span><input id="psa-logo-id" type="hidden" name="ps_auth[logo_id]" value="<?php echo absint($s['logo_id']); ?>" /><button type="button" class="button" id="psa-pick-logo">Select Logo</button><button type="button" class="button button-link" id="psa-remove-logo">Use Site Logo</button><span id="psa-logo-indicator"><?php echo $logo ? 'Logo ready' : 'Text branding'; ?></span></div>
                </section>
                <section class="psa-editor-section"><div class="psa-section-head"><div><span>04 / DEVICE PREVIEW</span><h3>Realistic login and registration preview</h3></div><span>Preview only · no credentials are sent</span></div>
                    <div class="psa-preview-controls"><div role="group" aria-label="Device preview"><button type="button" class="is-selected" data-device="desktop">Desktop</button><button type="button" data-device="tablet">Tablet</button><button type="button" data-device="mobile">Mobile</button></div><div role="group" aria-label="Form preview"><button type="button" class="is-selected" data-view="login">Login</button><button type="button" data-view="register">Register</button><button type="button" data-view="reset">Reset password</button></div></div>
                    <div class="psa-preview-stage"><div class="psa-device psa-device-desktop" id="psa-device"><div class="psa-canvas psa-design--<?php echo esc_attr($s['preset']); ?>" data-preset="<?php echo esc_attr($s['preset']); ?>" style="<?php echo esc_attr(self::css_variables($s)); ?>">
                        <div class="psa-canvas-art"><span class="psa-deco-mark">✳</span><span>YOUR SPACE · YOUR IDENTITY</span><strong id="psa-hero-copy">Designed for a better welcome.</strong><em>Premium account experience</em></div>
                        <div class="psa-canvas-form"><div class="psa-canvas-brand" id="psa-preview-logo"><?php if($logo): ?><img src="<?php echo esc_url($logo); ?>" alt="" /><?php else: ?><span class="psa-brand-symbol">✳</span><?php endif; ?></div><span class="psa-canvas-kicker">ACCOUNT ACCESS</span><h3 id="psa-heading-preview"><?php echo esc_html($s['headline']); ?></h3><p id="psa-subtitle-preview"><?php echo esc_html($s['subtitle']); ?></p>
                            <div class="psa-form-visual" aria-label="Decorative form preview"><label>Email address<span class="psa-input-fake">name@example.com</span></label><label class="psa-password-field">Password<span class="psa-input-fake">••••••••••</span></label><div class="psa-form-row"><span>☑ Remember me</span><span>Forgot password?</span></div><div class="psa-preview-button" id="psa-preview-button">Sign in securely →</div><div class="psa-preview-footer" id="psa-preview-footer">New here? Create account</div></div>
                        </div>
                    </div></div></div>
                    <p class="description">The preview shows design, shape and responsive arrangement. Live authentication is handled by WordPress/WooCommerce; the exact layout may differ when a theme supplies custom form markup.</p>
                </section>
                <div class="psa-editor-bottom"><span>Publishing changes only this Login Studio. Your product layouts, preloaders and bottom navigation are untouched.</span><button class="button button-primary" type="submit">Save Login Studio</button></div>
            </form>
            <form class="psa-restore-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="ps_studio_auth_restore" /><?php wp_nonce_field('ps_studio_auth_restore','ps_auth_restore_nonce'); ?><button class="button" type="submit">Restore default WordPress / Theme login</button><small>Restores immediately, without deleting the design settings you saved.</small></form>
            <div class="psa-compat-note"><h3>Test the real forms</h3><p>Save your design first, then visit the native account pages to verify the visual treatment and your existing captcha/2FA integrations.</p><p><a class="button" href="<?php echo esc_url(wp_login_url()); ?>" target="_blank" rel="noopener noreferrer">Open WordPress Login ↗</a> <?php if (function_exists('wc_get_page_permalink')) : ?><a class="button" href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" target="_blank" rel="noopener noreferrer">Open WooCommerce My Account ↗</a><?php endif; ?></p><h3>Compatibility and security</h3><p>WordPress registration is available only if Settings → General → Anyone can register is enabled. WooCommerce My Account registration follows WooCommerce → Settings → Accounts &amp; Privacy. These controls never bypass site policy, nonces, passwords, CAPTCHA, 2FA or existing authentication plugins. WoodMart/Elementor Layout Builder may bypass the WooCommerce core form template; if so, use WooCommerce My Account shortcode/widget or disable the conflicting WoodMart Login/Register layout. A separate theme sidebar popup may need its own styling. No CAPTCHA, 2FA or form handler is bypassed.</p><p>Optional shortcode for a dedicated account area: <code>[preloader_studio_auth]</code>. For best WooCommerce integration, use the existing WooCommerce My Account page.</p></div>
        </div>
        <?php
    }
}
