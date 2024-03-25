<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Liste des pages exclues de la recherche (dans un array php)
//-----------------------------------------------------

function generatepress_child_search_excluded_terms_as_array() {
    if(get_option('generate_child_exclude_from_search')){
        $slugs_to_exclude = json_decode(stripslashes(get_option('generate_child_exclude_from_search')), true);
    } else {
        $slugs_to_exclude = array(
            'home',
            'accueil',
            'a-propos',
            'cgv',
            'conditions',
            'confidentialite',
            'mentions-legales',
            'mentions',
            'contact',
        );
    }
return (array) $slugs_to_exclude;
}

//-----------------------------------------------------
// Exclusion pages non pertinentes de la recherche / Optimisé le 21/09/2023
//-----------------------------------------------------

add_action( 'pre_get_posts', 'generateperf_search_filter' );
function generateperf_search_filter($query)
{
    if (!$query->is_admin && $query->is_search() && $query->is_main_query()) {
        $slugs_to_exclude = generatepress_child_search_excluded_terms_as_array();
        $posts = get_posts([
            'post_name__in' => $slugs_to_exclude,
            'fields' => 'ids',
            'post_type' => 'any',
            'numberposts' => -1
        ]);
        if (!empty($posts)) {
            $query->set('post__not_in', $posts);
        }
    }
}

//-----------------------------------------------------
// On désactive le rendu par défaut de la recherche si CSE activé
//-----------------------------------------------------

add_filter('generate_do_template_part', function ($do) {
    if (is_search() && get_option('generate_child_cse_search')) {
        return false;
    }
    return $do;
});

//-----------------------------------------------------
// On retourne les résultats CSE à la place
//-----------------------------------------------------

add_action('generate_after_loop', 'generate_child_return_cse_search');
function generate_child_return_cse_search()
{
    if (is_search() && get_option('generate_child_cse_search')) {
        echo '<style>div.cse{width: 100%}table.gsc-input{margin-bottom:0!important;}table.gsc-search-box>tbody>tr,table.gsc-search-box>tbody>tr>td,table.gsc-input>tbody>tr,table.gsc-input>tbody>tr>td{border:none!important;}</style><div class="cse"><div class="gcse-search" data-queryParameterName="s" enableAutoComplete="true" data-newWindow="false" data-linkTarget="_top"></div></div>';
    }
}

//-----------------------------------------------------
// On désactive la pagination sur les pages de résultats
//-----------------------------------------------------

add_action('wp', 'generate_child_disable_search_pagination');
function generate_child_disable_search_pagination()
{
    if (is_search() && get_option('generate_child_cse_search')) {
        add_filter('generate_show_post_navigation', '__return_false');
    }
}
