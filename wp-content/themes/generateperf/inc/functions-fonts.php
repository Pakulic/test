<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Désactivation des Google Fonts (GP 3.2 et supérieur)
//-----------------------------------------------------

add_filter( 'generate_font_manager_show_google_fonts', '__return_false' );

//-----------------------------------------------------
// Chargement du CSS des fontes actives
//-----------------------------------------------------

add_action('wp_enqueue_scripts', 'generatepress_child_enqueue_local_fonts', 2);
function generatepress_child_enqueue_local_fonts()
{
    $active_fonts = generatepress_child_active_fonts();
    $local_fonts = generatepress_child_local_fonts();
    foreach ($active_fonts as $font_name) {
        if (!empty($font_name) && array_key_exists($font_name, $local_fonts)) {
            wp_enqueue_style('local-fonts-'.$local_fonts[$font_name], get_stylesheet_directory_uri() . '/css/local-fonts-'.$local_fonts[$font_name].'.css', array(), gp_c_version);
        }
    }
}

//-----------------------------------------------------
// Liste des fontes locales (à uploader dans /css/ et /fonts/)
//-----------------------------------------------------

function generatepress_child_local_fonts()
{
    return array(
      'Albert Sans' => 'albert-sans',
      'Alkatra' => 'alkatra',
      'Archivo' => 'archivo',
      'Barlow' => 'barlow',
      'Bitter' => 'bitter',
      'Centrale Sans' => 'centrale-sans',
      'Coda' => 'coda',
      'Core Sans' => 'core-sans',
      'Crimson Text' => 'crimson-text',
      'Dosis' => 'dosis',
      'Heebo' => 'heebo',
      'Helvetica Neue (Orange)' => 'o-helveticaneue',
      'Inter' => 'inter',
      'Jost' => 'jost',
      'Lato' => 'lato',
      'Lexend' => 'lexend',
      'Lexend Deca' => 'lexend-deca',
      'Libre Franklin' => 'libre-franklin',
      'Lora' => 'lora',
      'Manrope' => 'manrope',
      'Maven Pro' => 'maven-pro',
      'Montserrat' => 'montserrat',
      'Mulish' => 'mulish',
      'Noto Sans' => 'noto-sans',
      'Old Standard TT' => 'old-standard-tt',
      'Open Sans' => 'open-sans',
      'Oswald' => 'oswald',
      'Outfit' => 'outfit',
      'PT Serif' => 'pt-serif',
      'Playfair Display' => 'playfair-display',
      'Plus Jakarta Sans' => 'plus-jakarta-sans',
      'Poppins' => 'poppins',
      'Questrial' => 'questrial',
      'Quicksand' => 'quicksand',
      'Raleway' => 'raleway',
      'Readex Pro' => 'readex-pro',
      'Red Hat Display' => 'redhat-display',
      'Righteous' => 'righteous',
      'Roboto' => 'roboto',
      'Source Sans' => 'source-sans-3',
      'Spartan' => 'spartan',
      'Titillium Web' => 'titillium-web',
      'Urbanist' => 'urbanist',
      'Varta' => 'varta',
      'Vollkorn' => 'vollkorn',
      'Work Sans' => 'work-sans',
    );
}

//-----------------------------------------------------
// Utilisation de fontes locales dans le Customizer WordPress (GP 3.2 et supérieur)
//-----------------------------------------------------

add_action('customize_controls_print_footer_scripts', 'generatepress_child_fonts_in_customizer_v2');

function generatepress_child_fonts_in_customizer_v2()
{
    echo '<script>wp.hooks.addFilter("generate_font_manager_system_fonts","generatepress/child",($fonts)=>[';
    $local_fonts = generatepress_child_local_fonts();
    foreach ($local_fonts as $font_label => $font_file_name) {
        echo '{value:"' . $font_label . '",label:"' . $font_label . '"},';
    }
    echo ']);</script>';
}

//-----------------------------------------------------
// Liste des fontes utilisées par GeneratePress
//-----------------------------------------------------

function generatepress_child_active_fonts()
{
    if (!function_exists('generate_get_default_fonts')) {
        return array();
    }

    $settings = wp_parse_args(
        get_option('generate_settings', array()),
        generate_get_default_fonts()
    );

    // Détection de la version 3 avec Font Manager
    if(isset($settings['font_manager']) && isset($settings['typography'])){

        $used_fonts = wp_list_pluck($settings['typography'], 'fontFamily');

    } else {

        $used_fonts = array(
            $settings['font_body'],
            $settings['font_top_bar'],
            $settings['font_site_title'],
            $settings['font_site_tagline'],
            $settings['font_navigation'],
            $settings['font_widget_title'],
            $settings['font_buttons'],
            $settings['font_heading_1'],
            $settings['font_heading_2'],
            $settings['font_heading_3'],
            $settings['font_heading_4'],
            $settings['font_heading_5'],
            $settings['font_heading_6'],
            $settings['font_footer']
          );
        
    }

    return array_unique($used_fonts);
}
