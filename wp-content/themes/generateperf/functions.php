<?php

/**

 * Thème enfant GeneratePerf pour GeneratePress by Agence Web Performance

 */



if (!defined('ABSPATH')) {

    exit;
} // Exit if accessed directly



define('gp_c_version', '2.4.0');


//-----------------------------------------------------

// Internationalisation

//-----------------------------------------------------



load_theme_textdomain('generateperf', get_stylesheet_directory() . '/languages/');



//-----------------------------------------------------

// Test de l'activation d'une option en fonction du post type courant (singular)

//-----------------------------------------------------



function generate_child_option_active_on_current_page($option)
{

    if (!is_singular() || is_front_page()) {

        return false;
    }

    $allowed_post_types = json_decode(stripslashes(get_option($option)), true);

    if (!is_array($allowed_post_types)) {

        return false;
    }

    return array_key_exists(get_post_type(), $allowed_post_types);
}



//-----------------------------------------------------

// Test de l'activation d'une option en fonction du post type courant (archives)

//-----------------------------------------------------



function generate_child_option_active_on_current_archive($option)
{

    if (!is_archive()) {

        return false;
    }

    $option_value = get_option($option);

    if (!$option_value) {

        return false;
    }

    $allowed_post_types = json_decode(stripslashes($option_value), true);

    if (!is_array($allowed_post_types)) {

        return false;
    }

    return array_key_exists(get_queried_object()->name, $allowed_post_types);
}



//-----------------------------------------------------

// Appel des fonctions ventilées dans le répertoire /inc/

//-----------------------------------------------------



require_once get_stylesheet_directory() . '/inc/functions-i18n.php';

require_once get_stylesheet_directory() . '/inc/functions-security.php';

require_once get_stylesheet_directory() . '/inc/functions-performance.php';

require_once get_stylesheet_directory() . '/inc/functions-seo.php';

require_once get_stylesheet_directory() . '/inc/functions-users.php';

require_once get_stylesheet_directory() . '/inc/functions-medias.php';

require_once get_stylesheet_directory() . '/inc/functions-layout.php';

require_once get_stylesheet_directory() . '/inc/functions-fonts.php';

require_once get_stylesheet_directory() . '/inc/functions-colors.php';

require_once get_stylesheet_directory() . '/inc/functions-search.php';

require_once get_stylesheet_directory() . '/inc/functions-shortcodes.php';

require_once get_stylesheet_directory() . '/inc/functions-comments.php';

require_once get_stylesheet_directory() . '/inc/functions-views.php';

require_once get_stylesheet_directory() . '/inc/functions-ratings.php';

require_once get_stylesheet_directory() . '/inc/functions-custom.php';

require_once get_stylesheet_directory() . '/inc/functions-emailing.php';

require_once get_stylesheet_directory() . '/inc/functions-blocks.php';



if (get_option('generate_child_news_sitemap')) {

    require_once get_stylesheet_directory() . '/inc/functions-sitemaps.php';
}



if (defined('WC_PLUGIN_FILE')) {

    require_once get_stylesheet_directory() . '/inc/functions-woocommerce.php';
}



if (is_admin()) {

    require_once get_stylesheet_directory() . '/inc/functions-admin.php';

    require_once get_stylesheet_directory() . '/inc/functions-ratings-admin.php';
}



//-----------------------------------------------------

// Écrasement intégral du CSS parent si nécessaire

//-----------------------------------------------------



add_action('wp_enqueue_scripts', 'generatepress_child_manage_main_css', 1);

function generatepress_child_manage_main_css()

{

    if (get_option('generate_child_replace_main_css')) {

        wp_enqueue_style('generate-style', get_stylesheet_directory_uri() . '/css/main.css', array(), gp_c_version);
    }
}



//-----------------------------------------------------

// Gestion des variables CSS en inline du thème enfant

//-----------------------------------------------------



add_action('wp_enqueue_scripts', 'generatepress_child_requeue_style_with_variables', 11);

function generatepress_child_requeue_style_with_variables()

{

    $css_variables = ':root{--border-radius:' . get_option('generate_child_style_border_radius', '0') . ';}';

    wp_add_inline_style('generate-style', $css_variables);
}



//-----------------------------------------------------

// Appel des fichiers CSS du thème

//-----------------------------------------------------



add_action('wp_enqueue_scripts', 'generatepress_child_enqueue_styles', 20);

function generatepress_child_enqueue_styles()

