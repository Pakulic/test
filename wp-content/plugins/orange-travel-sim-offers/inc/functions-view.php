<?php

if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

use Controllers\Form;
use Controllers\Translate;

//-----------------------------------------------------
// Ajout des fichiers style et scripts du plugin
//-----------------------------------------------------

function gp_child_widget_sim_enqueue_script()
{
	wp_register_style('widget-sim-css', PLUGIN_DIR_URL_WIDGET_SIM  . 'css/widget-sim.css', array(), gp_c_version);
    wp_enqueue_style('widget-sim-css');

    wp_register_style('flags-css', PLUGIN_DIR_URL_WIDGET_SIM  . 'css/flags.css', array(), gp_c_version);
    wp_enqueue_style('flags-css');

    wp_register_script('widget-sim-js', PLUGIN_DIR_URL_WIDGET_SIM . 'js/widget-sim-recharge.js', array(), gp_c_version, true);
    wp_enqueue_script('widget-sim-js');

    wp_enqueue_script(
        'search-ajax',
		PLUGIN_DIR_URL_WIDGET_SIM . '/js/search-ajax.js', [ 'jquery' ],
        '1.0',
        true
    );

    wp_localize_script('search-ajax', 'SearchAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

}
add_action('wp_enqueue_scripts', 'gp_child_widget_sim_enqueue_script');

//-----------------------------------------------------
// création du champs de recherche pour l'achat de la carte sim 
//-----------------------------------------------------

function gp_child_widget_purchase_sim($atts)
{
	$form = new Form;
	$translate = new Translate();
	$params = $translate->gp_child_translate_purchase_sim_form();
	$params['postType']   = 'esim-purchase-offer';
    $params['file']   = 'form/esim-purchase-form.php';
	$pageContent = $form->plugin_sim_create_form_offers($params );
	return $pageContent;
}
add_shortcode('widget_purchase_sim', 'gp_child_widget_purchase_sim');

//-----------------------------------------------------
// création du champs de recherche pour la recharge de la carte sim 
//-----------------------------------------------------

function gp_child_widget_recharge_sim($atts)
{
	$form = new Form;
	$translate = new Translate();
	$params = $translate->gp_child_translate_recharge_sim_form();
	$params['postType'] = 'esim-recharge-offer';
    $params['file']   = 'form/esim-recharge-form.php';
	$pageContent = $form->plugin_sim_create_form_offers($params );
	return $pageContent;
}
add_shortcode('widget_recharge_sim', 'gp_child_widget_recharge_sim');