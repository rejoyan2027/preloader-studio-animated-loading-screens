<?php
/** Admin settings panel and live 36-animation picker. */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class PRELSTAN_Studio_Admin {
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
        add_action( 'admin_post_ps_studio_save', array( __CLASS__, 'save' ) );
        add_action( 'admin_post_ps_studio_nav_save', array( __CLASS__, 'save_navigation' ) );
        add_action( 'admin_post_ps_studio_export', array( __CLASS__, 'export_settings' ) );
        add_action( 'admin_post_ps_studio_import', array( __CLASS__, 'import_settings' ) );
        add_action( 'wp_ajax_ps_studio_design', array( __CLASS__, 'manage_design' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( PRELSTAN_STUDIO_FILE ), array( __CLASS__, 'plugin_links' ) );
    }

    public static function plugin_links( $links ) {
        array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=preloader-studio' ) ) . '">' . esc_html__( 'Settings', 'preloader-studio-animated-loading-screens' ) . '</a>' );
        return $links;
    }

    private static function pages() {
        return array(
            'preloader-studio'            => array( 'Dashboard', 'dashicons-dashboard' ),
            'preloader-studio-animations' => array( 'Animations', 'dashicons-art' ),
            'preloader-studio-builder'    => array( 'Visual Builder', 'dashicons-admin-customizer' ),
            'preloader-studio-branding'   => array( 'Branding & Logo', 'dashicons-format-image' ),
            'preloader-studio-style'      => array( 'Style & Timing', 'dashicons-admin-appearance' ),
            'preloader-studio-designs'    => array( 'My Designs', 'dashicons-saved' ),
            'preloader-studio-rules'      => array( 'Rules & Performance', 'dashicons-admin-generic' ),
            'preloader-studio-navigation' => array( 'Bottom Navigation', 'dashicons-smartphone' ),
            'preloader-studio-products'   => array( 'Product Layouts', 'dashicons-cart' ),
            'preloader-studio-auth'       => array( 'Login & Register', 'dashicons-lock' ),
            'preloader-studio-tools'      => array( 'Import / Export', 'dashicons-database' ),
        );
    }

    private static function page_url( $slug ) {
        return admin_url( 'admin.php?page=' . $slug );
    }

    public static function add_menu() {
        add_menu_page( __( 'Preloader Studio', 'preloader-studio-animated-loading-screens' ), __( 'Preloader Studio', 'preloader-studio-animated-loading-screens' ),
            'manage_options', 'preloader-studio', array( __CLASS__, 'page' ), 'dashicons-image-rotate', 65 );
        foreach ( self::pages() as $slug => $item ) {
            add_submenu_page( 'preloader-studio', $item[0] . ' - Preloader Studio',
                $item[0], 'manage_options', $slug, array( __CLASS__, 'page' ) );
        }
    }

    private static function section_for_page( $page ) {
        $mapping = array(
            'preloader-studio-animations' => 'animations', 'preloader-studio-builder' => 'builder',
            'preloader-studio-branding' => 'branding', 'preloader-studio-style' => 'style',
            'preloader-studio-designs' => 'designs', 'preloader-studio-rules' => 'rules',
            'preloader-studio-navigation' => 'navigation', 'preloader-studio-products' => 'products', 'preloader-studio-auth' => 'auth', 'preloader-studio-tools' => 'tools',
        );
        return isset( $mapping[ $page ] ) ? $mapping[ $page ] : 'overview';
    }

    private static function allowed_fields( $section ) {
        $fields = array(
            'animations' => array( 'preset', 'source' ),
            'builder' => array( 'layer_order' ),
            'branding' => array( 'source', 'image_effect', 'logo_id', 'custom_image_id', 'loading_text', 'show_text', 'show_percent' ),
            'style' => array( 'color', 'background', 'text_color', 'background_style', 'gradient_color', 'glow',
                'exit_effect', 'font_size', 'font_weight', 'letter_spacing', 'size', 'speed', 'min_ms', 'max_ms', 'fade_ms', 'mobile_custom', 'tablet_custom', 'mobile_size', 'tablet_size', 'mobile_speed', 'tablet_speed' ),
            'rules' => array( 'show_on', 'devices', 'exclude_ids', 'compat_mode', 'theme_selectors',
                'performance_mode', 'fast_skip_ms', 'skip_wc_cart', 'skip_wc_checkout', 'skip_wc_account', 'skip_wc_shop', 'skip_wc_product' ),
        );
        return isset( $fields[ $section ] ) ? array_merge( array( 'enabled' ), $fields[ $section ] ) : array();
    }

    public static function enqueue( $hook ) {
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only admin menu identifier, not a submitted change.
        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Only reads a page slug for selecting assets; no action or mutation.
        if ( ! isset( self::pages()[ $page ] ) ) { return; }
        $section = self::section_for_page( $page );
        wp_enqueue_style( 'preloader-studio-front', PRELSTAN_STUDIO_URL . 'assets/css/frontend.css', array(), PRELSTAN_STUDIO_VERSION );
        wp_enqueue_style( 'preloader-studio-admin', PRELSTAN_STUDIO_URL . 'assets/css/admin.css', array( 'preloader-studio-front' ), PRELSTAN_STUDIO_VERSION );
        wp_enqueue_style( 'preloader-studio-pages', PRELSTAN_STUDIO_URL . 'assets/css/admin-pages.css', array( 'preloader-studio-admin' ), PRELSTAN_STUDIO_VERSION );
        wp_enqueue_script( 'ps-studio-device-preview', PRELSTAN_STUDIO_URL . 'assets/js/device-preview.js', array(), PRELSTAN_STUDIO_VERSION, true );
        if ( 'products' === $section ) { PRELSTAN_Studio_Products::enqueue(); }
        if ( 'auth' === $section ) { PRELSTAN_Studio_Auth::admin_enqueue(); }
        if ( 'navigation' === $section ) {
            wp_enqueue_style( 'ps-studio-nav-preview', PRELSTAN_STUDIO_URL . 'assets/css/navigation.css', array(), PRELSTAN_STUDIO_VERSION );
            wp_enqueue_style( 'ps-studio-nav-admin', PRELSTAN_STUDIO_URL . 'assets/css/navigation-admin.css', array( 'preloader-studio-admin', 'ps-studio-nav-preview' ), PRELSTAN_STUDIO_VERSION );
            wp_enqueue_script( 'ps-studio-nav-admin', PRELSTAN_STUDIO_URL . 'assets/js/navigation-admin.js', array( 'ps-studio-device-preview' ), PRELSTAN_STUDIO_VERSION, true );
            $posts = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 250, 'orderby' => 'title', 'order' => 'ASC', 'no_found_rows' => true ) );
            $choices = array();
            foreach ( $posts as $item ) { $choices[] = array( 'id' => (int) $item->ID, 'title' => $item->post_title ?: '(Untitled)' ); }
            wp_localize_script( 'ps-studio-nav-admin', 'prelstan_navigation_data', array(
                'settings' => PRELSTAN_Studio_Navigation::settings(), 'styles' => PRELSTAN_Studio_Navigation::styles(),
                'icons' => PRELSTAN_Studio_Navigation::all_icons(), 'pages' => $choices, 'maxItems' => 6,
                'confirmRemove' => __( 'Remove this menu item?', 'preloader-studio-animated-loading-screens' ),
                'siteUrl' => home_url('/'),
            ) );
        }
        if ( in_array( $section, array( 'animations', 'builder', 'branding', 'style', 'rules', 'designs' ), true ) ) {
            if ( 'branding' === $section || 'designs' === $section ) { wp_enqueue_media(); }
            wp_enqueue_script( 'preloader-studio-admin', PRELSTAN_STUDIO_URL . 'assets/js/admin.js', array( 'ps-studio-device-preview' ), PRELSTAN_STUDIO_VERSION, true );
            $settings = PRELSTAN_Studio::settings();
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only saved-design preview, no mutation.
            if ( 'builder' === $section && isset( $_GET['load_design'] ) && is_string( $_GET['load_design'] ) ) {
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Saved design preview is read-only and does not publish.
                $requested = sanitize_text_field( wp_unslash( $_GET['load_design'] ) );
                foreach ( self::saved_designs() as $design ) {
                    if ( isset( $design['id'], $design['settings'] ) && is_array( $design['settings'] ) && hash_equals( (string) $design['id'], $requested ) ) {
                        $settings = PRELSTAN_Studio::sanitize( array_merge( $settings, $design['settings'] ) );
                        break;
                    }
                }
            }
            wp_localize_script( 'preloader-studio-admin', 'prelstan_admin_data', array(
                'mediaTitle' => __( 'Choose an image', 'preloader-studio-animated-loading-screens' ),
                'mediaButton' => __( 'Use this image', 'preloader-studio-animated-loading-screens' ),
                'siteLogoUrl' => (string) wp_get_attachment_image_url( absint( get_theme_mod( 'custom_logo', 0 ) ), 'full' ),
                'logoUrl' => (string) wp_get_attachment_image_url( absint( $settings['logo_id'] ), 'full' ),
                'customImageUrl' => (string) wp_get_attachment_url( absint( $settings['custom_image_id'] ) ),
                'presetName' => PRELSTAN_Studio::presets()[ $settings['preset'] ][0],
                'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'designNonce' => wp_create_nonce( 'ps_studio_design' ),
                'designs' => self::saved_designs(),
                'builderUrl' => self::page_url( 'preloader-studio-builder' ),
                'designSaved' => __( 'Design saved in your library.', 'preloader-studio-animated-loading-screens' ),
                'designDeleted' => __( 'Saved design removed.', 'preloader-studio-animated-loading-screens' ),
                'designError' => __( 'Could not complete the action.', 'preloader-studio-animated-loading-screens' ),
            ) );
        }
        // Late enqueue is intentional: device preview must override navigation-admin CSS.
        if ( 'navigation' === $section || in_array( $section, array( 'animations', 'builder', 'branding', 'style', 'rules', 'designs' ), true ) ) {
            $device_deps = 'navigation' === $section ? array( 'ps-studio-nav-admin' ) : array( 'preloader-studio-pages' );
            wp_enqueue_style( 'ps-studio-device-preview', PRELSTAN_STUDIO_URL . 'assets/css/device-preview.css', $device_deps, PRELSTAN_STUDIO_VERSION );
        }
    }

    public static function save() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to manage these settings.', 'preloader-studio-animated-loading-screens' ), 403 );
        }
        check_admin_referer( 'ps_studio_save', 'ps_studio_nonce' );
        $section = isset( $_POST['ps_section'] ) && is_string( $_POST['ps_section'] ) ? sanitize_key( wp_unslash( $_POST['ps_section'] ) ) : '';
        $allowed = self::allowed_fields( $section );
        if ( ! $allowed ) { wp_die( esc_html__( 'Invalid settings section.', 'preloader-studio-animated-loading-screens' ), 400 ); }
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- check_admin_referer verified in this method; sanitized via field allowlist.
        $submitted = isset( $_POST['ps_settings'] ) && is_array( $_POST['ps_settings'] ) ? wp_unslash( $_POST['ps_settings'] ) : array();
        $merged = PRELSTAN_Studio::settings();
        // Optional saved-design import is allowed only when the user explicitly loads a real library ID.
        if ( 'builder' === $section && isset( $_POST['ps_apply_design'] ) && is_string( $_POST['ps_apply_design'] ) ) {
            $design_id = sanitize_text_field( wp_unslash( $_POST['ps_apply_design'] ) );
            foreach ( self::saved_designs() as $design ) {
                if ( isset( $design['id'], $design['settings'] ) && is_array( $design['settings'] ) && hash_equals( (string) $design['id'], $design_id ) ) {
                    $merged = array_merge( $merged, $design['settings'] );
                    break;
                }
            }
        }
        // Update only the active page's fields. A blank checkbox is an explicit false,
        // while other pages' existing options are never silently reset.
        $toggles = array( 'enabled', 'show_text', 'show_percent', 'skip_wc_cart', 'skip_wc_checkout',
            'skip_wc_account', 'skip_wc_shop', 'skip_wc_product', 'mobile_custom', 'tablet_custom' );
        foreach ( $allowed as $key ) {
            if ( in_array( $key, $toggles, true ) ) {
                $merged[ $key ] = ! empty( $submitted[ $key ] ) ? 1 : 0;
            } elseif ( isset( $submitted[ $key ] ) && is_scalar( $submitted[ $key ] ) ) {
                $merged[ $key ] = $submitted[ $key ];
            }
        }
        update_option( PRELSTAN_Studio::OPTION, PRELSTAN_Studio::sanitize( $merged ), false );
        wp_safe_redirect( add_query_arg( array( 'page' => 'preloader-studio-' . $section, 'updated' => '1' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public static function save_navigation() {
        if (!current_user_can('manage_options')) { wp_die(esc_html__('Permission denied.','preloader-studio-animated-loading-screens'),403); }
        check_admin_referer('ps_studio_nav_save','ps_studio_nav_nonce');
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- check_admin_referer verified; PRELSTAN_Studio_Navigation::sanitize validates each key.
        $raw = isset($_POST['ps_nav']) && is_array($_POST['ps_nav']) ? wp_unslash($_POST['ps_nav']) : array();
        $settings = PRELSTAN_Studio_Navigation::sanitize($raw);
        $status = 'nav';
        if ( ! empty($settings['enabled']) && count(PRELSTAN_Studio_Navigation::resolved_items($settings)) < 2 ) {
            // Never silently show an empty menu: persist the draft, but leave it disabled.
            $settings['enabled'] = 0;
            $status = 'nav_links_needed';
        }
        update_option(PRELSTAN_Studio_Navigation::OPTION,$settings,false);
        wp_safe_redirect(add_query_arg(array('page'=>'preloader-studio-navigation','updated'=>$status),admin_url('admin.php')));
        exit;
    }

    public static function export_settings() {
        if (!current_user_can('manage_options')) { wp_die(esc_html__('Permission denied.','preloader-studio-animated-loading-screens'),403); }
        check_admin_referer('ps_studio_export');
        $payload = array(
            'format'=>'preloader-studio', 'version'=>PRELSTAN_STUDIO_VERSION,
            'preloader'=>PRELSTAN_Studio::settings(),
            'navigation'=>PRELSTAN_Studio_Navigation::settings(),
            'products'=>PRELSTAN_Studio_Products::settings(),
            'auth'=>PRELSTAN_Studio_Auth::settings(),
            'designs'=>self::saved_designs(),
        );
        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="preloader-studio-settings.json"');
        header('X-Content-Type-Options: nosniff');
        echo wp_json_encode($payload,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function import_settings() {
        if (!current_user_can('manage_options')) { wp_die(esc_html__('Permission denied.','preloader-studio-animated-loading-screens'),403); }
        check_admin_referer('ps_studio_import','ps_studio_import_nonce');
        $error = 'bad_import';
        if (isset($_FILES['ps_import_file']) && is_array($_FILES['ps_import_file'])) {
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Upload is limited to 100KB, checked for UPLOAD_ERR_OK and is_uploaded_file.
            $f = $_FILES['ps_import_file'];
            if (isset($f['error'],$f['size'],$f['tmp_name']) && UPLOAD_ERR_OK === (int)$f['error'] && $f['size'] > 0 && $f['size'] <= 102400 && is_uploaded_file($f['tmp_name'])) {
                $data = json_decode((string)file_get_contents($f['tmp_name']),true);
                if (is_array($data) && isset($data['format']) && 'preloader-studio' === $data['format'] && isset($data['preloader'],$data['navigation']) && is_array($data['preloader']) && is_array($data['navigation'])) {
                    // Reject oversized libraries and re-sanitize imported fields. Never execute JSON content.
                    $library = array();
                    foreach (array_slice(isset($data['designs']) && is_array($data['designs']) ? $data['designs'] : array(),0,20) as $design) {
                        if (!is_array($design) || !isset($design['settings']) || !is_array($design['settings'])) { continue; }
                        $name = isset($design['name']) && is_scalar($design['name']) ? sanitize_text_field($design['name']) : 'Imported';
                        $library[] = array('id'=>wp_generate_uuid4(),'name'=>function_exists('mb_substr')?mb_substr($name,0,55):substr($name,0,55),'settings'=>PRELSTAN_Studio::sanitize($design['settings']));
                    }
                    update_option(PRELSTAN_Studio::OPTION,PRELSTAN_Studio::sanitize($data['preloader']),false);
                    update_option(PRELSTAN_Studio_Navigation::OPTION,PRELSTAN_Studio_Navigation::sanitize($data['navigation']),false);
                    update_option(PRELSTAN_Studio::DESIGNS_OPTION,$library,false);
                    if ( isset($data['products']) && is_array($data['products']) ) { update_option(PRELSTAN_Studio_Products::OPTION,PRELSTAN_Studio_Products::sanitize($data['products']),false); }
                    if ( isset($data['auth']) && is_array($data['auth']) ) { update_option(PRELSTAN_Studio_Auth::OPTION,PRELSTAN_Studio_Auth::sanitize($data['auth']),false); }
                    $error = 'imported';
                }
            }
        }
        wp_safe_redirect(add_query_arg(array('page'=>'preloader-studio-tools','updated'=>$error),admin_url('admin.php')));
        exit;
    }

    public static function saved_designs() {
        $designs = get_option( PRELSTAN_Studio::DESIGNS_OPTION, array() );
        return is_array( $designs ) ? array_slice( array_values( $designs ), 0, 20 ) : array();
    }

    /** AJAX save/delete designs: all operations require a nonce and administrator privileges. */
    public static function manage_design() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'preloader-studio-animated-loading-screens' ) ), 403 );
        }
        check_ajax_referer( 'ps_studio_design', 'nonce' );
        $operation = isset( $_POST['operation'] ) && is_string( $_POST['operation'] ) ? sanitize_key( wp_unslash( $_POST['operation'] ) ) : '';
        $items = self::saved_designs();
        if ( 'save' === $operation ) {
            $raw_name = isset( $_POST['design_name'] ) && is_string( $_POST['design_name'] ) ? sanitize_text_field( wp_unslash( $_POST['design_name'] ) ) : '';
            $name = function_exists( 'mb_substr' ) ? mb_substr( $raw_name, 0, 55 ) : substr( $raw_name, 0, 55 );
            if ( '' === trim( $name ) ) {
                wp_send_json_error( array( 'message' => __( 'Enter a name for your design.', 'preloader-studio-animated-loading-screens' ) ), 400 );
            }
            if ( count( $items ) >= 20 ) {
                wp_send_json_error( array( 'message' => __( 'The library holds up to 20 designs. Delete an older design first.', 'preloader-studio-animated-loading-screens' ) ), 400 );
            }
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- check_ajax_referer verified in this method; sanitized by PRELSTAN_Studio::sanitize.
            $raw_settings = isset( $_POST['ps_settings'] ) && is_array( $_POST['ps_settings'] ) ? wp_unslash( $_POST['ps_settings'] ) : array();
            $items[] = array(
                'id' => wp_generate_uuid4(),
                'name' => $name,
                'settings' => PRELSTAN_Studio::sanitize( $raw_settings ),
            );
        } elseif ( 'delete' === $operation ) {
            $id = isset( $_POST['design_id'] ) && is_string( $_POST['design_id'] ) ? sanitize_text_field( wp_unslash( $_POST['design_id'] ) ) : '';
            $found = false;
            foreach ( $items as $index => $item ) {
                if ( isset( $item['id'] ) && hash_equals( (string) $item['id'], $id ) ) {
                    unset( $items[ $index ] );
                    $found = true;
                    break;
                }
            }
            if ( ! $found ) {
                wp_send_json_error( array( 'message' => __( 'Design not found.', 'preloader-studio-animated-loading-screens' ) ), 404 );
            }
            $items = array_values( $items );
        } else {
            wp_send_json_error( array( 'message' => __( 'Invalid action.', 'preloader-studio-animated-loading-screens' ) ), 400 );
        }
        update_option( PRELSTAN_Studio::DESIGNS_OPTION, $items, false );
        wp_send_json_success( array( 'designs' => $items ) );
    }

    private static function field( $key, $label, $s, $type = 'text', $min = null, $max = null ) {
        echo '<label class="ps-field" for="ps-' . esc_attr( $key ) . '"><span>' . esc_html( $label ) . '</span>';
        echo '<input id="ps-' . esc_attr( $key ) . '" name="ps_settings[' . esc_attr( $key ) . ']" type="' . esc_attr( $type ) . '" value="' . esc_attr( (string) $s[ $key ] ) . '"';
        if ( null !== $min ) {
            echo ' min="' . absint( $min ) . '"';
        }
        if ( null !== $max ) {
            echo ' max="' . absint( $max ) . '"';
        }
        echo ' /></label>';
    }

    private static function toggle( $key, $label, $s, $help = '' ) {
        echo '<label class="ps-toggle-row"><span><b>' . esc_html( $label ) . '</b>';
        if ( $help ) {
            echo '<small>' . esc_html( $help ) . '</small>';
        }
        echo '</span><span class="ps-switch"><input type="checkbox" name="ps_settings[' . esc_attr( $key ) . ']" value="1" ' . checked( $s[ $key ], 1, false ) . ' /><span class="ps-switch-slider"></span></span></label>';
    }

    private static function select( $key, $label, $options, $s ) {
        echo '<label class="ps-field" for="ps-' . esc_attr( $key ) . '"><span>' . esc_html( $label ) . '</span><select id="ps-' . esc_attr( $key ) . '" name="ps_settings[' . esc_attr( $key ) . ']">';
        foreach ( $options as $value => $title ) {
            echo '<option value="' . esc_attr( $value ) . '" ' . selected( $s[ $key ], $value, false ) . '>' . esc_html( $title ) . '</option>';
        }
        echo '</select></label>';
    }

    private static function image_picker( $key, $label, $s ) {
        $id = absint( $s[ $key ] );
        $url = $id ? wp_get_attachment_image_url( $id, 'thumbnail' ) : '';
        $full_url = $id ? wp_get_attachment_url( $id ) : '';
        echo '<div class="ps-upload" data-upload="' . esc_attr( $key ) . '"><span class="ps-upload-label">' . esc_html( $label ) . '</span>';
        echo '<input type="hidden" name="ps_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $id ) . '" />';
        echo '<div class="ps-upload-preview">';
        if ( $url ) {
            echo '<img src="' . esc_url( $url ) . '" data-full-src="' . esc_url( $full_url ) . '" alt="" />';
        } else {
            echo '<span class="dashicons dashicons-format-image"></span>';
        }
        echo '</div><div class="ps-upload-actions"><button type="button" class="button ps-pick-image">' . esc_html__( 'Choose image', 'preloader-studio-animated-loading-screens' ) . '</button> '; 
        echo '<button type="button" class="button-link ps-remove-image">' . esc_html__( 'Remove', 'preloader-studio-animated-loading-screens' ) . '</button></div></div>';
    }


    private static function navigation_panel() {
        $s = PRELSTAN_Studio_Navigation::settings();
        $theme = wp_get_theme();
        $audit = PRELSTAN_Studio_Navigation::audit( $s );
        ?>
        <section class="ps-panel ps-nav-panel" id="ps-navigation-panel">
          <div class="ps-panel-head"><div><h2><span class="dashicons dashicons-smartphone" aria-hidden="true"></span> <?php esc_html_e('Mobile & Tablet Bottom Menu Studio','preloader-studio-animated-loading-screens'); ?></h2>
            <p><?php esc_html_e('24 unique design systems, 200+ Font Awesome Free vector icons, adjustable sizes and a phone preview. This feature is opt-in for existing sites.','preloader-studio-animated-loading-screens'); ?></p></div><span class="ps-count">24 styles · 200+ icons</span></div>
          <div class="ps-quality-audit" aria-label="<?php esc_attr_e( 'Saved navigation health', 'preloader-studio-animated-loading-screens' ); ?>">
            <div class="ps-quality-audit-head"><span class="dashicons dashicons-shield" aria-hidden="true"></span><div><strong><?php esc_html_e( 'Saved menu health', 'preloader-studio-animated-loading-screens' ); ?></strong><small><?php esc_html_e( 'Checks the saved links. Unsaved edits are validated in the editor below.', 'preloader-studio-animated-loading-screens' ); ?></small></div></div>
            <div class="ps-quality-audit-stats">
              <div><b><?php echo esc_html( (string) $audit['valid'] ); ?></b><span><?php esc_html_e( 'Working destinations', 'preloader-studio-animated-loading-screens' ); ?></span></div>
              <div><b><?php echo esc_html( (string) $audit['incomplete'] ); ?></b><span><?php esc_html_e( 'Incomplete items', 'preloader-studio-animated-loading-screens' ); ?></span></div>
              <div><b><?php echo esc_html( (string) count( $audit['duplicate_groups'] ) ); ?></b><span><?php esc_html_e( 'Shared destinations', 'preloader-studio-animated-loading-screens' ); ?></span></div>
            </div>
            <?php if ( $audit['incomplete'] ) : ?><p class="ps-quality-note is-warning"><?php esc_html_e( 'Some saved menu items have no published page or valid URL and are omitted from the live menu.', 'preloader-studio-animated-loading-screens' ); ?></p><?php endif; ?>
            <?php if ( $audit['duplicate_groups'] ) : ?><p class="ps-quality-note is-info"><?php esc_html_e( 'Multiple buttons point to the same destination. This is allowed; only one is highlighted at a time. Assign different pages for distinct destinations.', 'preloader-studio-animated-loading-screens' ); ?></p><?php endif; ?>
            <?php if ( ! $audit['incomplete'] && ! $audit['duplicate_groups'] && $audit['ready'] ) : ?><p class="ps-quality-note is-good"><?php esc_html_e( 'Saved links look ready. Always test the real mobile website after clearing any page cache.', 'preloader-studio-animated-loading-screens' ); ?></p><?php endif; ?>
          </div>
          <?php if ( ! empty($s['enabled']) && count(PRELSTAN_Studio_Navigation::resolved_items($s)) < 2 ) : ?>
            <div class="notice notice-warning inline psn-site-warning"><p><strong>Bottom menu is not visible:</strong> fewer than two menu items have valid destinations. Use Quick Setup or select published pages and save again.</p></div>
          <?php endif; ?>
          <?php
          // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin success/error notice, no changes or processing.
          if ( isset( $_GET['updated'] ) && 'nav_links_needed' === sanitize_key( wp_unslash( $_GET['updated'] ) ) ) : ?>
            <div class="notice notice-error inline psn-site-warning"><p><strong>Menu saved as disabled:</strong> set at least two working links, enable the menu, and save. A draft with broken links cannot be published.</p></div>
          <?php endif; ?>
          <form id="ps-nav-settings-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="ps_studio_nav_save" /><?php wp_nonce_field('ps_studio_nav_save','ps_studio_nav_nonce'); ?>
            <div class="psn-admin-grid"><div class="psn-admin-editor">
              <div class="psn-setting-block"><h3><?php esc_html_e('01 · Menu Display','preloader-studio-animated-loading-screens'); ?></h3>
                <?php self::toggle_nav('enabled',__('Enable Bottom Navigation','preloader-studio-animated-loading-screens'),$s,__('Requires 2 valid destinations. New websites can use Home + Search immediately.','preloader-studio-animated-loading-screens')); ?>
                <p class="psn-explainer">This menu appears only on real mobile/tablet viewports (up to 1024px). In Chrome, turn off <strong>Desktop site</strong> before testing.</p>
                <div class="ps-fields-grid">
                  <?php self::select_nav('devices',__('Show on devices','preloader-studio-animated-loading-screens'),array('mobile'=>'Mobile only (up to 767px)','tablet'=>'Tablet only (768–1024px)','both'=>'Mobile + Tablet (up to 1024px)'),$s); ?>
                  <?php self::select_nav('style',__('Design style','preloader-studio-animated-loading-screens'),PRELSTAN_Studio_Navigation::styles(),$s); ?>
                </div>
                <?php self::toggle_nav('labels',__('Show labels under icons','preloader-studio-animated-loading-screens'),$s); ?>
              </div>
              <div class="psn-setting-block"><h3><?php esc_html_e('02 · Choose Your Look','preloader-studio-animated-loading-screens'); ?></h3>
                <div id="psn-style-gallery" class="psn-style-gallery" aria-label="<?php esc_attr_e('Choose a bottom menu design','preloader-studio-animated-loading-screens'); ?>"></div>
                <div class="ps-fields-grid">
                  <?php foreach (array('accent'=>'Accent color','background'=>'Background color','text_color'=>'Inactive text color','active_color'=>'Active icon color') as $key=>$label) : ?>
                  <label class="ps-field" for="psn-<?php echo esc_attr($key); ?>"><span><?php echo esc_html($label); ?></span><input type="color" id="psn-<?php echo esc_attr($key); ?>" name="ps_nav[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($s[$key]); ?>" /></label>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="psn-setting-block psn-customizer"><div class="psn-inline-heading"><h3><?php esc_html_e('03 · Size & Spacing Studio','preloader-studio-animated-loading-screens'); ?></h3><button type="button" class="button" id="psn-reset-sizing">Reset sizes</button></div>
                <p class="description">Fine-tune the menu to match your theme. Every adjustment updates the phone/tablet preview immediately.</p>
                <div class="psn-size-grid">
                  <?php
                    $sliders = array(
                      'menu_height'=>array('Menu height','px'), 'menu_width'=>array('Menu width','%'),
                      'icon_size'=>array('Icon size','px'), 'label_size'=>array('Label size','px'),
                      'item_gap'=>array('Between items','px'), 'menu_radius'=>array('Menu corners','px'),
                      'item_radius'=>array('Button corners','px'), 'bottom_offset'=>array('Bottom offset','px'),
                    );
                    foreach ($sliders as $key=>$meta) : $rules=PRELSTAN_Studio_Navigation::size_rules()[$key]; ?>
                    <label class="psn-size-field" for="psn-<?php echo esc_attr($key); ?>">
                      <span class="psn-size-label"><?php echo esc_html($meta[0]); ?><output for="psn-<?php echo esc_attr($key); ?>" id="psn-value-<?php echo esc_attr($key); ?>"><?php echo esc_html($s[$key] . $meta[1]); ?></output></span>
                      <input id="psn-<?php echo esc_attr($key); ?>" data-psn-size="<?php echo esc_attr($meta[1]); ?>" type="range" name="ps_nav[<?php echo esc_attr($key); ?>]" min="<?php echo esc_attr($rules[0]); ?>" max="<?php echo esc_attr($rules[1]); ?>" value="<?php echo esc_attr($s[$key]); ?>" step="1" />
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="psn-setting-block psn-device-profiles">
                <h3>04 · Independent Mobile &amp; Tablet Sizes</h3>
                <p class="description">Leave each profile off to inherit the global Size &amp; Spacing Studio settings. Enable only if you want different sizes on that device. Previews use the actual device profile.</p>
                <?php foreach (array('mobile'=>'Mobile (up to 767px)', 'tablet'=>'Tablet (768–1024px)') as $dev=>$label) : ?>
                  <div class="psn-profile-card" data-psn-profile="<?php echo esc_attr($dev); ?>">
                    <label class="psn-profile-toggle"><input type="checkbox" name="ps_nav[<?php echo esc_attr($dev); ?>_custom]" id="psn-<?php echo esc_attr($dev); ?>-custom" value="1" <?php echo checked(!empty($s[$dev.'_custom']),true,false); ?> /> <strong><?php echo esc_html($label); ?>: separate sizes</strong></label>
                    <div class="psn-size-grid psn-device-size-grid">
                      <?php foreach (PRELSTAN_Studio_Navigation::device_size_keys() as $key) :
                        $meta=$sliders[$key]; $rule=PRELSTAN_Studio_Navigation::size_rules()[$key]; $name=$dev.'_'.$key; ?>
                        <label class="psn-size-field" for="psn-<?php echo esc_attr($name); ?>">
                          <span class="psn-size-label"><?php echo esc_html($meta[0]); ?><output id="psn-value-<?php echo esc_attr($name); ?>"><?php echo esc_html($s[$name].$meta[1]); ?></output></span>
                          <input type="range" id="psn-<?php echo esc_attr($name); ?>" name="ps_nav[<?php echo esc_attr($name); ?>]" data-psn-size="<?php echo esc_attr($meta[1]); ?>" min="<?php echo esc_attr($rule[0]); ?>" max="<?php echo esc_attr($rule[1]); ?>" value="<?php echo esc_attr($s[$name]); ?>" />
                        </label>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="psn-setting-block psn-motion-studio">
                <h3>05 · Animation &amp; Active Indicator</h3>
                <p class="description">Choose a subtle selected-button effect, an active marker, and the animation duration. Reduced-motion preferences are respected.</p>
                <div class="ps-fields-grid">
                  <?php self::select_nav('motion','Active button animation',PRELSTAN_Studio_Navigation::motion_styles(),$s); ?>
                  <?php self::select_nav('indicator','Active indicator',PRELSTAN_Studio_Navigation::indicators(),$s); ?>
                  <?php self::select_nav('scroll_behavior','Navigation while scrolling',array('hide-down'=>'Hide while scrolling down; show when scrolling up','always'=>'Always visible (legacy behavior)'),$s); ?>
                </div>
                <div class="psn-size-grid"><label class="psn-size-field" for="psn-motion-ms"><span class="psn-size-label">Animation duration <output id="psn-value-motion_ms"><?php echo esc_html($s['motion_ms']); ?>ms</output></span><input type="range" id="psn-motion-ms" name="ps_nav[motion_ms]" min="150" max="900" step="10" value="<?php echo esc_attr($s['motion_ms']); ?>" /></label></div>
              </div>
              <div class="psn-setting-block"><div class="psn-inline-heading"><h3><?php esc_html_e('06 · Menu Items & Destinations','preloader-studio-animated-loading-screens'); ?></h3><button type="button" class="button" id="psn-quick-setup">Quick Setup</button><button type="button" class="button" id="psn-add-item">+ <?php esc_html_e('Add item','preloader-studio-animated-loading-screens'); ?></button></div>
                <p class="description"><?php esc_html_e('Up to 6 items. Choose a Font Awesome icon, built-in Search, a WordPress Page or a custom URL. Drag to reorder or use the arrows. Missing page destinations are not shown on your site.','preloader-studio-animated-loading-screens'); ?></p>
                <div id="psn-menu-items" class="psn-menu-items"></div>
                <p class="psn-setup-hint">Quick Setup replaces the current draft with working Home + Search destinations and available published pages. Save to make it live.</p>
              </div>
              <div class="psn-setting-block"><h3><?php esc_html_e('07 · Existing Theme Menu','preloader-studio-animated-loading-screens'); ?></h3>
<?php /* translators: %s: active theme name. */ ?>
                <div class="ps-theme-info"><span class="dashicons dashicons-info-outline"></span><div><strong><?php echo esc_html(sprintf(__('Theme: %s','preloader-studio-animated-loading-screens'),$theme->get('Name'))); ?></strong>
                <p><?php esc_html_e('Preferred method: open Appearance → Customize or the theme options and switch off the theme’s own mobile bottom/sticky navigation if supported. The exact location varies by theme.','preloader-studio-animated-loading-screens'); ?></p>
                <a href="<?php echo esc_url(admin_url('customize.php')); ?>" class="button button-secondary" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open Theme Customizer','preloader-studio-animated-loading-screens'); ?></a></div></div>
                <?php if (PRELSTAN_Studio_Navigation::woodmart_theme()) : ?>
                  <div class="ps-theme-info"><span class="dashicons dashicons-yes-alt"></span><div><strong>WoodMart mobile toolbar detected</strong><p>WoodMart → Theme Settings → General → Mobile bottom navbar → disable “Enable sticky navbar” for the cleanest setup. Or keep it enabled in the theme and let Preloader Studio conceal the WoodMart toolbar while this plugin menu is active (including when the plugin menu hides on scroll). No WoodMart settings are overwritten.</p>
                  <?php self::toggle_nav('hide_woodmart','Hide WoodMart original bottom toolbar while plugin menu is enabled',$s); ?></div></div>
                <?php endif; ?>
                <?php self::select_nav('override',__('Theme bottom menu handling','preloader-studio-animated-loading-screens'),array(
                  'guide'=>'Guide only (safest: leave theme unchanged)',
                  'auto'=>'Auto: hide verified known fixed bottom bars',
                  'manual'=>'Manual: hide matched verified selectors only',
                  'off'=>'Off: never hide theme navigation'),$s); ?>
                <label class="ps-field" for="psn-selectors"><span><?php esc_html_e('Optional CSS selectors (one #id or .class per line)','preloader-studio-animated-loading-screens'); ?></span><textarea id="psn-selectors" name="ps_nav[selectors]" rows="3" placeholder=".your-theme-bottom-bar"><?php echo esc_textarea($s['selectors']); ?></textarea><small><?php esc_html_e('Only fixed/sticky elements at the bottom of the viewport within a safe height range can be hidden. No theme options are overwritten; disable Auto/Manual to restore.','preloader-studio-animated-loading-screens'); ?></small></label>
                <?php self::toggle_nav('skip_checkout',__('Hide plugin menu during checkout','preloader-studio-animated-loading-screens'),$s); ?>
                <?php self::toggle_nav('skip_cart',__('Hide plugin menu on cart','preloader-studio-animated-loading-screens'),$s); ?>
              </div>
              <div class="psn-save-row"><span id="psn-status" role="status" aria-live="polite"><?php esc_html_e('Preview updates while editing; publish with Save Bottom Menu.','preloader-studio-animated-loading-screens'); ?></span><button type="submit" class="button button-primary"><?php esc_html_e('Save Bottom Menu','preloader-studio-animated-loading-screens'); ?></button></div>
            </div>
            <aside class="psn-preview-sidebar"><div class="psn-preview-shell"><div class="psn-preview-heading"><strong><?php esc_html_e('Live Device Preview','preloader-studio-animated-loading-screens'); ?></strong><div><button type="button" class="button is-active" data-psn-preview="mobile" aria-pressed="true">Phone</button><button type="button" class="button" data-psn-preview="tablet" aria-pressed="false">Tablet</button><button type="button" class="button psn-rotate-button" data-ps-rotate="1" title="Switch portrait / landscape" aria-label="Rotate preview">&#8635; Rotate</button></div></div>
              <div id="psn-device-stage" class="ps-device-stage psn-device-stage" data-device="mobile"><button type="button" class="ps-device-close" data-ps-device-close aria-label="Close expanded preview">&times; Close</button><div id="psn-phone" class="psn-phone ps-device-shell ps-device-shell--mobile">
                <div class="psn-phone-top"><span>9:41</span><span aria-hidden="true">●●● ▰</span></div><div class="psn-phone-content"><div class="psn-phone-hero">Your website</div><div class="psn-phone-line"></div><div class="psn-phone-line short"></div><div class="psn-phone-tile"></div></div><div id="psn-live-nav" class="psn-nav psn-style--soft-pill psn-device--both" aria-label="Bottom navigation design preview"></div><div class="ps-device-home-bar" aria-hidden="true"></div></div></div>
              <p class="ps-device-meta" id="psn-device-meta" role="status" aria-live="polite">Phone · 390 × 844 CSS px</p><button type="button" class="button psn-device-expand" id="psn-open-device" aria-expanded="false">Expand Device Preview</button>
              <p id="psn-preview-notice" class="psn-preview-notice" role="status"></p><p class="description"><?php esc_html_e('Preview uses a realistic mobile/tablet viewport and the live menu CSS sizes. The frame is scaled visually; save to publish changes.','preloader-studio-animated-loading-screens'); ?></p>
            </div></aside></div>
          </form>
        </section>
        <?php
    }

    private static function toggle_nav($key,$label,$s,$hint='') {
        echo '<label class="ps-toggle-row"><span><b>'.esc_html($label).'</b>'.($hint?'<small>'.esc_html($hint).'</small>':'').'</span><span class="ps-switch"><input type="checkbox" name="ps_nav['.esc_attr($key).']" value="1" '.checked((int)$s[$key],1,false).' /><span class="ps-switch-slider"></span></span></label>';
    }
    private static function select_nav($key,$label,$choices,$s) {
        echo '<label class="ps-field"><span>'.esc_html($label).'</span><select id="psn-'.esc_attr($key).'" name="ps_nav['.esc_attr($key).']">';
        foreach($choices as $value=>$title) { echo '<option value="'.esc_attr($value).'" '.selected($s[$key],$value,false).'>'.esc_html($title).'</option>'; }
        echo '</select></label>';
    }

    /** Render one focused WordPress admin page per functional area. */
    public static function page() {
        if ( ! current_user_can( 'manage_options' ) ) { return; }
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only WordPress admin page slug.
        $slug = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'preloader-studio';
        if ( ! isset( self::pages()[ $slug ] ) ) { $slug = 'preloader-studio'; }
        $section = self::section_for_page( $slug );
        $s = PRELSTAN_Studio::settings();
        $presets = PRELSTAN_Studio::presets();
        $theme = wp_get_theme();
        $diagnostic_url = add_query_arg( 'ps_studio_diagnostics', '1', home_url( '/' ) );
        $wc_active = class_exists( 'WooCommerce' );
        $loading_name = '';
        $design_id = '';
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only saved-design preview, no mutation.
        if ( 'builder' === $section && isset( $_GET['load_design'] ) && is_string( $_GET['load_design'] ) ) {
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Saved design preview is read-only and does not publish.
            $requested = sanitize_text_field( wp_unslash( $_GET['load_design'] ) );
            foreach ( self::saved_designs() as $design ) {
                if ( isset( $design['id'], $design['name'], $design['settings'] ) && is_array( $design['settings'] ) && hash_equals( (string) $design['id'], $requested ) ) {
                    $s = PRELSTAN_Studio::sanitize( array_merge( $s, $design['settings'] ) );
                    $loading_name = $design['name'];
                    $design_id = $design['id'];
                    break;
                }
            }
        }
        ?>
        <div class="wrap ps-admin ps-admin-page ps-page-<?php echo esc_attr( $section ); ?>">
          <header class="ps-header">
            <div class="ps-brand-icon"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></div>
            <div><h1><?php esc_html_e( 'Preloader Studio', 'preloader-studio-animated-loading-screens' ); ?> <em>v<?php echo esc_html( PRELSTAN_STUDIO_VERSION ); ?></em></h1><p><?php esc_html_e( 'Your loading, navigation & product design workspace', 'preloader-studio-animated-loading-screens' ); ?></p></div>
            <span class="ps-pill"><?php echo esc_html( self::pages()[ $slug ][0] ); ?></span>
          </header>
          <nav class="ps-section-tabs" aria-label="<?php esc_attr_e( 'Preloader Studio pages', 'preloader-studio-animated-loading-screens' ); ?>">
            <?php foreach ( self::pages() as $key => $item ) : ?>
              <a href="<?php echo esc_url( self::page_url( $key ) ); ?>" class="<?php echo $key === $slug ? 'is-active' : ''; ?>" <?php echo $key === $slug ? 'aria-current="page"' : ''; ?>><span class="dashicons <?php echo esc_attr( $item[1] ); ?>" aria-hidden="true"></span><span><?php echo esc_html( $item[0] ); ?></span></a>
            <?php endforeach; ?>
          </nav>
          <?php
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only status message set by an authenticated save handler.
          $updated = isset( $_GET['updated'] ) && is_string( $_GET['updated'] ) ? sanitize_key( wp_unslash( $_GET['updated'] ) ) : '';
          if ( in_array( $updated, array( '1', 'nav', 'auth', 'imported' ), true ) ) : ?>
          <div class="notice notice-success is-dismissible"><p><?php echo esc_html( 'imported' === $updated ? __( 'Import completed. Check image IDs and page links for this site.', 'preloader-studio-animated-loading-screens' ) : __( 'Settings saved successfully.', 'preloader-studio-animated-loading-screens' ) ); ?></p></div>
          <?php elseif ( 'bad_import' === $updated ) : ?>
          <div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Import failed. Choose a valid Preloader Studio JSON file below 100 KB.', 'preloader-studio-animated-loading-screens' ); ?></p></div>
          <?php endif; ?>
          <?php if ( $loading_name ) : ?>
          <?php /* translators: %s: name of the saved design being loaded. */ ?>
          <div class="notice notice-info"><p><?php echo esc_html( sprintf( __( 'Loaded saved design: %s. Review the preview, then click Apply Saved Design to publish all its settings.', 'preloader-studio-animated-loading-screens' ), $loading_name ) ); ?></p></div>
          <?php endif; ?>
          <?php if ( 'overview' === $section ) : ?>
            <?php self::overview( $s ); ?>
          <?php elseif ( 'navigation' === $section ) : ?>
            <?php self::navigation_panel(); ?>
          <?php elseif ( 'products' === $section ) : ?>
            <?php PRELSTAN_Studio_Products::admin_page(); ?>
          <?php elseif ( 'auth' === $section ) : ?>
            <?php PRELSTAN_Studio_Auth::admin_page(); ?>
          <?php elseif ( 'tools' === $section ) : ?>
            <?php include __DIR__ . '/views/tools.php'; ?>
          <?php else : ?>
            <form id="ps-settings-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
              <input type="hidden" name="action" value="ps_studio_save" />
              <input type="hidden" name="ps_section" value="<?php echo esc_attr( $section ); ?>" />
              <?php wp_nonce_field( 'ps_studio_save', 'ps_studio_nonce' ); ?>
              <?php if ( $design_id ) : ?><input type="hidden" name="ps_apply_design" value="<?php echo esc_attr( $design_id ); ?>" /><?php endif; ?>
              <?php
              // The preview and Saved Designs serialize a complete read-only snapshot;
              // only fields explicitly owned by this page may be written on Save.
              $owned = self::allowed_fields( $section );
              if ( 'designs' === $section ) { $owned = array(); }
              foreach ( $s as $key => $value ) {
                  if ( 'preset' === $key || ( 'enabled' === $key && 'designs' !== $section ) || ( in_array( $key, $owned, true ) && ! ( 'animations' === $section && 'source' === $key ) ) ) { continue; }
                  if ( ! is_scalar( $value ) ) { continue; }
                  echo '<input type="hidden" name="ps_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( (string) $value ) . '" />';
              }
              ?>
              <input id="ps-preset" name="ps_settings[preset]" type="hidden" value="<?php echo esc_attr( $s['preset'] ); ?>" />
              <?php if ( 'designs' !== $section ) : ?>
              <div class="ps-topbar"><div class="ps-onoff"><?php self::toggle( 'enabled', __( 'Enable Preloader', 'preloader-studio-animated-loading-screens' ), $s ); ?></div>
              <div class="ps-topbar-right"><span class="ps-status-dot" aria-hidden="true"></span><?php esc_html_e( 'Save to publish changes', 'preloader-studio-animated-loading-screens' ); ?>
              <button class="button button-primary ps-save" type="submit"><?php echo esc_html( $design_id ? __( 'Apply Saved Design', 'preloader-studio-animated-loading-screens' ) : __( 'Save Changes', 'preloader-studio-animated-loading-screens' ) ); ?></button></div></div>
              <?php endif; ?>
              <div class="ps-layout"><main class="ps-main">
                <?php include __DIR__ . '/views/' . $section . '.php'; ?>
                <?php if ( 'designs' === $section ) : ?><p class="ps-library-hint"><?php esc_html_e( 'Save a snapshot of your currently published preloader configuration, or load a saved design into the Visual Builder before publishing.', 'preloader-studio-animated-loading-screens' ); ?></p><?php endif; ?>
              </main>
              <?php include __DIR__ . '/views/preview.php'; ?>
              </div>
              <?php if ( 'designs' !== $section ) : ?><div class="ps-bottom-save"><span><?php esc_html_e( 'Only settings from this page will be changed.', 'preloader-studio-animated-loading-screens' ); ?></span><button type="submit" class="button button-primary ps-save"><?php echo esc_html( $design_id ? __( 'Apply Saved Design', 'preloader-studio-animated-loading-screens' ) : __( 'Save Changes', 'preloader-studio-animated-loading-screens' ) ); ?></button></div><?php endif; ?>
            </form>
            <div id="ps-current-visual" hidden><?php PRELSTAN_Studio::render_animation( $s['preset'], true ); ?></div>
            <?php include __DIR__ . '/views/fullscreen.php'; ?>
          <?php endif; ?>
        </div>
        <?php
    }

    private static function overview( $s ) {
        $nav = PRELSTAN_Studio_Navigation::settings();
        $audit = PRELSTAN_Studio_Navigation::audit( $nav );
        $cards = array(
            array( 'preloader-studio-animations', '36 Animations', 'Choose a motion design from six categories.', 'dashicons-art' ),
            array( 'preloader-studio-builder', 'Visual Builder', 'Reorder visual, text and progress layers.', 'dashicons-admin-customizer' ),
            array( 'preloader-studio-branding', 'Branding & Logo', 'Upload a logo, choose an image, and edit loading text.', 'dashicons-format-image' ),
            array( 'preloader-studio-style', 'Style & Timing', 'Change colors, backgrounds, transitions and speed.', 'dashicons-admin-appearance' ),
            array( 'preloader-studio-designs', 'My Designs', 'Save, restore or delete your design presets.', 'dashicons-saved' ),
            array( 'preloader-studio-rules', 'Rules & Performance', 'Configure loading, WooCommerce and theme handling.', 'dashicons-admin-generic' ),
            array( 'preloader-studio-navigation', 'Bottom Navigation', 'Build a 24-style mobile and tablet dock with links.', 'dashicons-smartphone' ),
            array( 'preloader-studio-products', 'Product Layouts', 'Build 32 distinct WooCommerce shop and category cards.', 'dashicons-cart' ),
            array( 'preloader-studio-auth', 'Login & Register', 'Six independent WordPress and WooCommerce account templates.', 'dashicons-lock' ),
            array( 'preloader-studio-tools', 'Import / Export', 'Back up or import configuration as JSON.', 'dashicons-database' ),
        );
        ?>
        <div class="ps-overview-hero"><div><div class="ps-overview-kicker"><?php esc_html_e( 'YOUR STUDIO', 'preloader-studio-animated-loading-screens' ); ?></div><h2><?php esc_html_e( 'Everything in its own place.', 'preloader-studio-animated-loading-screens' ); ?></h2><p><?php esc_html_e( 'Open a section below to manage only that feature. Changes in one section will not reset settings in another.', 'preloader-studio-animated-loading-screens' ); ?></p></div><div class="ps-overview-status"><span><?php esc_html_e( 'Preloader', 'preloader-studio-animated-loading-screens' ); ?>: <b><?php echo ! empty( $s['enabled'] ) ? esc_html__( 'Enabled', 'preloader-studio-animated-loading-screens' ) : esc_html__( 'Disabled', 'preloader-studio-animated-loading-screens' ); ?></b></span><span><?php esc_html_e( 'Bottom Menu', 'preloader-studio-animated-loading-screens' ); ?>: <b><?php echo ! empty( $nav['enabled'] ) ? esc_html__( 'Enabled', 'preloader-studio-animated-loading-screens' ) : esc_html__( 'Disabled', 'preloader-studio-animated-loading-screens' ); ?></b></span></div></div>
        <section class="ps-readiness" aria-labelledby="ps-readiness-heading">
          <div class="ps-readiness-heading"><div><span class="ps-overview-kicker"><?php esc_html_e( 'SITE READINESS', 'preloader-studio-animated-loading-screens' ); ?></span><h2 id="ps-readiness-heading"><?php esc_html_e( 'Launch checklist', 'preloader-studio-animated-loading-screens' ); ?></h2><p><?php esc_html_e( 'Quickly check your saved setup before viewing it on a real device.', 'preloader-studio-animated-loading-screens' ); ?></p></div><a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open live website ↗', 'preloader-studio-animated-loading-screens' ); ?></a></div>
          <div class="ps-readiness-grid">
            <div class="ps-readiness-row"><span class="ps-ready-icon <?php echo ! empty( $s['enabled'] ) ? 'is-good' : 'is-neutral'; ?> dashicons <?php echo ! empty( $s['enabled'] ) ? 'dashicons-yes-alt' : 'dashicons-marker'; ?>" aria-hidden="true"></span><div><strong><?php esc_html_e( 'Preloader', 'preloader-studio-animated-loading-screens' ); ?></strong><small><?php echo esc_html( ! empty( $s['enabled'] ) ? __( 'Enabled; check it on the public site.', 'preloader-studio-animated-loading-screens' ) : __( 'Currently disabled. Optional until you choose to publish.', 'preloader-studio-animated-loading-screens' ) ); ?></small></div><a href="<?php echo esc_url( self::page_url( 'preloader-studio-animations' ) ); ?>"><?php esc_html_e( 'Manage', 'preloader-studio-animated-loading-screens' ); ?></a></div>
            <div class="ps-readiness-row"><span class="ps-ready-icon <?php echo ! empty( $nav['enabled'] ) && $audit['ready'] ? 'is-good' : 'is-neutral'; ?> dashicons <?php echo ! empty( $nav['enabled'] ) && $audit['ready'] ? 'dashicons-yes-alt' : 'dashicons-marker'; ?>" aria-hidden="true"></span><div><strong><?php esc_html_e( 'Mobile bottom menu', 'preloader-studio-animated-loading-screens' ); ?></strong><small><?php echo esc_html( ! empty( $nav['enabled'] ) && $audit['ready'] ? __( 'Enabled with enough destinations.', 'preloader-studio-animated-loading-screens' ) : __( 'Disabled or needs at least two working links.', 'preloader-studio-animated-loading-screens' ) ); ?></small></div><a href="<?php echo esc_url( self::page_url( 'preloader-studio-navigation' ) ); ?>"><?php esc_html_e( 'Manage', 'preloader-studio-animated-loading-screens' ); ?></a></div>
<?php /* translators: %s: counts of working, incomplete and repeated menu destinations. */ ?>
            <div class="ps-readiness-row"><span class="ps-ready-icon <?php echo ! $audit['incomplete'] && ! $audit['duplicate_groups'] ? 'is-good' : 'is-review'; ?> dashicons <?php echo ! $audit['incomplete'] && ! $audit['duplicate_groups'] ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>" aria-hidden="true"></span><div><strong><?php esc_html_e( 'Menu link review', 'preloader-studio-animated-loading-screens' ); ?></strong><small><?php echo esc_html( sprintf( __( '%1$d working · %2$d incomplete · %3$d repeated destinations', 'preloader-studio-animated-loading-screens' ), $audit['valid'], $audit['incomplete'], count( $audit['duplicate_groups'] ) ) ); ?></small></div><a href="<?php echo esc_url( self::page_url( 'preloader-studio-navigation' ) ); ?>"><?php esc_html_e( 'Review', 'preloader-studio-animated-loading-screens' ); ?></a></div>
            <div class="ps-readiness-row"><span class="ps-ready-icon is-neutral dashicons dashicons-admin-tools" aria-hidden="true"></span><div><strong><?php esc_html_e( 'Backup & browser testing', 'preloader-studio-animated-loading-screens' ); ?></strong><small><?php esc_html_e( 'Export settings, then test on mobile, tablet and your active theme.', 'preloader-studio-animated-loading-screens' ); ?></small></div><a href="<?php echo esc_url( self::page_url( 'preloader-studio-tools' ) ); ?>"><?php esc_html_e( 'Export', 'preloader-studio-animated-loading-screens' ); ?></a></div>
          </div>
          <p class="ps-readiness-footnote"><?php esc_html_e( 'These are configuration checks, not a live performance scan or a guarantee of theme compatibility.', 'preloader-studio-animated-loading-screens' ); ?></p>
        </section>
        <div class="ps-overview-grid">
          <?php foreach ( $cards as $card ) : ?>
          <a class="ps-overview-card" href="<?php echo esc_url( self::page_url( $card[0] ) ); ?>"><span class="ps-overview-card-icon dashicons <?php echo esc_attr( $card[3] ); ?>" aria-hidden="true"></span><span class="ps-overview-card-body"><strong><?php echo esc_html( $card[1] ); ?></strong><small><?php echo esc_html( $card[2] ); ?></small></span><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></a>
          <?php endforeach; ?>
        </div>
        <?php
    }
}
