<?php

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Titre <title> customisé dans les archives par mois
//-----------------------------------------------------

add_filter('pre_get_document_title', function($title) {
    if (is_date()) {
        $year  = get_query_var('year');
        $month = get_query_var('monthnum');
        if ($year && $month) {
            $title = sprintf( __('Archives for %s', 'generateperf'), $title );
        }
    }
    return $title;
}, PHP_INT_MAX);

//-----------------------------------------------------
// Titre h1 customisé dans les archives par mois
//-----------------------------------------------------

add_filter('get_the_archive_title', function($title) {
    if (is_date()) {
        $year  = get_query_var('year');
        $month = get_query_var('monthnum');
        if ($year && $month) {
            $title = sprintf( __('Archives for %s', 'generateperf'), $title );
        }
    }
    return $title;
});

//-----------------------------------------------------
// Navigation dans les archives des posts
//-----------------------------------------------------

add_action('generate_after_archive_title', 'generateperf_date_archives_navigation', 20);
function generateperf_date_archives_navigation() {
    if (is_date()) {

        global $wp_query;
        $year = get_query_var('year');
        $month = get_query_var('monthnum');
        $args = array(
            'year' => $year,
            'monthnum' => $month,
            'posts_per_page' => -1,
        );
        $query = new WP_Query($args);
        $all_tags = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_tags = get_the_tags();
                if ($post_tags) {
                    foreach ($post_tags as $tag) {
                        $all_tags[] = $tag->term_id;
                    }
                }
            }
            wp_reset_postdata();
        }

        $tag_counts = array_count_values($all_tags);
        arsort($tag_counts);
        $popular_tags = array_slice(array_keys($tag_counts), 0, 20, true);

        if (!empty($popular_tags)) {
            $tag_names = array();
            foreach ($popular_tags as $tag) {
                $term = get_term($tag);
                $tag_names[] = esc_html($term->name);
            }

            $first_ten_tags = array_slice($tag_names, 0, 10);
            $next_ten_tags = array_slice($tag_names, 10);

            $first_tag_list = wp_sprintf('%l', $first_ten_tags);
            $second_tag_list = wp_sprintf('%l', $next_ten_tags);
            if(!empty($first_tag_list)){
                echo '<p>' . sprintf( __( 'That month <strong>the most popular topics</strong> were %s.', 'generateperf' ), $first_tag_list ) . '</p>';
                if(!empty($second_tag_list)){
                    echo '<br>' . sprintf( __( 'However, other themes have been <strong>addressed on %s</strong>, such as %s.', 'generateperf' ), get_bloginfo('name'), $second_tag_list ) . '</p>';
                }
                echo generateperf_separator();
            }
        }
        
        echo '<h2>' . __('All our publications', 'generateperf') . '</h2>';
        echo '<p>' . sprintf( __( 'Access all articles published on %s by selecting the date of your choice:', 'generateperf' ), get_bloginfo('name') ) . '</p>';

        global $wpdb;
        $current_year = get_query_var('year');
        $current_month = get_query_var('monthnum');
        $years_with_posts = $wpdb->get_results("SELECT DISTINCT YEAR(post_date) as year FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC", ARRAY_A);
        echo '<div class="archive-selector"><h3 class="simple">'.__('Select a year:', 'generateperf').'</h3>';
        foreach($years_with_posts as $year_array) {
            $year = $year_array['year'];
            $first_month = $wpdb->get_var($wpdb->prepare("SELECT MIN(MONTH(post_date)) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' AND YEAR(post_date) = %s", $year));
            $first_month = str_pad($first_month, 2, "0", STR_PAD_LEFT);
            if ($current_year == $year) {
                echo '<span class="button current"><strong>' . $year . '</strong></span>';
                $display_year = $year;
            } else {
                echo '<a class="button simple" href="' . get_home_url() . '/' . $year . '/' . user_trailingslashit($first_month) . '">' . $year . '</a>';
            }
        }
        echo '</div>';
        
        $months_with_posts = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT MONTH(post_date) as month FROM $wpdb->posts WHERE post_status = 'publish' AND YEAR(post_date) = %s ORDER BY post_date ASC", $current_year), ARRAY_A);
        echo '<div class="archive-selector"><h3 class="simple">'.__('Select a month:', 'generateperf').'</h3>';
        foreach($months_with_posts as $month_array) {
            $month = $month_array['month'];
            $month_name = date_i18n("F", mktime(0, 0, 0, $month, 1, $year));
            if ($current_month == str_pad($month, 2, "0", STR_PAD_LEFT)) {
                echo '<span class="button current"><strong>' . $month_name . '</strong></span>';
                $display_month = $month_name;
            } else {
                echo '<a class="button simple" href="' . get_home_url() . '/' . $current_year . '/' . user_trailingslashit(str_pad($month, 2, "0", STR_PAD_LEFT)) . '">' . $month_name . '</a>';
            }
        }
        echo '</div>';
        echo generateperf_separator();
        echo '<h2>' . sprintf( __( 'Content published in %s', 'generateperf' ), $display_month . $display_year ). '</h2>';
    }
}

