<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Définition des couleurs utilisées par le thème (à personnaliser)
//-----------------------------------------------------

function generatepress_child_custom_color_palettes()
{
    return array(
        array(
            'name'  => __('Blanc'),
            'slug'  => 'white',
            'color' => '#FFFFFF',
        ),
        array(
            'name'  => __('Gris clair'),
            'slug'  => 'lightgray',
            'color' => '#CCCCCC',
        ),
        array(
            'name'  => __('Gris'),
            'slug'  => 'gray',
            'color' => '#666666',
        ),
        array(
            'name'  => __('Gris foncé'),
            'slug'  => 'darkgray',
            'color' => '#333333',
        ),
        array(
            'name'  => __('Noir'),
            'slug'  => 'black',
            'color' => '#000000',
        ),
        array(
            'name'  => __('Primaire'),
            'slug'  => 'primary',
            'color' => '#1E73BE',
        ),
        array(
            'name'  => __('Secondaire'),
            'slug'  => 'secondary',
            'color' => '#BE691E',
        ),
        array(
            'name'  => __('Complémentaire'),
            'slug'  => 'complementary',
            'color' => '#BE1E73',
        ),
        array(
            'name'  => __('Analogue'),
            'slug'  => 'analog',
            'color' => '#73BE1E',
        ),
    );
}

//-----------------------------------------------------
// Création de la palette de couleurs pour le customizer GeneratePress
//-----------------------------------------------------

add_filter('generate_default_color_palettes', 'generatepress_child_apply_theme_palette');
function generatepress_child_apply_theme_palette($palettes)
{
    return wp_list_pluck(generatepress_child_custom_color_palettes(), 'color');
}

//-----------------------------------------------------
// Création de la palette de couleurs pour Gutenberg & GenerateBlocks
//-----------------------------------------------------

add_action('after_setup_theme', function () {
    add_theme_support('editor-color-palette', generatepress_child_custom_color_palettes());
});

//-----------------------------------------------------
// Couleur par défaut de la barre de progression de lecture
//-----------------------------------------------------

add_action('wp_head', 'generatepress_child_progress_bar_color');
function generatepress_child_progress_bar_color()
{
  if(generate_child_option_active_on_current_page('generate_child_progress_bar')){
    echo '<style>.progress-bar{background-color:'.generate_get_option( 'link_color' ).'}</style>';
  }
}