{

    wp_enqueue_style('generate-reset', get_stylesheet_directory_uri() . '/css/reset.css', array('generate-style'), gp_c_version);



    // CSS de la CMP

    if (get_option('generate_child_native_cmp')) {

        wp_enqueue_style('cmp', get_stylesheet_directory_uri() . '/css/cmp.css', array('generate-style'), gp_c_version);
    }



    // Reset du style des pages d'archives et block dernières actualités

    switch (get_option('generate_child_style_articles_style')) {

        case 'cover':

            $css_file = '-cover';

            break;

        case 'cards':

            $css_file = '-cards';

            break;

        case 'classic':

        default:

            $css_file = '';

            break;
    }

    wp_enqueue_style('articles-cards', get_stylesheet_directory_uri() . '/css/articles-reset' . $css_file . '.css', array('generate-style'), gp_c_version);



    // Articles relatifs in-text seulement si activé

    $active_locations = json_decode(stripslashes(get_option('generate_child_similar')), true);

    if (is_single() && 'post' == get_post_type() && is_array($active_locations) && array_key_exists('inside_content', $active_locations)) {

        wp_enqueue_style('related-articles', get_stylesheet_directory_uri() . '/css/related-articles.css', array('generate-style'), gp_c_version);
    }



    // Script de gestion des notes utilisateurs

    if (generate_child_option_active_on_current_page('generate_child_ratings')) {

        wp_enqueue_style('ratings', get_stylesheet_directory_uri() . '/css/ratings.css', array('generate-style'), gp_c_version);
    }



    // Affichage des archives par date

    if (is_date()) {

        wp_enqueue_style('date-archives', get_stylesheet_directory_uri() . '/css/date-archives.css', array('generate-style'), gp_c_version);
    }



    // Affichage stylisé des "pills"

    if (is_single() && 'post' == get_post_type() && get_option('generate_child_cats_above_titles')) {

        wp_enqueue_style('badges-pills', get_stylesheet_directory_uri() . '/css/badges-pills.css', array('generate-style'), gp_c_version);
    }



    // Bouton Google News seulement si activé

    if (is_single() && 'post' == get_post_type() && get_option('generate_child_ggnews_url')) {

        wp_enqueue_style('google-news-button', get_stylesheet_directory_uri() . '/css/google-news-button.css', array('generate-style'), gp_c_version);
    }



    // Sticky sidebar seulement si activé

    if (get_option('generate_child_sticky_sidebar')) {

        wp_enqueue_style('sticky-sidebar', get_stylesheet_directory_uri() . '/css/sticky-sidebar.css', array('generate-style'), gp_c_version);
    }



    // Source seulement si activé

    if (generate_child_option_active_on_current_page('generate_child_acf_content_source')) {

        wp_enqueue_style('source', get_stylesheet_directory_uri() . '/css/sources.css', array('generate-style'), gp_c_version);
    }



    // Copie des liens en relation avec TOC seulement si activé

    if (generate_child_option_active_on_current_page('generate_child_toc') && get_option('generate_child_toc_copy_links')) {

        wp_enqueue_style('copy-links', get_stylesheet_directory_uri() . '/css/copy-links.css', array('generate-style'), gp_c_version);
    }



    // Boutons de partage social seulement si activé

    if (generate_child_option_active_on_current_page('generate_child_social_share')) {

        wp_enqueue_style('social-share', get_stylesheet_directory_uri() . '/css/social-share.css', array('generate-style'), gp_c_version);
    }



    // Sommaire automatique seulement si activé

    if (generate_child_option_active_on_current_page('generate_child_toc')) {



        switch (get_option('generate_child_toc_style')) {

            case 'numbering':

                $css_file = '-numbering';

                break;

            case 'classic':

            default:

                $css_file = '';

                break;
        }



        wp_enqueue_style('toc', get_stylesheet_directory_uri() . '/css/toc' . $css_file . '.css', array('generate-style'), gp_c_version);
    }



    // Encart auteur seulement si activé

    if (generate_child_option_active_on_current_page('generate_child_author_box') || is_author()) {

        wp_enqueue_style('authors', get_stylesheet_directory_uri() . '/css/authors.css', array('generate-style'), gp_c_version);
    }



    // Chargement du CSS aos seulement si activé

    if (get_option('generate_child_load_aos')) {

        wp_enqueue_style('aos', get_stylesheet_directory_uri() . '/css/aos.css', array('generate-style'), gp_c_version);
    }



    // Listes responsive

    if ((is_category() && get_option('generate_child_categories_children')) || (is_tag() && get_option('generate_child_tags_related_in_archives'))) {

        wp_enqueue_style('splitted-list', get_stylesheet_directory_uri() . '/css/splitted-list.css', array('generate-style'), gp_c_version);
    }
}



//-----------------------------------------------------

// Appel des fichiers Javascript du thème

//-----------------------------------------------------



add_action('wp_enqueue_scripts', 'generatepress_child_enqueue_scripts', 10);