//-----------------------------------------------------
// Modif date de publication uniquement si changement réel (plus de 1% de changement)
// Reco https://www.sistrix.fr/blog/google-helpful-content-update-de-septembre-2023/
//-----------------------------------------------------

add_filter('wp_insert_post_data', 'generateperf_prevent_minor_update', 10, 2);

function generateperf_prevent_minor_update($data, $postarr) {
    $post_ID = $postarr['ID'];
    $post_before = get_post($post_ID);
    if ($post_before && !wp_is_post_revision($post_ID)) {
        $content_before = strip_tags($post_before->post_content);
        $content_after = strip_tags($data['post_content']);
        similar_text($content_before, $content_after, $similarity_percent);
        if ($similarity_percent > 99) {
            $data['post_modified'] = $post_before->post_modified;
            $data['post_modified_gmt'] = $post_before->post_modified_gmt;
        }
    }
    return $data;
}

//-----------------------------------------------------
// Suppression des sous-menus en fonction de la page courante
//-----------------------------------------------------

class Custom_Walker_Nav_Menu extends Walker_Nav_Menu {
    private $show_children = true;
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        if ($depth === 0) {
            $this->show_children = in_array('current-menu-item', $item->classes) ||
                                   in_array('current-menu-ancestor', $item->classes) ||
                                   in_array('current-menu-parent', $item->classes);
            if(!$this->show_children){
                $item->classes = array_diff($item->classes, ['current-menu-item', 'current-menu-ancestor', 'current-menu-parent', 'menu-item-has-children']);
            }
        }
        if ($depth === 1 && !$this->show_children) {
            return;
        }
        parent::start_el($output, $item, $depth, $args, $id);
    }
}

//-----------------------------------------------------
// Activation de la suppression des sous-menus en fonction de la page courante
//-----------------------------------------------------

function custom_nav_menu_args($args) {
    if (!is_front_page() && get_option('generate_child_silo_menus') && 'primary' === $args['theme_location']) {
        $args['walker'] = new Custom_Walker_Nav_Menu();
    }
    return $args;
}
add_filter('wp_nav_menu_args', 'custom_nav_menu_args');

//-----------------------------------------------------
// Redirection vers le dernier post via une variable d'url ou slug
//-----------------------------------------------------

add_action('wp', 'generateperf_redirect_to_last_post', 1);
function generateperf_redirect_to_last_post() {
    global $wp;
    if(isset($_GET['last_post']) || add_query_arg( array(), $wp->request ) === 'last_post') {
        $args = array(
            'numberposts' => 1,
            'post_status' => 'publish',
        );
        $dernier_post = wp_get_recent_posts($args);
        if(!empty($dernier_post)) {
            $post_url = get_permalink( $dernier_post[0]['ID'] );
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
            wp_redirect($post_url, 301);
            exit;
        }
    }
}

//-----------------------------------------------------
// Redirection vers un post aléatoire via une variable d'url ou slug
//-----------------------------------------------------

