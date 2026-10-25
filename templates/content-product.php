<?php
/** Product-loop template override. Only used when a store admin explicitly enables it. */
defined('ABSPATH') || exit;
global $product;
if (!is_a($product,'WC_Product') || !$product->is_visible()) { return; }
PRELSTAN_Studio_Products::card($product);
