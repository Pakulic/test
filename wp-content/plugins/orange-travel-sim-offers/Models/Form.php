<?php namespace Models;

use WP_query;

class Form
{

    public function get_post_type($type)
    {
        $args = array(
            'post_type' => array($type),
            'post_status'  => array('publish'),
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        $query = new WP_Query($args);
        wp_reset_postdata();
        return $query;
    }
    public function plugin_sim_get_search_country($type)
    {

        $searchValue = sanitize_text_field( $_POST['keyword']);
        $args = array(
            'post_type' => array($type),
            'post_status'  => array('publish'),
            'posts_per_page' => -1,
            's' => $searchValue,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        // Si la recherche est vide, la requête retournera tous les posts
        if (empty($searchValue)) {
            unset($args['search_prod_title']);
        }
        $query = new WP_Query($args);
        wp_reset_postdata();
        return $query;
    }
}
