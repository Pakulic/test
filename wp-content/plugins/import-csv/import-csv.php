<?php
/**
 * @package Import csv
 * @version 1.0.0
 */
/*
Plugin Name:  Import csv
Description: Ce plugin permet d'importer les données d'offres d'achat SIM en csv
Author: Christelle Pakulic
Version: 1.0.0
*/
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

define('PLUGIN_URL_IMPORT_CSV' ,plugin_dir_path(__FILE__) );


register_activation_hook(__FILE__,'gp_child_add_admin_pages');

 register_deactivation_hook(__FILE__, function () {
    remove_action('admin_menu', 'gp_child_add_admin_pages');
 });


 function gp_child_add_admin_pages()
 {
     add_menu_page(
         __(' Data import', 'Orange Travel'),
         'Importer en csv',
         'manage_options',
         'import-csv/import.php',
         '',
         null,
         null
     );
     add_submenu_page(
         __('Data import', 'Orange Travel'),
         'Prismamatch traitement import csv',
         'traitement import csv',
         'manage_options',
         'import-csv/traitement.php',
         '',
     );
 }
 add_action('admin_menu', 'gp_child_add_admin_pages');
 
