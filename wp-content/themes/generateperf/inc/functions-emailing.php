<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Changement des infos d’expédition pour les emails
//-----------------------------------------------------

add_filter('wp_mail_from_name', 'generatepress_child_sender_name');
function generatepress_child_sender_name()
{
    return get_bloginfo('name');
}