add_action('wp', 'generateperf_redirect_to_random_post', 1);
function generateperf_redirect_to_random_post() {
    global $wp;
    if(isset($_GET['random_post']) || add_query_arg( array(), $wp->request ) === 'random_post') {
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => 1,
            'orderby' => 'rand',
            'fields' => 'ids',
        );
        $random_post = get_posts($args);
        if (!empty($random_post)) {
            $post_url = get_permalink( $random_post[0] );
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
            wp_redirect($post_url, 301);
            exit;
        }
    }
}

//-----------------------------------------------------
// Modification des slugs des posts après une date
//-----------------------------------------------------

add_filter( 'post_link', 'generatepress_child_conditional_custom_permalink', 10, 2 );
function generatepress_child_conditional_custom_permalink( $permalink, $post ) {
    if( get_option('generate_child_optimal_post_slugs') ){
        $post_datetime = new DateTime( $post->post_date );
        $swap_datetime = DateTime::createFromFormat( 'Y-m-d\TH:i', get_option('generate_child_optimal_post_slugs') );
        if ( $post_datetime > $swap_datetime ) {
            $category = get_the_category( $post->ID );
            $permalink = user_trailingslashit( home_url( $category[0]->slug . '/' . $post->ID . '-' . $post->post_name . '/' ) ) ;
        }
    }
    return $permalink;
}

//-----------------------------------------------------
// Mise en place des redirections si nécessaire
//-----------------------------------------------------

add_action( 'init', 'generatepress_child_conditional_custom_permalink_rewrite' );
function generatepress_child_conditional_custom_permalink_rewrite() {
    if( get_option('generate_child_optimal_post_slugs') ){
        add_rewrite_rule( '([a-z0-9-]+)/([0-9]+)-([a-z0-9-]+)/?$', 'index.php?post_type=post&p=$matches[2]', 'top' );
    }
}

//-----------------------------------------------------
// Affichage des catégories et tags au-dessus des titres
//-----------------------------------------------------

add_action('generate_before_entry_title', 'generatepress_child_display_top_taxonomies', 0);

function generatepress_child_display_top_taxonomies() {
    if(is_single() && 'post' == get_post_type() && (get_option('generate_child_cats_above_titles') || get_option('generate_child_tags_above_titles'))){
        echo '<div class="badges-container">';
        if(get_option('generate_child_cats_above_titles')){
            echo get_the_category_list();
        }
        if(get_option('generate_child_tags_above_titles')){
            echo get_the_tag_list('<ul class="post-tags"><li>', '</li><li>', '</li></ul>');
        }
        echo '</div>';
    }
}

//-----------------------------------------------------
// Affichage du bouton Google News sous le contenu des articles
//-----------------------------------------------------

add_filter( 'generate_after_content', 'generate_child_googlenews_display_button', 6);
function generate_child_googlenews_display_button ( $content ) {
    if ( is_single() && 'post' == get_post_type() && get_option('generate_child_ggnews_url') ) {
        $content = '<div class="component">';
        $content .= generateperf_separator();
        $content .= '<div class="google-news-card">
        <p>'.sprintf(__('%s is an independent media. Support us by adding us to your Google News favorites:', 'generateperf'), get_bloginfo('name')).'</p>
        <div class="google-news-button-container">
        <a class="simple" href="'.esc_attr(get_option('generate_child_ggnews_url')).'" target="_blank" rel="external noopener">'.__('Follow us on Google News', 'generateperf').' '.generate_child_get_svg_icon('google-news', 33).'</a>
        </div></div>';
        $content .= '</div>';
        echo $content;
    }
}

//-----------------------------------------------------
// Pas de navigation sur les articles (précédent, suivant) et la home
//-----------------------------------------------------

add_action('wp_head', 'generatepress_child_no_nav', 2);
function generatepress_child_no_nav()
{
    if (is_front_page()) {
        add_filter('generate_show_post_navigation', '__return_false');
    }
}

//-----------------------------------------------------
// Gestion des liens en pagination (hausse du nombre par défaut)
//-----------------------------------------------------

add_filter('generate_pagination_mid_size', 'generatepress_child_increase_pagination');
function generatepress_child_increase_pagination()
{
    return 5;
}

