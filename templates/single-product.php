<?php
/** Opt-in plugin-owned WooCommerce single product page, works without editing WoodMart/Elementor templates. */
if ( ! defined('ABSPATH') ) { exit; }
get_header('shop');
?>
<!-- Preloader Studio v2.3.3: Plugin-owned Single Product template -->
<main id="psp-single-main" class="psp-single-page woocommerce" role="main">
<?php
while (have_posts()) : the_post();
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WooCommerce requires its existing $product global for product images, variations and cart hooks.
    global $product;
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Set WooCommerce core product context (not a plugin-owned global).
    $product=wc_get_product(get_the_ID());
    if (!$product || !is_a($product, 'WC_Product')) { continue; }
    if (post_password_required()) { echo wp_kses( get_the_password_form(), array(
        'form' => array( 'action' => true, 'method' => true, 'class' => true ),
        'p' => array( 'class' => true ),
        'label' => array( 'for' => true ),
        'input' => array( 'type' => true, 'name' => true, 'id' => true, 'class' => true, 'value' => true, 'size' => true, 'required' => true ),
        'button' => array( 'type' => true, 'class' => true ),
    ) ); continue; }
    $ps_studio_settings=PRELSTAN_Studio_Products::settings();
    $ps_studio_layout=PRELSTAN_Studio_Products::layout_for_product($product,$ps_studio_settings);
    if (!isset(PRELSTAN_Studio_Products::layouts()[$ps_studio_layout])) { $ps_studio_layout=$ps_studio_settings['layout']; }
    // Visible only to a logged-in site administrator testing the template.
        // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Read-only admin verification badge; no state change.
    if (isset($_GET['psp_verify']) && current_user_can('manage_options')) {
        echo '<div class="psp-single-verified" role="status">Preloader Studio 2.3.3: Single Product template loaded · ' . esc_html(PRELSTAN_Studio_Products::layouts()[$ps_studio_layout]) . '</div>';
    }
    do_action('woocommerce_before_single_product');
    ?>
    <article id="product-<?php the_ID(); ?>" <?php wc_product_class('psp-single-product psp-single--'.sanitize_html_class($ps_studio_layout),$product); ?> style="<?php echo esc_attr(PRELSTAN_Studio_Products::single_css_vars($ps_studio_settings)); ?>">
        <div class="psp-single-eyebrow"><span><?php echo esc_html__('Product details','preloader-studio-animated-loading-screens'); ?></span><span><?php echo esc_html(PRELSTAN_Studio_Products::layouts()[$ps_studio_layout]); ?></span></div>
        <div class="psp-single-hero">
            <div class="psp-single-gallery" aria-label="<?php echo esc_attr__('Product gallery','preloader-studio-animated-loading-screens'); ?>">
                <?php do_action('woocommerce_before_single_product_summary'); ?>
            </div>
            <div class="psp-single-info">
                <div class="summary entry-summary">
                    <?php do_action('woocommerce_single_product_summary'); ?>
                </div>
            </div>
        </div>
        <div class="psp-single-details">
            <?php
            // Own the tabs UI. Keep the existing WooCommerce tab callbacks and the
            // remaining summary hooks (upsells, related products and extensions).
            if (function_exists('remove_action')) remove_action('woocommerce_after_single_product_summary','woocommerce_output_product_data_tabs',10);
            PRELSTAN_Studio_Products::render_single_tabs();
            do_action('woocommerce_after_single_product_summary');
            ?>
        </div>
    </article>
    <?php
    do_action('woocommerce_after_single_product');
endwhile;
?>
</main>
<?php get_footer('shop'); ?>
