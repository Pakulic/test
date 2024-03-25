<?php

if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

use Controllers\Translate;

//-----------------------------------------------------
// Modification du post-type généré par ACF, notamment pour traduire les labels dans l'interface admin
//-----------------------------------------------------
function gp_child_update_post_type_sim_purchase_offers($args, $post_type)
{
	return gp_child_update_custom_post_types($args, $post_type, 'esim-purchase-offer', 'gp_child_get_labels_for_sim_purchase_post_type');
}

function gp_child_update_post_type_sim_recharge_offers($args, $post_type)
{
	return gp_child_update_custom_post_types($args, $post_type, 'esim-recharge-offer', 'gp_child_get_labels_for_sim_recharge_post_type');
}

function gp_child_update_custom_post_types($args, $post_type, $currentPostType, $translateFunction)
{
	if ($post_type == $currentPostType) {

		$translate = new Translate();
		$labelsPurschaseSim = $translate->$translateFunction();

		$args['labels'] = array(
			'name'          => get_field($labelsPurschaseSim['name'], 'option')
		);
	}
	return $args;
}

add_filter('register_post_type_args', 'gp_child_update_post_type_sim_purchase_offers', 10, 2);
add_filter('register_post_type_args', 'gp_child_update_post_type_sim_recharge_offers', 10, 2);

//-----------------------------------------------------
// Ajouter les post-type dans la traduction de polylang
//-----------------------------------------------------


function gp_child_add_acf_pll($post_types, $is_settings)
{
	if ($is_settings) {
		// hides from the list of custom post types in Polylang settings
		unset($post_types['esim-purchase-offer']);
		unset($post_types['esim-recharge-offer']);
	} else {
		$post_types['esim-purchase-offer'] = 'esim-purchase-offer';
		$post_types['esim-recharge-offer'] = 'esim-recharge-offer';
	}
	$post_types[] = 'acf-field-group';
	return $post_types;
}

add_filter('pll_get_post_types', 'gp_child_add_acf_pll', 10, 2);

//-----------------------------------------------------
// Création de la page d'administration du plugin
//-----------------------------------------------------

function gp_child_widget_sim_add_admin_pages()
{
	add_menu_page(
		__('Plugin SIM offers', 'Orange Travel'),
		'Plugin SIM offers',
		'manage_options',
		'orange-travel-sim-offers/plugin-admin-home.php',
		'',
		null,
		null
	);
}

add_action('admin_menu', 'gp_child_widget_sim_add_admin_pages');