function generatepress_child_enqueue_scripts()

{

    // Fichier javascript principal

    wp_enqueue_script('scripts', get_stylesheet_directory_uri() . '/js/scripts.js', array(), gp_c_version, true);



    // Barre de défilement seulement si activé

    if (generate_child_option_active_on_current_page('generate_child_progress_bar')) {

        wp_enqueue_script('progress-bar', get_stylesheet_directory_uri() . '/js/progress-bar.js', array(), gp_c_version, true);
    }



    // Copie des liens en relation avec TOC seulement si activé

    if (generate_child_option_active_on_current_page('generate_child_toc') && get_option('generate_child_toc_copy_links')) {

        wp_enqueue_script('copy-links', get_stylesheet_directory_uri() . '/js/copy-links.js', array(), gp_c_version, true);
    }



    // Script de gestion des notes utilisateurs

    if (generate_child_option_active_on_current_page('generate_child_ratings')) {

        wp_enqueue_script('user-ratings', get_stylesheet_directory_uri() . '/js/user-ratings.js', array(), gp_c_version, true);

        wp_add_inline_script('user-ratings', 'var generateperf_user_ratings = {saved:"' . __('Your vote has been recorded', 'generateperf') . '",error:"' . __('Error during voting', 'generateperf') . '"};');
    }



    // Boutons de partage social seulement si activé

    if (generate_child_option_active_on_current_page('generate_child_social_share')) {

        wp_enqueue_script('social-share', get_stylesheet_directory_uri() . '/js/social-share.js', array(), gp_c_version, true);

        wp_add_inline_script('social-share', 'var generateperf_social_share = {"url" : "' . get_the_permalink() . '", "title" : "' . str_replace('"', '\"', get_the_title()) . '", "image" : "' . urlencode(wp_get_attachment_image_src((get_post_thumbnail_id() ? get_post_thumbnail_id() : get_theme_mod('custom_logo')), 'full')[0]) . '",copied:"' . __('Link copied!', 'generateperf') . '"};');
    }



    // Chargement du JS AOS seulement si activé

    if (get_option('generate_child_load_aos')) {

        wp_enqueue_script('aos', get_stylesheet_directory_uri() . '/js/aos.js', array(), gp_c_version, true);
    }



    // Chargement du JS Rellax seulement si activé

    if (get_option('generate_child_load_rellax')) {

        wp_enqueue_script('rellax', get_stylesheet_directory_uri() . '/js/rellax.min.js', array(), gp_c_version, true);
    }



    // Script de consentement seulement si activé

    if (get_option('generate_child_native_cmp')) {

        wp_enqueue_script('cmp-load', get_stylesheet_directory_uri() . '/js/cmp.js', array(), gp_c_version, true);

        wp_add_inline_script('cmp-load', 'var privacy_policy_url="' . get_privacy_policy_url() . '";', 'before');

        if (get_option('generate_child_native_cmp_toggler')) {

            wp_add_inline_script('cmp-load', 'var display_cmp_toggler=true;', 'before');
        }
    }



    if (is_search() && get_option('generate_child_cse_search')) {

        wp_enqueue_script('google-cse', 'https://cse.google.com/cse.js?cx=' . get_option('generate_child_cse_search'), array(), gp_c_version, true);
    }
}



//-----------------------------------------------------

// Ajout des scripts en en-tête, parfaitement priorisés

//-----------------------------------------------------



add_action('wp_head', 'generatepress_child_display_custom_header_code', 5);

function generatepress_child_display_custom_header_code()

{

    echo get_option('generate_child_custom_header_code', '');
}



//-----------------------------------------------------

// Ajout des scripts en footer, le plus tard possible

//-----------------------------------------------------



add_action('wp_footer', 'generatepress_child_display_custom_footer_code', PHP_INT_MAX);

function generatepress_child_display_custom_footer_code()

{

    echo get_option('generate_child_custom_footer_code', '');
}



//-----------------------------------------------------

// Modifications à l'activation

//-----------------------------------------------------



add_action('after_switch_theme', 'generate_child_setup_options');

function generate_child_setup_options()
{



    // Taille des miniatures

    update_option('thumbnail_size_w', 150);

    update_option('thumbnail_size_h', 150);

    update_option('medium_size_w', 400);

    update_option('medium_size_h', 400);

    update_option('large_size_w', 1200);

    update_option('large_size_h', 1200);



    // Paramètres de WP Rocket

    generatepress_child_wp_rocket_set_default_parameters();



    // Paramètres d’Imagify

    generate_child_imagify_set_default_parameters();



    // Optimisation en masse

    generate_child_imagify_launch_bulk_optimization();
}