//-----------------------------------------------------
// Pas de prise en compte des pages via <!-- more -->
//-----------------------------------------------------

add_filter('generate_more_tag', '__return_false');

//-----------------------------------------------------
// Affichage des tags relatifs dans les archives de tags / Optimisé le 21/09/2023
//-----------------------------------------------------

add_filter('generate_after_main_content', 'generate_child_display_related_tags_on_archives', 11);

function generate_child_display_related_tags_on_archives() {
    if (!is_tag() || !get_option('generate_child_tags_related_in_archives')) {
        return;
    }
    $default_tag = get_query_var('tag_id');
    if (false === ($related_posts_tags = get_transient('related_tags_' . $default_tag))) {
        $args = [
            'post_type'      => 'post',
            'tag_id'         => $default_tag,
            'posts_per_page' => 20,
            'fields'         => 'ids'
        ];
        $query = new WP_Query($args);
        $post_ids = $query->posts;
        if (!empty($post_ids)) {
            $all_tags = wp_get_object_terms($post_ids, 'post_tag', ['fields' => 'ids']);
            $related_posts_tags = array_diff(array_unique($all_tags), [$default_tag]);
            set_transient('related_tags_' . $default_tag, $related_posts_tags, 5 * DAY_IN_SECONDS);
        }
        wp_reset_postdata();
    }
    if (!empty($related_posts_tags)) {
        echo '<div class="related-tags"><h2>' . __('See also', 'generateperf') . '</h2><ul class="splitted-list" data-columns="4">';
        foreach ($related_posts_tags as $term_id) {
            $term = get_term($term_id, 'post_tag');
            if ($term && !is_wp_error($term)) {
                echo '<li><a href="' . get_tag_link($term->term_id) . '">' . esc_html($term->name) . '</a></li>';
            }
        }
        echo '</ul></div>';
    }
}

//-----------------------------------------------------
// Affichage des catégories enfant dans les catégories / Optimisé le 21/09/2023
//-----------------------------------------------------

add_action('generate_after_archive_title', 'generatepress_child_archives_children', 5);

function generatepress_child_archives_children() {
    if (!is_category() || !get_option('generate_child_categories_children')) {
        return;
    }
    $page = max(1, get_query_var('paged', 1));
    if ($page !== 1) {
        return;
    }
    $current_category = get_queried_object();
    $args = [
        'parent'     => $current_category->term_id,
        'orderby'    => 'name',
        'order'      => 'ASC',
        'hide_empty' => true,
        'fields'     => 'id=>name'
    ];
    $categories = get_categories($args);
    if (!empty($categories)) {
        echo '<ul class="splitted-list" data-columns="4">';
        foreach ($categories as $term_id => $cat_name) {
            $url = esc_url(get_category_link($term_id));
            $name = esc_html($cat_name);
            echo '<li><a href="'.$url.'">'.$name.'</a></li>';
        }
        echo '</ul>';
    }
}

//-----------------------------------------------------
// Affichage des descriptions d'archives sur la première page uniquement et éventuellement en bas / Optimisé le 21/09/2023
//-----------------------------------------------------

add_action('wp', 'generateperf_custom_archive_descriptions');

function generateperf_custom_archive_descriptions() {
    if (!is_category() && !is_tag()) {
        return;
    }
    $page = max(1, get_query_var('paged', 1));
    if ($page !== 1) {
        return;
    }
    remove_action('generate_after_archive_title', 'generate_do_archive_description');
    if (get_option('generate_child_categories_move_description')) {
        add_action('generate_after_main_content', 'generateperf_custom_archive_title');
        add_action('generate_after_main_content', 'generate_do_archive_description');
    } else {
        add_action('generate_after_archive_title', 'generate_do_archive_description');
    }
}

function generateperf_custom_archive_title() {
    if (get_the_archive_description()) {
        echo '<h2>' . sprintf(__('More information on “%1$s”', 'generateperf'), single_term_title('', false)) . '</h2>';
    }
}

//-----------------------------------------------------
// Affichage des flux RSS des catégories et tags sur les articles
//-----------------------------------------------------

add_action('wp_head', 'generatepress_child_categories_rss_display', 2);

