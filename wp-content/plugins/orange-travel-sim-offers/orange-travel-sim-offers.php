<?php

/**
 * @package Plugin Orange Travel SIM offers
 * @version 1.0.0
 */
/*
Plugin Name: Plugin Orange Travel SIM offers
Description: This plugin allows to manage the display of selection fields (shorcode, administratio..) for the purchase and recharge of SIM cards.
Author: Christelle Pakulic, Neuron Partners
Version: 1.0.0
*/
if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly


define('PLUGIN_DIR_PATH_WIDGET_SIM', plugin_dir_path(__FILE__));
define('PLUGIN_DIR_URL_WIDGET_SIM',  plugin_dir_url(__FILE__));
/* Domaine pour la redirection des pages du widget sim */

add_action('acf/init', 'plugin_sim_get_domain');

function plugin_sim_get_domain()
{
	if ($domainUrl = get_field('domain_redirect_links', 'option')) {
		define('WIDGET_SIM_DOMAIN', $domainUrl);
	}
}

require_once PLUGIN_DIR_PATH_WIDGET_SIM . '/Controllers/Form.php';
require_once PLUGIN_DIR_PATH_WIDGET_SIM . '/Controllers/Role.php';
require_once PLUGIN_DIR_PATH_WIDGET_SIM . '/Controllers/Translate.php';
require_once PLUGIN_DIR_PATH_WIDGET_SIM . '/Models/Form.php';

require_once PLUGIN_DIR_PATH_WIDGET_SIM . '/inc/functions-view.php';
require_once PLUGIN_DIR_PATH_WIDGET_SIM . '/inc/functions-admin.php';
require_once PLUGIN_DIR_PATH_WIDGET_SIM . '/inc/functions-role.php';
require_once PLUGIN_DIR_PATH_WIDGET_SIM . '/inc/functions-ajax.php';

register_activation_hook(__FILE__, 'plugin_sim_activate');
register_deactivation_hook(__FILE__, 'plugin_sim_desactivate');


function plugin_sim_activate()
{
	/*  fonctions roles */
	plugin_sim_add_capabilites_admin();
}

function plugin_sim_desactivate()
{
	/*  fonctions styles / views */
	remove_action('wp_enqueue_scripts', 'gp_child_widget_sim_enqueue_script');
	remove_filter('pll_get_post_types', 'gp_child_add_acf_pll', 10, 2);
	remove_filter('register_post_type_args', 'gp_child_update_custom_post_types', 10, 2);

	/*  fonctions admin pages / shorcodes */
	remove_shortcode('widget_purchase_sim', 'gp_child_widget_purchase_sim');
	remove_shortcode('widget_recharge_sim', 'gp_child_widget_recharge_sim');
	remove_action('admin_menu', 'gp_child_widget_sim_add_admin_pages');
	/*  fonctions roles */
	plugin_sim_remove_capabilites_admin();
	remove_filter('acf/options_page/save', 'plugin_sim_update_user_rights', 10, 2);
}
