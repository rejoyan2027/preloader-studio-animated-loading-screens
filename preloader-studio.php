<?php
/**
 * Plugin Name: Preloader Studio - Animated Loading Screens
 * Description: Build responsive preloaders, mobile navigation, WooCommerce product layouts, and custom login/register and account designs.
 * Version: 2.6.5
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: codelith
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: preloader-studio-animated-loading-screens
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PRELSTAN_STUDIO_VERSION', '2.6.5' );
define( 'PRELSTAN_STUDIO_FILE', __FILE__ );
define( 'PRELSTAN_STUDIO_URL', plugin_dir_url( __FILE__ ) );
define( 'PRELSTAN_STUDIO_DIR', plugin_dir_path( __FILE__ ) );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-preloader-studio.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-preloader-studio-navigation.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-preloader-studio-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-preloader-studio-products.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-preloader-studio-auth.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-preloader-studio-auth-suite.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-preloader-studio-account-dashboard.php';

register_activation_hook( __FILE__, array( 'PRELSTAN_Studio', 'activate' ) );
add_action( 'plugins_loaded', array( 'PRELSTAN_Studio', 'init' ) );
add_action( 'plugins_loaded', array( 'PRELSTAN_Studio_Navigation', 'init' ) );

add_action( 'plugins_loaded', array( 'PRELSTAN_Studio_Products', 'init' ) );

add_action( 'plugins_loaded', array( 'PRELSTAN_Studio_Auth', 'init' ) );
add_action( 'plugins_loaded', array( 'PRELSTAN_Studio_Account_Dashboard', 'init' ) );
