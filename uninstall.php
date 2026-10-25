<?php
/** Clean up plugin options only on uninstall, never on deactivation. */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}
foreach ( array(
    'ps_studio_settings',
    'ps_studio_designs',
    'ps_studio_navigation',
    'ps_studio_product_layouts',
    'ps_studio_product_designs',
    'ps_studio_auth',
    'ps_studio_auth_page',
) as $ps_studio_option_name ) {
    delete_option( $ps_studio_option_name );
}
// Existing user profile metadata is retained to avoid destroying customer data.
// See readme.txt: Privacy and Uninstall.