function generatepress_child_categories_rss_display() {
    if (!is_single() || !get_option('generate_child_tags_and_cats_rss_links')) {
        return;
    }
    $output = '';
    $categories = get_the_category();
    if (!empty($categories)) {
        foreach ($categories as $category) {
            $output .= sprintf(
                '<link rel="alternate" type="application/rss+xml" title="%s &raquo; Flux" href="%s" />' . "\n",
                esc_attr($category->name),
                esc_url(get_category_feed_link($category->term_id))
            );
        }
    }
    $tags = get_the_tags();
    if (!empty($tags)) {
        foreach ($tags as $tag) {
            $output .= sprintf(
                '<link rel="alternate" type="application/rss+xml" title="%s &raquo; Flux" href="%s" />' . "\n",
                esc_attr($tag->name),
                esc_url(get_tag_feed_link($tag->term_id))
            );
        }
    }
    echo $output;
}

//-----------------------------------------------------
// Pas de shortlink (mieux d'un point de vue SEO)
//-----------------------------------------------------

add_filter('after_setup_theme', 'generatepress_child_remove_shortlink');
function generatepress_child_remove_shortlink()
{
    remove_action('wp_head', 'wp_shortlink_wp_head', 10);
    remove_action('template_redirect', 'wp_shortlink_header', 11);
}

//-----------------------------------------------------
// Pas de flux RSS dans le header à part le /feed/ global
//-----------------------------------------------------

add_action('after_setup_theme', 'generatepress_child_remove_extra_feeds');
function generatepress_child_remove_extra_feeds()
{
    add_filter('feed_links_show_comments_feed', '__return_false');
    remove_action('wp_head', 'feed_links_extra', 3);
}

//-----------------------------------------------------
// On renvoie des 404 en cas d'appel des flux RSS de commentaires
//-----------------------------------------------------

add_action('template_redirect', 'generatepress_child_disable_comments_feeds', 1);
function generatepress_child_disable_comments_feeds()
{
    if (is_comment_feed()) {
        global $wp_query;
        $wp_query->is_feed = false;
        $wp_query->set_404();
        status_header('404');
        wp_die(__('RSS comment feeds are deactivated.', 'generateperf') . ' <a href="' . get_bloginfo('url') . '">' . __('Back to home page', 'generateperf') . '</a>', '', 404);
        exit;
    }
}

//-----------------------------------------------------
// On renvoie des 404 en cas d'appel des flux RSS de recherche
//-----------------------------------------------------

add_action('pre_get_posts', 'generatepress_child_disable_search_feeds', 1);
function generatepress_child_disable_search_feeds()
{
    global $query;
    if (is_feed() && substr($_SERVER['REQUEST_URI'], 0, 8) === '/search/') {
        /* wp_redirect(home_url(), 301); */
        global $wp_query;
        $wp_query->is_feed = false;
        $wp_query->set_404();
        status_header('404');
        wp_die(__('RSS search feeds are deactivated', 'generateperf') . ' <a href="' . get_bloginfo('url') . '">' . __('Back to home page', 'generateperf') . '</a>', '', 404);
        exit;
    }
}

//-----------------------------------------------------
// Pas de lien sur le logo si lien textuel déjà présent ou page d'accueil
//-----------------------------------------------------

add_filter('generate_logo_output', 'generatepress_child_no_logo_link', 11, 2);
function generatepress_child_no_logo_link($output, $logo_url)
{
    if(generate_has_logo_site_branding() || is_front_page()){
        printf('<div class="site-logo">%1$s</div>', $output);
    } else {
        printf(
            '<div class="site-logo"><a href="%1$s" title="%2$s" rel="home" aria-label="%3$s">%4$s</a></div>',
            esc_url( apply_filters( 'generate_logo_href' , home_url( '/' ) ) ),
            esc_attr( apply_filters( 'generate_logo_title', get_bloginfo( 'name', 'display' ) ) ),
            __('Back to home page', 'generateperf'),
            $output
        );
    }
}

//-----------------------------------------------------
// Affichage avancé des auteurs + suppression éventuelle du lien
//-----------------------------------------------------

