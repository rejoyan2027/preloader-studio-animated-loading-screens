<?php
/** WooCommerce named hover template nested within a theme product wrapper. */
defined('ABSPATH') || exit;
global $product;
if (!is_a($product,'WC_Product') || !$product->is_visible()) return;
PRELSTAN_Studio_Products::card($product,null,false,false,true);
