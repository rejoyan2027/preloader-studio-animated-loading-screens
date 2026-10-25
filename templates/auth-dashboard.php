<?php
/** Premium account surface; retains the theme's header/footer and WooCommerce endpoints. */
if(!defined('ABSPATH'))exit;
get_header();
PRELSTAN_Studio_Account_Dashboard::render();
get_footer();
