<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Désactivation du cache des fragments panier WooCommerce
//-----------------------------------------------------

add_filter( 'rocket_cache_wc_empty_cart', '__return_false' );