add_filter('generate_post_author_output', 'generatepress_child_no_author_link', 1, 1);
function generatepress_child_no_author_link($output)
{
    if( !is_singular() ) return $output;

    $labels = array(
        'written' => __('Written by', 'generateperf'),
        'composed' => __('Composed by', 'generateperf'),
        'authored' => __('Authored by', 'generateperf'),
        'crafted' => __('Crafted by', 'generateperf'),
        'scribed' => __('Scribed by', 'generateperf'),
        'conceived' => __('Conceived by', 'generateperf'),
    );

    if(get_option('generate_child_author_link')) {
        $template = '<span class="author vcard" itemtype="http://schema.org/Person" itemscope="itemscope" itemprop="author">%1$s <a class="url fn n" href="%3$s" title="%4$s" rel="author"><span class="fn n author-name" itemprop="name">%2$s</span></a></span>';
    } else {
        $template = '<span class="author vcard" itemtype="http://schema.org/Person" itemscope="itemscope" itemprop="author">%1$s <span class="fn n author-name" itemprop="name">%2$s</span></span>'; 
    }

    printf(
        '<span class="byline">'.generateperf_splitter().' %1$s</span>',
        sprintf(
        $template,
        $labels[get_option('generate_child_author_write_action_name', 'written')],
        esc_html(get_the_author()),
        esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
        esc_attr( sprintf( __( 'View all posts by %s', 'generatepress' ),
            get_the_author() ) ),
            esc_html( get_the_author() ),
        )
    );
}

//-----------------------------------------------------
// Pas de lien sur les auteurs des commentaires
//-----------------------------------------------------

add_filter('get_comment_author_link', 'generatepress_child_disable_comment_author_links');
function generatepress_child_disable_comment_author_links($author_link)
{
    return preg_replace('#<a.*?>([^>]*)</a>#i', '$1', $author_link);
}

//-----------------------------------------------------
// Activation par défaut du fil d'ariane Yoast
//-----------------------------------------------------

add_action('after_setup_theme', 'generatepress_child_yoast_breadcrumbs');
function generatepress_child_yoast_breadcrumbs()
{
    add_theme_support('yoast-seo-breadcrumbs');
}

//-----------------------------------------------------
// Désactiver les notifications de mises à jour de Yoast SEO
//-----------------------------------------------------

add_filter('wpseo_update_notice_content', '__return_null');

//-----------------------------------------------------
// Noindex sur les pages de pagination et recherche via Yoast SEO
//-----------------------------------------------------

add_filter('wpseo_robots', 'generatepress_child_nofollow_exceptions', PHP_INT_MAX);
function generatepress_child_nofollow_exceptions($robots)
{
    if (is_paged() || is_search()) {
        $robots = 'noindex, follow';
    }
    return $robots;
}

//-----------------------------------------------------
// On teste si le visiteur est un robot ou un humain
//-----------------------------------------------------

function generatepress_child_is_robot()
{
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if (preg_match('/crawl|bot|spider|abacho|accona|addthis|alexa|altavista|anthill|appie|arale|araneo|ariadne|arks|aspseek|atn_worldwide|atomz|baidu|bing|bjaaland|blackwidow|calif|chinaclaw|cmc|combine|contaxe|cosmos|curl|cusco|cyberspyder|dataprovider|digger|downloadexpress|dwcp|ebiness|ecollector|esculapio|esi|esther|estyle|ezooms|facebookexternalhit|facebook|fdse|felix ide|fetch|fido|find|firefly|fouineur|froogle|gazz|gcreep|geona|getterrobo-plus|get|golem|\-google|grabber|grabnet|griffon|gromit|gulliver|gulper|havindex|hotwired|htdig|httrack|ia_archiver|informant|infoseek|ingrid|inktomi|inspectorwww|iron33|jeeves|jobo|kdd\-explorer|kit\-fireball|label\-grabber|larbin|legs|libwww-perl|linkedin|linkidator|linkwalker|lockon|lycos|m2e|majesticseo|marvin|mattie|mediafox|mediapartners|merzscope|mod_pagespeed|moget|motor|muncher|muninn|muscatferret|mwdsearch|nationaldirectory|nec\-meshexplorer|netcraftsurveyagent|netscoop|netseer|newscan\-online|nil|none|nutch|objectssearch|occam|packrat|pageboy|parasite|patric|pegasus|phpdig|piltdownman|pimptrain|pingdom|pinterest|plumtreewebaccessor|rambler|raven|rhcs|roadrunner|robbie|robi|robofox|scooter|scrubby|search\-au|searchprocess|search|senrigan|shagseeker|sharp\-info\-agent|sift|site valet|sitesucker|skymob|slurp|snooper|speedy|suke|tach_bw|technoratisnoop|templeton|teoma|titin|topiclink|twitter|udmsearch|ukonline|unwindfetchor|urlck|urlresolver|valkyrie libwww\-perl|victoria|voyager|webbandit|webcatcher|webcopier|webleacher|webmechanic|webmoose|webquest|webreaper|webs|webwalker|webzip|wget|whowhere|winona|wlm|wolp|wwwc|xget|xing|yahoo|yandex|yeti|zeus/', $user_agent)
    ) {
        return true;
    }
    return false;
}

