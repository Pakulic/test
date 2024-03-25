<?php

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

use Controllers\Role;


//-----------------------------------------------------
// Attribution des droits au role admin
//-----------------------------------------------------
function plugin_sim_add_capabilites_admin()
{
    $role = new Role();
    $role->plugin_sim_add_capabilities('administrator');
}

//-----------------------------------------------------
// Suppression des droits au role admi
//-----------------------------------------------------
function plugin_sim_remove_capabilites_admin()
{
    $role = new Role();
    $role->plugin_sim_remove_capabilities('administrator');
}

//-----------------------------------------------------
// Activation de la fonction lorsque la page d'options est mise à jour
//-----------------------------------------------------
add_filter('acf/options_page/save', 'plugin_sim_update_user_rights', 10, 2);

function plugin_sim_update_user_rights($post_id, $menu_slug)
{

    if ('plugin_sim_users_rights' !== $menu_slug) {

        /* attribution des droits aux roles définis dans la page  */
        $role = new Role();
        $rolesList = $role->plugin_sim_update_users_capabilities();


        /* mise à jour de la liste des roles utilisateurs dans la checkbox selon celle définie dans le site  */
        $role->plugin_sim_update_role_choices($rolesList);
    }
}
