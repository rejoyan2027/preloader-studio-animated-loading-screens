<?php
/** Independent WooCommerce guest account screen, no WooCommerce or theme auth template loaded. */
if (!defined('ABSPATH')) {exit;}
get_header();
// Do not render the_content or the WooCommerce shortcode: those would inject the old account forms.
echo PRELSTAN_Studio_Auth_Suite::render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by render().
get_footer();