//-----------------------------------------------------
// On soulage le serveur en réduisant le crawl de certains Bots
//-----------------------------------------------------

add_filter( 'robots_txt', 'generatepress_child_bad_bots_directives', 11, 2 );
function generatepress_child_bad_bots_directives( $output, $public ) {
	if ( '0' != $public ) {
        $output .= "\n";
		$bot_agents = array(
			'8LEGS',
			'AhrefsBot',
			'AspiegelBot',
			'BLEXBot',
			'Barkrowler',
			'DotBot',
			'MJ12bot',
			'MauiBot',
			'Nimbostratus-Bot',
			'PetalBot',
			'SemrushBot',
			'SeznamBot',
			'Sogou',
			'serpstatbot',
			'trendiction',
            'TextBulkerBot',
		);
		foreach($bot_agents as $bot_agent){
			$output	 .= 'User-agent: '.$bot_agent."\n";
		}
		$output	 .= 'Crawl-delay: 180'."\n";
		$output	 .= 'Disallow: /wp-admin/'."\n";

        $block_agents = array(
            'GPTBot',
            'Google-Extended',
        );
		foreach($block_agents as $block_agent){
			$output	 .= 'User-agent: '.$block_agent."\n";
		}
		$output	 .= 'Disallow: /'."\n";

	}
	return $output;
}

//-----------------------------------------------------
// Désactivation du balisage sémantique par défaut
//-----------------------------------------------------

/* add_filter( 'generate_schema_type', '__return_false' ); */

//-----------------------------------------------------
// Canonical sur les pages de pagination via Yoast SEO
//-----------------------------------------------------

/*
add_filter('wpseo_canonical', 'generatepress_child_canonical_exceptions');
function generatepress_child_canonical_exceptions($canonical)
{
    if (is_paged()) {
        return '';
    }
    else {
        return $canonical;
    }
}
*/

//-----------------------------------------------------
// Désactivation complète des catégories
//-----------------------------------------------------

/*
add_action('init', 'generatepress_child_unregister_categories');
function generatepress_child_unregister_categories()
{
    register_taxonomy('category', array());
    unregister_widget('WP_Widget_Categories');
}
*/

//-----------------------------------------------------
// Remplacement date de publication de Yoast SEO
//-----------------------------------------------------

/*
add_filter(
    'wpseo_frontend_presenter_classes',
    function ( $filter ) {

	if (($key = array_search('Yoast\WP\SEO\Presenters\Open_Graph\Article_Published_Time_Presenter', $filter)) !== false) {
		unset($filter[$key]);
	}

	return $filter;
    }
); 

add_action( 'wpseo_opengraph', 'awp_remove_publish_date' );
function awp_remove_publish_date() {
    add_action( 'wpseo_opengraph', 'awp_opengraph_rag', 13 );
}
function awp_opengraph_rag() {
   echo '	<meta property="article:published_time" content="'.get_the_time('Y-m-d\TH:i:sP', get_the_ID()).'">'."\n";
}
*/