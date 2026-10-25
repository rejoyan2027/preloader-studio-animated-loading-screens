<?php
/** Plugin-owned account screens; WooCommerce supplies authentication handlers, never markup. */
if ( ! defined('ABSPATH') ) { exit; }

final class PRELSTAN_Studio_Auth_Suite {
    const TEMPLATES = array('aurora-glass','editorial-split','midnight-neon','cloud-card','brutalist-ink','rose-atelier');
    public static function templates() {
        $all = PRELSTAN_Studio_Auth::presets(); $out=array();
        foreach (self::TEMPLATES as $key) { $out[$key]=$all[$key]; }
        return $out;
    }
    public static function chosen($settings) {
        $preset = isset($settings['preset']) ? $settings['preset'] : 'aurora-glass';
        if (in_array($preset,self::TEMPLATES,true)) { return $preset; }
        $map=array('terracotta'=>'editorial-split','emerald-vault'=>'midnight-neon',
            'monochrome'=>'cloud-card','sunset-wave'=>'aurora-glass','neon-circuit'=>'midnight-neon',
            'velvet-royal'=>'rose-atelier','paper-fold'=>'editorial-split','cosmic-orbit'=>'midnight-neon',
            'bento-tiles'=>'cloud-card','sakura-blossom'=>'rose-atelier','retro-terminal'=>'brutalist-ink',
            'golden-gallery'=>'editorial-split','arctic-frost'=>'cloud-card','lilac-ribbon'=>'aurora-glass',
            'citrus-energy'=>'brutalist-ink','prism-depth'=>'aurora-glass','ocean-tide'=>'cloud-card',
            'forest-calm'=>'cloud-card');
        return isset($map[$preset]) ? $map[$preset] : 'aurora-glass';
    }
    public static function init() {
        add_shortcode('preloader_studio_auth',array(__CLASS__,'shortcode'));
        add_filter('template_include',array(__CLASS__,'account_template'),999999);
        add_action('wp_enqueue_scripts',array(__CLASS__,'maybe_enqueue'),1000);
        add_action('admin_post_nopriv_prelstan_login',array(__CLASS__,'login'));
        add_action('admin_post_nopriv_prelstan_register',array(__CLASS__,'register'));
        add_action('admin_post_nopriv_prelstan_request_reset',array(__CLASS__,'request_reset'));
        add_action('admin_post_nopriv_prelstan_finish_reset',array(__CLASS__,'finish_reset'));
        add_filter('login_url',array(__CLASS__,'login_url'),30,3);
        add_filter('register_url',array(__CLASS__,'register_url'),30);
        add_filter('lostpassword_url',array(__CLASS__,'lost_url'),30,2);
        add_filter('woocommerce_process_registration_errors',array(__CLASS__,'woocommerce_validate_custom'),10,4);
        add_action('woocommerce_created_customer',array(__CLASS__,'woocommerce_save_custom'),10,1);
        add_action('woocommerce_edit_account_form',array(__CLASS__,'profile_fields'));
        add_action('woocommerce_save_account_details_errors',array(__CLASS__,'validate_profile_fields'),10,1);
        add_action('woocommerce_save_account_details',array(__CLASS__,'save_profile_fields'),10,1);
    }
    public static function enabled($area='woocommerce') {
        $s=PRELSTAN_Studio_Auth::settings();
        return !empty($s['independent_mode']) && PRELSTAN_Studio_Auth::enabled_for($area);
    }
    public static function is_account_guest() {
        return self::enabled('woocommerce') && function_exists('is_account_page') && is_account_page()
            && !is_user_logged_in() && !is_admin() && !wp_doing_ajax();
    }
    public static function account_template($template) {
        if (!self::is_account_guest() || (function_exists('is_feed') && is_feed())) { return $template; }
        $our = PRELSTAN_STUDIO_DIR.'templates/auth-account.php';
        return is_readable($our) ? $our : $template;
    }
    public static function url($view='login') {
        if (function_exists('wc_get_page_permalink') && self::enabled('woocommerce')) {
            $wc=wc_get_page_permalink('myaccount');
            if ($wc && $wc !== '#') { return add_query_arg('psa_view',$view,$wc); }
        }
        $id = (int) get_option('ps_studio_auth_page',0);
        if ($id && get_post_status($id)==='publish') { return add_query_arg('psa_view',$view,get_permalink($id)); }
        return '';
    }
    // Never rewrite wp-login URLs until an account route explicitly exists; retain emergency recovery.
    public static function login_url($url,$redirect='',$force_reauth=false) {
        if (!self::enabled('wp_login') || $force_reauth) { return $url; }
        $ours=self::url('login');
        return $ours ? ($redirect?add_query_arg('redirect_to',$redirect,$ours):$ours) : $url;
    }
    public static function register_url($url) {
        if (!self::enabled('wp_login')) {return $url;}
        $ours=self::url('register'); return $ours?:$url;
    }
    public static function lost_url($url,$redirect='') {
        if (!self::enabled('wp_login')) {return $url;}
        $ours=self::url('reset'); return $ours?:$url;
    }
    public static function maybe_enqueue() {
        global $post;
        $shortcode = isset($post->post_content) && has_shortcode($post->post_content,'preloader_studio_auth');
        if (self::is_account_guest() || ($shortcode && (self::enabled('woocommerce') || self::enabled('wp_login')))) {
            wp_enqueue_style('ps-studio-custom-auth',PRELSTAN_STUDIO_URL.'assets/css/auth-custom.css',array(),PRELSTAN_STUDIO_VERSION);
            wp_enqueue_script('ps-studio-custom-auth',PRELSTAN_STUDIO_URL.'assets/js/auth-custom.js',array(),PRELSTAN_STUDIO_VERSION,true);
        }
    }
    public static function shortcode($atts=array()) {
        if (!self::enabled('woocommerce') && !self::enabled('wp_login')) { return ''; }
        return self::render();
    }
    public static function render() {
        if (is_user_logged_in()) {
            return '<div class="psx-signed">'.esc_html__('You are already signed in.','preloader-studio-animated-loading-screens').' <a href="'.esc_url(function_exists('wc_get_page_permalink')?wc_get_page_permalink('myaccount'):admin_url('profile.php')).'">'.esc_html__('My account','preloader-studio-animated-loading-screens').'</a></div>';
        }
        $s=PRELSTAN_Studio_Auth::settings(); $preset=self::chosen($s);
        $colors=self::templates()[$preset];
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only choice of which form to display.
        $view=isset($_GET['psa_view']) ? sanitize_key(wp_unslash($_GET['psa_view'])) : 'login';
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only selection of password-recovery screen.
        if (!isset($_GET['psa_view']) && function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('lost-password')) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only password reset screen selection, not a state change.
            $view=isset($_GET['key']) ? 'set-password' : 'reset';
        }
        if (!in_array($view,array('login','register','reset','set-password'),true)) { $view='login'; }
        $can_register = self::can_register();
        if ($view==='register' && !$can_register) { $view='login'; }
        $logo=self::logo_url($s);
        $inline=sprintf('--psx-accent:%s;--psx-canvas:%s;--psx-panel:%s;--psx-ink:%s;',esc_attr($s['accent']),esc_attr($s['background']),esc_attr($colors[4]),esc_attr($colors[5]));
        ob_start(); ?>
        <main class="psx-account psx-<?php echo esc_attr($preset); ?>" style="<?php echo esc_attr($inline); ?>" data-template="<?php echo esc_attr($preset); ?>">
            <section class="psx-layout" aria-label="<?php esc_attr_e('Account access','preloader-studio-animated-loading-screens'); ?>">
                <aside class="psx-art"><div class="psx-art-inner"><div class="psx-art-top"><span class="psx-art-symbol" aria-hidden="true">✳</span><span><?php esc_html_e('ACCOUNT STUDIO','preloader-studio-animated-loading-screens'); ?></span></div>
                    <div class="psx-art-content"><span class="psx-eyebrow"><?php esc_html_e('A BETTER WAY TO CONNECT','preloader-studio-animated-loading-screens'); ?></span><h2><?php echo esc_html($s['headline']); ?></h2><p><?php echo esc_html($s['subtitle']); ?></p></div>
                    <div class="psx-art-bottom"><span><?php esc_html_e('Your account. Your experience.','preloader-studio-animated-loading-screens'); ?></span><span aria-hidden="true">↗</span></div></div></aside>
                <div class="psx-form-area"><div class="psx-form-inner">
                    <div class="psx-brand"><?php if($logo): ?><img src="<?php echo esc_url($logo); ?>" alt="" width="60" height="60" /><?php else: ?><span aria-hidden="true" class="psx-brand-icon">✳</span><?php endif; ?><span><?php echo esc_html(get_bloginfo('name')); ?></span></div>
                    <nav class="psx-switch" aria-label="<?php esc_attr_e('Account actions','preloader-studio-animated-loading-screens'); ?>"><a class="<?php echo $view==='login'?'psx-current':''; ?>" href="<?php echo esc_url(self::url_for_view('login')); ?>" <?php echo $view==='login'?'aria-current="page"':''; ?>><?php esc_html_e('Sign in','preloader-studio-animated-loading-screens'); ?></a><?php if($can_register): ?><a class="<?php echo $view==='register'?'psx-current':''; ?>" href="<?php echo esc_url(self::url_for_view('register')); ?>" <?php echo $view==='register'?'aria-current="page"':''; ?>><?php esc_html_e('Create account','preloader-studio-animated-loading-screens'); ?></a><?php endif; ?></nav>
                    <?php self::notices(); ?>
                    <?php if($view==='register'): self::registration_form();
                    elseif($view==='reset'): self::reset_request_form();
                    elseif($view==='set-password'): self::new_password_form();
                    else: self::login_form(); endif; ?>
                    <p class="psx-security"><span aria-hidden="true">✦</span> <?php esc_html_e('Your account details are handled securely by this website.','preloader-studio-animated-loading-screens'); ?></p>
                </div></div>
            </section>
        </main>
        <?php return ob_get_clean();
    }
    private static function url_for_view($view) {
        $ours=self::url($view);
        if ($ours) { return $ours; }
        $here = get_permalink(); return $here ? add_query_arg('psa_view',$view,$here) : home_url('/');
    }
    private static function logo_url($s) {
        $id=(int)$s['logo_id']; if(!$id) {$id=(int)get_theme_mod('custom_logo',0);}
        return $id ? (string) wp_get_attachment_image_url($id,'medium') : '';
    }
    private static function can_register() {
        if (self::enabled('woocommerce') && function_exists('WC') && function_exists('is_account_page') && is_account_page()) {
            return get_option('woocommerce_enable_myaccount_registration','no')==='yes';
        }
        return (bool)get_option('users_can_register',false);
    }
    private static function notices() {
        if (function_exists('wc_print_notices')) { wc_print_notices(); }
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only status code from a validated redirect.
        $msg=isset($_GET['psa_message']) ? sanitize_key(wp_unslash($_GET['psa_message'])) : '';
        $messages=array('invalid'=>__('Could not complete the request. Please check your details and try again.','preloader-studio-animated-loading-screens'),
            'sent'=>__('If this email is registered, password reset instructions have been sent.','preloader-studio-animated-loading-screens'),
            'reset'=>__('Password updated. You can now sign in.','preloader-studio-animated-loading-screens'),
            'registered'=>__('Account created. Please check your email or sign in.','preloader-studio-animated-loading-screens'),
            'expired'=>__('This password reset link has expired. Request a new one.','preloader-studio-animated-loading-screens'),
            'limited'=>__('Too many requests. Please wait a little and try again.','preloader-studio-animated-loading-screens'));
        if(isset($messages[$msg])) { echo '<div class="psx-notice" role="status">'.esc_html($messages[$msg]).'</div>'; }
    }
    private static function field($name,$label,$type,$autocomplete,$required=true) {
        echo '<label class="psx-field"><span>'.esc_html($label).'</span><input type="'.esc_attr($type).'" name="'.esc_attr($name).'" autocomplete="'.esc_attr($autocomplete).'" '.($required?'required':'').' maxlength="254" /></label>';
    }
    private static function login_form() {
        $s=PRELSTAN_Studio_Auth::settings();
        echo '<span class="psx-kicker">'.esc_html__('WELCOME BACK','preloader-studio-animated-loading-screens').'</span><h1>'.esc_html($s['login_title']).'</h1><p class="psx-intro">'.esc_html($s['login_intro']).'</p>';
        $wc=self::enabled('woocommerce') && function_exists('WC') && function_exists('is_account_page') && is_account_page();
        $action=$wc ? self::url_for_view('login') : admin_url('admin-post.php');
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only optional redirect URL, subsequently validated by wp_validate_redirect.
        $redirect=isset($_GET['redirect_to']) && is_string($_GET['redirect_to']) ? wp_validate_redirect(esc_url_raw(wp_unslash($_GET['redirect_to'])),home_url('/')) : '';
        echo '<form method="post" action="'.esc_url($action).'" class="psx-form psx-login-form">';
        if(!$wc){ echo '<input type="hidden" name="action" value="prelstan_login">'; wp_nonce_field('prelstan_submit_login','prelstan_nonce'); }
        if($redirect){echo '<input type="hidden" name="redirect" value="'.esc_attr($redirect).'"><input type="hidden" name="redirect_to" value="'.esc_attr($redirect).'">';}
        self::field('username',$s['login_user_label'],'text','username');
        echo '<div class="psx-password-wrap">';self::field('password',$s['login_password_label'],'password','current-password'); echo '<button type="button" class="psx-eye" aria-label="Show password" aria-pressed="false">◉</button></div>';
        echo '<div class="psx-form-meta"><label class="psx-check"><input type="checkbox" name="rememberme" value="forever"><span>'.esc_html__('Remember me','preloader-studio-animated-loading-screens').'</span></label><a href="'.esc_url(self::url_for_view('reset')).'">'.esc_html__('Forgot password?','preloader-studio-animated-loading-screens').'</a></div>';
        if($wc){do_action('woocommerce_login_form_start');do_action('woocommerce_login_form');do_action('woocommerce_login_form_end');wp_nonce_field('woocommerce-login','woocommerce-login-nonce');echo '<input type="hidden" name="login" value="1">';}
        echo '<button type="submit" class="psx-submit">'.esc_html($s['login_button']).' <span aria-hidden="true">→</span></button></form>';
    }
    private static function registration_form() {
        $s=PRELSTAN_Studio_Auth::settings();
        echo '<span class="psx-kicker">'.esc_html__('JOIN THE COMMUNITY','preloader-studio-animated-loading-screens').'</span><h1>'.esc_html($s['register_title']).'</h1><p class="psx-intro">'.esc_html($s['register_intro']).'</p>';
        $wc=self::enabled('woocommerce') && function_exists('WC') && function_exists('is_account_page') && is_account_page();
        echo '<form class="psx-form" method="post" action="'.esc_url($wc?self::url_for_view('register'):admin_url('admin-post.php')).'">';
        if(!$wc){echo '<input type="hidden" name="action" value="prelstan_register">';wp_nonce_field('prelstan_submit_register','prelstan_nonce');}
        $need_user=!$wc || get_option('woocommerce_registration_generate_username','yes')==='no';
        if($need_user){self::field('username',__('Username','preloader-studio-animated-loading-screens'),'text','username');}
        self::field('email',__('Email address','preloader-studio-animated-loading-screens'),'email','email');
        $need_pass=$wc && get_option('woocommerce_registration_generate_password','yes')==='no';
        if($need_pass) {self::field('password',__('Choose password','preloader-studio-animated-loading-screens'),'password','new-password');}
        self::render_custom_fields();
        if($wc){echo '<input type="hidden" name="psx_form" value="register" />';do_action('woocommerce_register_form_start');do_action('woocommerce_register_form');do_action('woocommerce_register_form_end');wp_nonce_field('woocommerce-register','woocommerce-register-nonce');echo '<input type="hidden" name="register" value="1">';}
        echo '<p class="psx-legal">'.esc_html__('By registering, you agree to the website policies. Your details are handled by the website owner.','preloader-studio-animated-loading-screens').'</p>';
        echo '<button class="psx-submit" type="submit">'.esc_html($s['register_button']).' <span aria-hidden="true">→</span></button></form>';
    }

    /** Render extra fields only on the plugin's registration screen. */
    public static function render_custom_fields() {
        $s=PRELSTAN_Studio_Auth::settings();
        foreach($s['register_fields'] as $f) {
            $key=$f['key'];$req=!empty($f['required']);
            echo '<label class="psx-field psx-extra"><span>'.esc_html($f['label']).($req?' <b aria-hidden="true">*</b>':'').'</span>';
            $name='psx_extra['.$key.']';
            if($f['type']==='textarea') {echo '<textarea name="'.esc_attr($name).'" rows="3" maxlength="500" placeholder="'.esc_attr($f['placeholder']).'" '.($req?'required':'').'></textarea>';}
            elseif($f['type']==='select') {
                echo '<select name="'.esc_attr($name).'" '.($req?'required':'').'><option value="">'.esc_html__('Select an option','preloader-studio-animated-loading-screens').'</option>';
                foreach($f['options'] as $opt)echo '<option value="'.esc_attr($opt).'">'.esc_html($opt).'</option>';
                echo '</select>';
            } elseif($f['type']==='checkbox') {
                echo '<span class="psx-check-custom"><input type="checkbox" name="'.esc_attr($name).'" value="1" '.($req?'required':'').'> '.esc_html($f['placeholder']?:$f['label']).'</span>';
            }else{
                echo '<input type="'.esc_attr($f['type']).'" name="'.esc_attr($name).'" maxlength="190" placeholder="'.esc_attr($f['placeholder']).'" '.($req?'required':'').' />';
            }
            echo '</label>';
        }
    }
    private static function posted_custom() {
        // Registration is protected by prelstan_nonce / WooCommerce's registration nonce;
        // account edits use WooCommerce's edit-account nonce. Restrict input to defined keys.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The parent form's nonce is checked in its action handler.
        if ( ! isset( $_POST['psx_extra'] ) || ! is_array( $_POST['psx_extra'] ) ) { return array(); }
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Values are unslashed and sanitized by their field type below.
        $raw = wp_unslash( $_POST['psx_extra'] );
        $result = array();
        foreach ( PRELSTAN_Studio_Auth::settings()['register_fields'] as $field ) {
            $key = $field['key'];
            if ( ! array_key_exists( $key, $raw ) || ! is_scalar( $raw[$key] ) ) { continue; }
            $value = (string) $raw[$key];
            if ( 'textarea' === $field['type'] ) { $result[$key] = sanitize_textarea_field( $value ); }
            elseif ( 'email' === $field['type'] ) { $result[$key] = sanitize_email( $value ); }
            else { $result[$key] = sanitize_text_field( $value ); }
        }
        return $result;
    }
    public static function validate_custom($errors) {
        $posted=self::posted_custom(); $s=PRELSTAN_Studio_Auth::settings();
        foreach($s['register_fields'] as $f) {
            $v=$posted[$f['key']]??'';
            $value=is_scalar($v) ? trim((string)$v) : '';
            /* translators: %s: label of a required custom registration field. */
            if(!empty($f['required']) && ($value==='' || ($f['type']==='checkbox' && $value!=='1'))){$errors->add('psx_required_'.$f['key'],sprintf(__('%s is required.','preloader-studio-animated-loading-screens'),$f['label']));continue;}
            if($value==='' || ($f['type']==='checkbox' && $value==='1'))continue;
            if($f['type']==='email' && !is_email($value))$errors->add('psx_email_'.$f['key'],__('Enter a valid email.','preloader-studio-animated-loading-screens'));
            if($f['type']==='select' && !in_array($value,$f['options'],true))$errors->add('psx_select_'.$f['key'],__('Select an available option.','preloader-studio-animated-loading-screens'));
            if($f['type']==='tel' && !preg_match('/^[0-9+(). \-]{3,40}$/',$value))$errors->add('psx_tel_'.$f['key'],__('Enter a valid phone number.','preloader-studio-animated-loading-screens'));
            if(strlen($value)>500)$errors->add('psx_length_'.$f['key'],__('Field value is too long.','preloader-studio-animated-loading-screens'));
        }
        return $errors;
    }
    public static function woocommerce_validate_custom($errors,$username='',$email='') {
        // WooCommerce calls this during its own validation, not via a fake checkout.
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- WooCommerce checks its nonce before registration processing.
        if(self::enabled('woocommerce') && !empty($_POST['register']) && isset($_POST['psx_form']) && $_POST['psx_form']==='register')return self::validate_custom($errors);
        return $errors;
    }
    public static function save_custom($user_id,$posted_override=null) {
        $posted=is_array($posted_override)?$posted_override:self::posted_custom();$s=PRELSTAN_Studio_Auth::settings();
        foreach($s['register_fields'] as $f){
            if(!array_key_exists($f['key'],$posted) || !is_scalar($posted[$f['key']]))continue;
            $raw=(string)$posted[$f['key']];
            if($f['type']==='checkbox')$value=$raw==='1'?'Yes':'No';
            elseif($f['type']==='email')$value=sanitize_email($raw);
            elseif($f['type']==='textarea')$value=substr(sanitize_textarea_field($raw),0,500);
            else $value=substr(sanitize_text_field($raw),0,190);
            if($f['type']==='select' && !in_array($value,$f['options'],true))continue;
            update_user_meta($user_id,'psx_field_'.$f['key'],$value);
        }
    }
    public static function woocommerce_save_custom($user_id) {
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- WooCommerce checks its nonce before registration processing.
        if(self::enabled('woocommerce') && !empty($_POST['register']) && isset($_POST['psx_form']) && $_POST['psx_form']==='register')self::save_custom($user_id);
    }

    /** WooCommerce's edit-account endpoint can update custom metadata with its own nonce. */
    public static function profile_fields() {
        if(!PRELSTAN_Studio_Account_Dashboard::enabled())return;
        $s=PRELSTAN_Studio_Auth::settings();
        if(!$s['register_fields'])return;
        echo '<fieldset class="psx-profile-fields"><legend>'.esc_html__('More about you','preloader-studio-animated-loading-screens').'</legend>';
        foreach($s['register_fields'] as $f) {
            $value=(string)get_user_meta(get_current_user_id(),'psx_field_'.$f['key'],true);
            $name='psx_extra['.$f['key'].']';
            echo '<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide"><label for="psx-profile-'.esc_attr($f['key']).'">'.esc_html($f['label']).(!empty($f['required'])?' <span class="required">*</span>':'').'</label>';
            $id='psx-profile-'.$f['key'];
            if($f['type']==='textarea') echo '<textarea id="'.esc_attr($id).'" name="'.esc_attr($name).'" maxlength="500" rows="3">'.esc_textarea($value).'</textarea>';
            elseif($f['type']==='select'){
                echo '<select id="'.esc_attr($id).'" name="'.esc_attr($name).'"><option value="">'.esc_html__('Select','preloader-studio-animated-loading-screens').'</option>';
                foreach($f['options'] as $opt)echo '<option value="'.esc_attr($opt).'" '.selected($value,$opt,false).'>'.esc_html($opt).'</option>';
                echo '</select>';
            } elseif($f['type']==='checkbox')echo '<input id="'.esc_attr($id).'" type="checkbox" name="'.esc_attr($name).'" value="1" '.checked($value,'Yes',false).' />';
            else echo '<input id="'.esc_attr($id).'" type="'.esc_attr($f['type']).'" name="'.esc_attr($name).'" maxlength="190" value="'.esc_attr($value).'" />';
            echo '</p>';
        }
        echo '</fieldset>';
    }
    public static function validate_profile_fields($errors) {
        if(PRELSTAN_Studio_Account_Dashboard::enabled())self::validate_custom($errors);
    }
    public static function save_profile_fields($user_id) {
        // WooCommerce validates its edit-account nonce and identity before this hook.
        if ( ! is_user_logged_in() || get_current_user_id() !== (int) $user_id ) { return; }
        if(PRELSTAN_Studio_Account_Dashboard::enabled()) {
            $posted=self::posted_custom();
            foreach(PRELSTAN_Studio_Auth::settings()['register_fields'] as $f) {
                if($f['type']==='checkbox' && !array_key_exists($f['key'],$posted))$posted[$f['key']]='0';
            }
            // Treat absent checkboxes as unchecked only when editing the protected form.
            self::save_custom($user_id,$posted);
        }
    }

    private static function reset_request_form() {
        echo '<span class="psx-kicker">'.esc_html__('ACCOUNT RECOVERY','preloader-studio-animated-loading-screens').'</span><h1>'.esc_html__('Forgot your password?','preloader-studio-animated-loading-screens').'</h1><p class="psx-intro">'.esc_html__('Enter your email to receive secure reset instructions.','preloader-studio-animated-loading-screens').'</p>';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" class="psx-form"><input type="hidden" name="action" value="prelstan_request_reset">'; wp_nonce_field('prelstan_request_reset','prelstan_nonce');
        self::field('user_login',__('Email or username','preloader-studio-animated-loading-screens'),'text','username');
        echo '<button class="psx-submit" type="submit">'.esc_html__('Send reset link','preloader-studio-animated-loading-screens').' <span aria-hidden="true">→</span></button></form><p class="psx-back"><a href="'.esc_url(self::url_for_view('login')).'">← '.esc_html__('Back to sign in','preloader-studio-animated-loading-screens').'</a></p>';
    }
    private static function new_password_form() {
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only password reset key; validated with check_password_reset_key.
        $key=isset($_GET['key'])?sanitize_text_field(wp_unslash($_GET['key'])):'';
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only user name for reset link; validated with check_password_reset_key.
        $login=isset($_GET['login']) && is_string($_GET['login']) ? sanitize_user(wp_unslash($_GET['login'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only account ID for reset link lookup.
        if (!$login && !empty($_GET['id']) && function_exists('get_userdata')) { $lookup=get_userdata(absint($_GET['id'])); $login=$lookup ? $lookup->user_login : ''; }
        $user=$key && $login ? check_password_reset_key($key,$login):new WP_Error('invalid_key');
        if(is_wp_error($user)) { echo '<h1>'.esc_html__('Reset link expired','preloader-studio-animated-loading-screens').'</h1><p><a href="'.esc_url(self::url_for_view('reset')).'">'.esc_html__('Request a new link','preloader-studio-animated-loading-screens').'</a></p>';return; }
        echo '<span class="psx-kicker">'.esc_html__('ACCOUNT SECURITY','preloader-studio-animated-loading-screens').'</span><h1>'.esc_html__('Choose a new password','preloader-studio-animated-loading-screens').'</h1>';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" class="psx-form"><input type="hidden" name="action" value="prelstan_finish_reset"><input type="hidden" name="login" value="'.esc_attr($login).'"><input type="hidden" name="key" value="'.esc_attr($key).'">';wp_nonce_field('prelstan_finish_reset','prelstan_nonce');
        self::field('password',__('New password','preloader-studio-animated-loading-screens'),'password','new-password');
        echo '<button class="psx-submit" type="submit">'.esc_html__('Update password','preloader-studio-animated-loading-screens').' →</button></form>';
    }
    private static function valid_request($action) {
        return isset($_POST['prelstan_nonce']) && is_string($_POST['prelstan_nonce'])
            && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['prelstan_nonce'])),$action);
    }
    private static function throttle($purpose) {
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- IP is unslashed and sanitized before hashing rate-limit key.
        $ip=isset($_SERVER['REMOTE_ADDR']) && is_scalar($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash((string)$_SERVER['REMOTE_ADDR'])) : 'unknown';
        $name='prelstan_rl_'.substr(hash('sha256',$purpose.'|'.$ip.wp_salt('auth')),0,30);
        $count=(int)get_transient($name);set_transient($name,$count+1,15*MINUTE_IN_SECONDS);
        return $count >= 7;
    }
    private static function go($view,$message='') {
        $url=self::url_for_view($view);
        if($message){$url=add_query_arg('psa_message',$message,$url);}
        wp_safe_redirect($url);exit;
    }
    public static function login() {
        if(!self::valid_request('prelstan_submit_login') || (!self::enabled('wp_login') && !self::enabled('woocommerce'))) {self::go('login','invalid');}
        if(self::throttle('login')) {self::go('login','limited');}
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- valid_request checks psx login nonce before this branch.
        $user=isset($_POST['username']) && is_string($_POST['username']) ? sanitize_text_field(wp_unslash($_POST['username'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Nonce verified; password must remain byte-for-byte intact for wp_signon.
        $pass=isset($_POST['password'])&&is_string($_POST['password'])?wp_unslash($_POST['password']):'';
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Nonce verified by valid_request; rememberme is boolean.
        $credentials=array('user_login'=>$user,'user_password'=>$pass,'remember'=>!empty($_POST['rememberme']));
        $signed=wp_signon($credentials,is_ssl());
        if(is_wp_error($signed)) {self::go('login','invalid');}
        $fallback=function_exists('wc_get_page_permalink')?wc_get_page_permalink('myaccount'):home_url('/');
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Nonce checked; wp_validate_redirect restricts destination.
        $destination=isset($_POST['redirect_to']) && is_string($_POST['redirect_to']) ? wp_validate_redirect(esc_url_raw(wp_unslash($_POST['redirect_to'])),$fallback) : $fallback;
        wp_safe_redirect($destination);exit;
    }
    public static function register() {
        if(!self::valid_request('prelstan_submit_register') || (!self::enabled('wp_login') && !self::enabled('woocommerce')) || !get_option('users_can_register')) {self::go('register','invalid');}
        if(self::throttle('register')) {self::go('register','limited');}
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Nonce checked and sanitized with sanitize_user.
        $username=isset($_POST['username']) && is_string($_POST['username'])?sanitize_user(wp_unslash($_POST['username'])):'';
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Nonce checked and sanitized with sanitize_email.
        $email=isset($_POST['email']) && is_string($_POST['email'])?sanitize_email(wp_unslash($_POST['email'])):'';
        if(!$username || !validate_username($username) || username_exists($username) || !is_email($email) || email_exists($email)) {self::go('register','invalid');}
        $validation=self::validate_custom(new WP_Error());if($validation->has_errors()){self::go('register','invalid');}
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Standard WordPress core registration lifecycle hook required by integrations.
        do_action('register_post',$username,$email,$validation);
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Standard WordPress registration_errors filter required by integrations.
        $validation=apply_filters('registration_errors',$validation,$username,$email);
        if(is_wp_error($validation) && $validation->has_errors()) {self::go('register','invalid');}
        $id=wp_create_user($username,wp_generate_password(24,true,true),$email);
        if(is_wp_error($id)) {self::go('register','invalid');}
        self::save_custom($id);
        // Fire the standard WordPress registration lifecycle for notification and integrations.
        // WordPress core provides the notification handler on this action.
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Intentionally invoke the WordPress core registration hook so notifications/integrations work.
        do_action('register_new_user',$id);
        self::go('login','registered');
    }
    public static function password_message($message,$key,$user_login,$user_data) {
        $url=self::url_for_view('set-password');
        $url=add_query_arg(array('key'=>$key,'login'=>$user_login),$url);
        /* translators: %s: website name. */
        return sprintf(__('A password reset was requested for %s.','preloader-studio-animated-loading-screens'),get_bloginfo('name'))."\n\n".__('Use this secure link to reset your password:','preloader-studio-animated-loading-screens')."\n".esc_url_raw($url)."\n\n".__('If you did not request this, ignore this email.','preloader-studio-animated-loading-screens');
    }
    public static function request_reset() {
        if(!self::valid_request('prelstan_request_reset')) {self::go('reset','invalid');}
        if(self::throttle('reset')) {self::go('reset','limited');}
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Nonce checked by valid_request before password request.
        $login=isset($_POST['user_login']) && is_string($_POST['user_login']) ? sanitize_text_field(wp_unslash($_POST['user_login'])) : '';
        add_filter('retrieve_password_message',array(__CLASS__,'password_message'),10,4);
        if($login){retrieve_password($login);}
        remove_filter('retrieve_password_message',array(__CLASS__,'password_message'),10);
        self::go('reset','sent');
    }
    public static function finish_reset() {
        if(!self::valid_request('prelstan_finish_reset')){self::go('reset','invalid');}
        if(self::throttle('finish_reset')) {self::go('reset','limited');}
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Nonce checked by valid_request; reset key is validated by core.
        $key=isset($_POST['key']) && is_string($_POST['key']) ? sanitize_text_field(wp_unslash($_POST['key'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Nonce checked by valid_request; username sanitized.
        $login=isset($_POST['login']) && is_string($_POST['login'])?sanitize_user(wp_unslash($_POST['login'])):'';
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Nonce checked; password must remain untouched for reset_password.
        $password=isset($_POST['password'])&&is_string($_POST['password'])?wp_unslash($_POST['password']):'';
        $user=$key && $login ? check_password_reset_key($key,$login):new WP_Error('invalid');
        if(is_wp_error($user)||strlen($password)<10){self::go('reset','invalid');}
        reset_password($user,$password);self::go('login','reset');
    }
    public static function admin_enqueue() {
        wp_enqueue_media();
        wp_enqueue_style('psx-suite',PRELSTAN_STUDIO_URL.'assets/css/auth-custom.css',array(),PRELSTAN_STUDIO_VERSION);
        wp_enqueue_style('psx-suite-admin',PRELSTAN_STUDIO_URL.'assets/css/auth-suite-admin.css',array('psx-suite'),PRELSTAN_STUDIO_VERSION);
        wp_enqueue_script('psx-suite-admin',PRELSTAN_STUDIO_URL.'assets/js/auth-suite-admin.js',array(),PRELSTAN_STUDIO_VERSION,true);
        wp_localize_script('psx-suite-admin','prelstan_auth_admin',array('presets'=>self::templates(),'fields'=>PRELSTAN_Studio_Auth::settings()['register_fields']));
    }
    public static function admin_page() {
        $s=PRELSTAN_Studio_Auth::settings();$styles=self::templates();$selected=self::chosen($s);
        $logo=self::logo_url($s); ?>
        <div class="psx-admin" id="psx-admin">
            <header class="psx-admin-heading"><span>PRELOADER STUDIO / AUTHENTICATION</span><h2>Independent Login &amp; Register Studio</h2><p>Six custom-built premium designs. No WooCommerce or theme login form HTML is displayed. Safe WordPress/WooCommerce authentication processing is retained.</p></header>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="psx-design-editor">
                <input type="hidden" name="action" value="ps_studio_auth_save" /><?php wp_nonce_field('ps_studio_auth_save','ps_auth_nonce'); ?>
                <section class="psx-admin-panel"><h3>01 · Publishing &amp; integration</h3>
                    <div class="psx-admin-toggles"><label><input type="checkbox" name="ps_auth[independent_mode]" value="1" <?php checked(!empty($s['independent_mode']),1); ?>><strong>Activate New Independent Form Engine</strong><small>Explicitly opt in after updating from v2.4.x, so your existing account access never changes unexpectedly.</small></label><label><input type="checkbox" name="ps_auth[enabled]" value="1" <?php checked($s['enabled'],1); ?>><strong>Enable Independent Login Studio</strong><small>Switch OFF to restore all original theme and WooCommerce account forms.</small></label>
                    <label><input type="checkbox" name="ps_auth[woocommerce]" value="1" <?php checked($s['woocommerce'],1); ?>><strong>Replace WooCommerce My Account guest page</strong><small>Plugin-owned login/register design replaces the entire guest account template, including WoodMart/Elementor content.</small></label>
                    <label><input type="checkbox" name="ps_auth[wp_login]" value="1" <?php checked($s['wp_login'],1); ?>><strong>Use custom links for WordPress login</strong><small>Existing wp-login.php remains available for emergency access and integrations.</small></label></div>
                    <label><input type="checkbox" name="ps_auth[account_dashboard]" value="1" <?php checked(!empty($s['account_dashboard']),1); ?>><strong>Premium logged-in My Account Dashboard</strong><small>Replace the default WoodMart/WooCommerce account dashboard while preserving all WooCommerce endpoints and extensions. Optional, restores on OFF.</small></label>
                    <input type="hidden" name="ps_auth[native_wc_form]" value="0" />
                    <p class="description">Registration follows WooCommerce → Accounts &amp; Privacy (or WordPress registration settings). Existing CAPTCHA/2FA integrations should be tested on staging. Account access is never enabled by bypassing these permissions.</p>
                </section>
                <section class="psx-admin-panel"><h3>02 · Choose one of 6 complete premium templates</h3>
                    <div class="psx-admin-gallery">
                    <?php foreach($styles as $slug=>$meta): ?><button type="button" class="psx-picker psx-<?php echo esc_attr($slug); ?> <?php echo $slug===$selected?'selected':''; ?>" data-psx-preset="<?php echo esc_attr($slug); ?>" aria-pressed="<?php echo $slug===$selected?'true':'false'; ?>">
                        <span class="psx-picker-picture"><i class="psx-picker-art">✳</i><i class="psx-picker-form"><b>Sign in</b><em></em><em></em><u>Continue →</u></i></span><span class="psx-picker-title"><?php echo esc_html($meta[0]); ?></span><small>Custom Login / Register / Reset</small></button><?php endforeach; ?>
                    </div>
                    <input type="hidden" id="psx-preset" name="ps_auth[preset]" value="<?php echo esc_attr($selected); ?>">
                </section>
                <section class="psx-admin-panel"><h3>03 · Branding &amp; customization</h3>
                    <div class="psx-admin-fields"><label>Accent color<input id="psx-accent" type="color" name="ps_auth[accent]" value="<?php echo esc_attr($s['accent']); ?>"></label><label>Background<input id="psx-bg" type="color" name="ps_auth[background]" value="<?php echo esc_attr($s['background']); ?>"></label>
                    <label>Welcome headline<input id="psx-headline" type="text" name="ps_auth[headline]" maxlength="75" value="<?php echo esc_attr($s['headline']); ?>"></label><label>Supporting text<input id="psx-subtitle" type="text" name="ps_auth[subtitle]" maxlength="160" value="<?php echo esc_attr($s['subtitle']); ?>"></label></div>
                    <input type="hidden" name="ps_auth[logo_id]" value="<?php echo absint($s['logo_id']); ?>" id="psx-logo-id"><p><button type="button" id="psx-logo-pick" class="button">Choose Brand Logo</button> <button type="button" id="psx-logo-clear" class="button">Use Site Logo</button> <span id="psx-logo-state"><?php echo $logo?'Logo ready':'Text branding'; ?></span></p>
                </section>
                <section class="psx-admin-panel"><h3>04 · Login &amp; registration form content</h3>
                    <p class="description">Customize text and buttons. Username, password and security checks cannot be removed.</p>
                    <div class="psx-admin-fields">
                    <?php foreach(array('login_title'=>'Login heading','login_intro'=>'Login intro','login_user_label'=>'Username/email label','login_password_label'=>'Password label','login_button'=>'Sign-in button','register_title'=>'Registration heading','register_intro'=>'Registration intro','register_button'=>'Registration button') as $k=>$label): ?>
                    <label><?php echo esc_html($label); ?><input type="text" name="ps_auth[<?php echo esc_attr($k); ?>]" maxlength="160" value="<?php echo esc_attr($s[$k]); ?>"></label>
                    <?php endforeach; ?>
                    </div>
                </section>
                <section class="psx-admin-panel" id="psx-fields-section"><h3>05 · Custom registration fields</h3>
                    <p class="description">Create up to 12 extra fields. Drag with arrows to reorder. Values are stored as protected user metadata with the prefix psx_field_. Never collect passwords, card details, or other highly sensitive data here.</p>
                    <input type="hidden" id="psx-fields-data" name="ps_auth[register_fields_json]" value="<?php echo esc_attr(wp_json_encode($s['register_fields'])); ?>">
                    <div id="psx-field-editor" class="psx-fields-editor" aria-live="polite"></div>
                    <button type="button" id="psx-add-field" class="button button-secondary">+ Add registration field</button>
                    <p class="description">Field keys cannot overlap built-in WordPress account keys. Required fields are validated before registration and stored only after successful account creation.</p>
                </section>
                <section class="psx-admin-panel"><h3>06 · Premium Account Dashboard</h3>
                    <div class="psx-dashboard-peek"><div><span>YOUR ACCOUNT SPACE</span><h3>Welcome back, Alex</h3><p>Your purchases, addresses and profile in one place.</p></div><div class="psx-dash-mini-grid"><span>◫ &nbsp; Orders</span><span>⬇ &nbsp; Downloads</span><span>⌂ &nbsp; Addresses</span><span>◇ &nbsp; Account details</span></div></div>
                    <p class="description">Your logged-in users will see a responsive dashboard in the selected template palette. Orders, Downloads, Addresses, Account Details and extra WooCommerce endpoints remain functional. Enable it in Publishing &amp; integration above.</p>
                    <?php $dashboard_active = !empty($s['account_dashboard']) && !empty($s['enabled']) && !empty($s['independent_mode']) && !empty($s['woocommerce']); ?>
                    <p class="psx-dashboard-status"><strong>Saved Dashboard Status:</strong> <?php echo esc_html($dashboard_active ? 'Enabled — plugin-owned My Account template' : 'Disabled — theme/WooCommerce default'); ?>
                    <?php if (function_exists('wc_get_page_permalink')): $account_url = wc_get_page_permalink('myaccount'); if ($account_url && $account_url !== '#'): ?>
                    <a href="<?php echo esc_url($account_url); ?>" target="_blank" rel="noopener noreferrer">Open My Account to test ↗</a>
                    <?php endif; endif; ?></p>
                </section>
                <section class="psx-admin-panel"><h3>07 · Live design preview</h3>
                    <div class="psx-admin-controls"><div role="group" aria-label="Preview devices"><button type="button" data-psx-device="desktop" class="selected">Desktop</button><button type="button" data-psx-device="tablet">Tablet</button><button type="button" data-psx-device="mobile">Mobile</button></div><div role="group" aria-label="Preview forms"><button type="button" class="selected" data-psx-view="login">Login</button><button type="button" data-psx-view="register">Register</button><button type="button" data-psx-view="reset">Reset</button></div></div>
                    <div class="psx-admin-stage" id="psx-admin-stage"><div class="psx-admin-frame" id="psx-admin-frame">
                        <div class="psx-account psx-<?php echo esc_attr($selected); ?>" id="psx-preview" style="--psx-accent:<?php echo esc_attr($s['accent']); ?>;--psx-canvas:<?php echo esc_attr($s['background']); ?>;--psx-panel:<?php echo esc_attr($styles[$selected][4]); ?>;--psx-ink:<?php echo esc_attr($styles[$selected][5]); ?>;"><div class="psx-layout">
                          <aside class="psx-art"><div class="psx-art-inner"><div class="psx-art-top">✳ &nbsp; ACCOUNT STUDIO</div><div class="psx-art-content"><span class="psx-eyebrow">A BETTER WAY TO CONNECT</span><h2 id="psx-preview-headline"><?php echo esc_html($s['headline']); ?></h2><p id="psx-preview-subtitle"><?php echo esc_html($s['subtitle']); ?></p></div><div class="psx-art-bottom">Your account. Your experience. ↗</div></div></aside>
                          <div class="psx-form-area"><div class="psx-form-inner"><div class="psx-brand">✳ &nbsp; <?php echo esc_html(get_bloginfo('name')); ?></div><nav class="psx-switch"><span class="psx-current">Sign in</span><span>Create account</span></nav><span class="psx-kicker">WELCOME BACK</span><h1 id="psx-preview-title">Sign in to your account</h1><p class="psx-intro">Beautiful and secure account access</p><div class="psx-form"><label class="psx-field"><span>Email address</span><input readonly value="hello@example.com"></label><label class="psx-field psx-preview-password"><span>Password</span><input readonly type="password" value="password123"></label><div id="psx-extra-preview" hidden></div><button type="button" class="psx-submit" id="psx-preview-cta">Sign in securely →</button></div></div></div>
                        </div></div></div></div><p class="description">This preview shows the plugin's actual layout system; the live form additionally includes WordPress/WooCommerce security hooks and can show integration-specific fields.</p>
                </section>
                <div class="psx-admin-save"><span>Your other Preloader, Product Layout and Bottom Menu settings remain unchanged.</span><button class="button button-primary" type="submit">Save &amp; Publish Login Studio</button></div>
            </form>
            <form class="psx-admin-restore" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="ps_studio_auth_restore"><?php wp_nonce_field('ps_studio_auth_restore','ps_auth_restore_nonce'); ?><button class="button" type="submit">Restore Theme / WooCommerce Default</button><p>Reverts instantly without deleting your saved branding.</p></form>
            <div class="psx-admin-panel"><h3>Test the live experience</h3><p><a href="<?php echo esc_url(self::url('login')?:wp_login_url()); ?>" class="button" target="_blank" rel="noopener noreferrer">Open My Account Login ↗</a> <a href="<?php echo esc_url(self::url('register')?:wp_registration_url()); ?>" class="button" target="_blank" rel="noopener noreferrer">Open Registration ↗</a></p>
            <p>WooCommerce uses its own secure account processing, but its default form HTML is not displayed. Custom theme popups outside the My Account page are independent and should be configured to link to this page.</p><p>For a dedicated WordPress page, insert <code>[preloader_studio_auth]</code> and use that page as the destination for header login links.</p></div>
        </div>
        <?php
    }
}
