<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Chargement du script de décompte des lectures en entête
//-----------------------------------------------------

add_action('wp_enqueue_scripts', 'generatepress_child_enqueue_views', 5);
function generatepress_child_enqueue_views()
{
    if (generate_child_option_active_on_current_page('generate_child_views')) {
        wp_enqueue_script('views', get_stylesheet_directory_uri() . '/js/views.js', array(), gp_c_version, true);
    }
}

//-----------------------------------------------------
// Compteur de lectures en ajax via l'API Rest (update 10/10/2023)
//-----------------------------------------------------

add_action('rest_api_init', 'generateperf_register_views_route');
function generateperf_register_views_route() {
    register_rest_route('generateperf', '/update_views/', array(
        'methods' => 'POST',
        'callback' => 'generateperf_update_views',
        'permission_callback' => '__return_true',
    ));
}

function generateperf_update_views( WP_REST_Request $request ) {
    $post_id = $request['post_id'];
    $valid = false;
    if (!generateperf_is_robot() && $post_id) {
        $count = (int) get_post_meta($post_id, 'views', true);
        $valid = update_post_meta($post_id, 'views', ++$count);
    }
    return new WP_REST_Response(array('updated' => $valid), $valid ? 200 : 500);
}

//-----------------------------------------------------
// Affichage du nombre de lectures en entête d’article
//-----------------------------------------------------

add_filter('generate_post_author_output', 'generatepress_child_add_views_to_post', 15, 1);
function generatepress_child_add_views_to_post($output)
{
    if (generate_child_option_active_on_current_page('generate_child_views') && generate_child_option_active_on_current_page('generate_child_views_public')) {
        (int) $lectures = get_post_meta(get_the_ID(), 'views', true);
        if ($lectures > 9) {
            $label = array(
                'read' => sprintf(__( 'read %s times', 'generateperf' ), $lectures),
                'consultations' => sprintf(__( '%s consultations', 'generateperf' ), $lectures),
                'views' => sprintf(__( '%s views', 'generateperf' ), $lectures),
                'displays' => sprintf(__( 'displayed %s times', 'generateperf' ), $lectures),
                'visits' => sprintf(__( '%s visits', 'generateperf' ), $lectures),
                'visualizations' => sprintf(__( '%s visualizations', 'generateperf' ), $lectures),
            );
            $output .= ' <span class="o50">&middot;</span> <span class="meta-item">'.$label[get_option('generate_child_views_texts') ?: 'read'].'</span>';
        }
    }
    return $output ;
}

//-----------------------------------------------------
// On ne copie pas le nombre de vues si on duplique un contenu
//-----------------------------------------------------

add_filter('duplicate_post_excludelist_filter', 'generatepress_child_exclude_custom_fields', 10, 1);
function generatepress_child_exclude_custom_fields($meta_excludelist)
{
    return array_merge($meta_excludelist, array('views'));
}

//-----------------------------------------------------
// Tri des archives par nombre de lectures
//-----------------------------------------------------

add_action('pre_get_posts', 'generatepress_child_sort_order_views', 10, 1);
function generatepress_child_sort_order_views($query)
{
    if (generate_child_option_active_on_current_archive('generate_child_views_archives')) {
  //if ($query->get('post_type') == 'post' && !in_array(6, $query->get('category__in'))) {
        $query->set('meta_key', 'views');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'DESC');
    }
};
