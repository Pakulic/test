<?php
  if (!defined('ABSPATH')) {
    exit;
  }



use Controllers\Form as ControllerForm;


function plugin_sim_instant_search(){
    $form = new ControllerForm();
    $form->plugin_sim_country_search();
}
add_action('wp_ajax_nopriv_plugin_sim_instant_search',  'plugin_sim_instant_search');
add_action('wp_ajax_plugin_sim_instant_search', 'plugin_sim_instant_search');



function plugin_sim_countries_list(){
    $form = new ControllerForm();
    $form->plugin_sim_countries_list();
}
add_action('wp_ajax_nopriv_plugin_sim_countries_list',  'plugin_sim_countries_list');
add_action('wp_ajax_plugin_sim_countries_list', 'plugin_sim_countries_list');

