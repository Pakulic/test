<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Masquage des erreurs détaillées à la connexion
//-----------------------------------------------------

add_filter( 'login_errors', 'generatepress_child_no_wordpress_errors' );
function generatepress_child_no_wordpress_errors(){
    return __('Something went wrong!', 'generateperf');
}

//-----------------------------------------------------
// Pas de xmlrpc
//-----------------------------------------------------

add_filter('xmlrpc_enabled', '__return_false');
remove_action('wp_head', 'rsd_link');
