<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Affichage de la première lettre en majuscule
//-----------------------------------------------------

function generateperf_ucfirst_first_letter($string) {
    $first_letter = mb_strtoupper(mb_substr($string, 0, 1, 'UTF-8'), 'UTF-8');
    return $first_letter . mb_substr($string, 1, null, 'UTF-8');
}

//-----------------------------------------------------
// Affichage de séparateurs de texte, utile dans les fonctions de Layout
//-----------------------------------------------------

function generateperf_splitter() {
    return '<span class="o50">&middot;</span>';
}

//-----------------------------------------------------
// Affichage de séparateurs, utile dans les fonctions de Layout
//-----------------------------------------------------

function generateperf_separator() {
    return '<div class="separator"></div>';
}

//-----------------------------------------------------
// Classe sticky Sidebar sur le body si nécessaire
//-----------------------------------------------------

add_filter('body_class', function ($classes) {
    if ( get_option('generate_child_sticky_sidebar') ) {
        return array_merge( $classes, array('sticky-sidebar') );
    }
    return $classes;
});

//-----------------------------------------------------
// Pas d'excerpt sur le site, seulement dans les flux RSS
//-----------------------------------------------------

add_filter('the_excerpt', 'generatepress_child_custom_short_excerpt');
function generatepress_child_custom_short_excerpt($excerpt)
{
    global $wp_query;
    if (
        is_feed() || 
        (is_search() && defined('RELEVANSSI_PREMIUM')) ||
        (is_singular() && get_option('generate_child_activate_subtitles') === 'excerpt' && $wp_query->is_main_query())
        ) {
        return $excerpt;
    } else {
        return '';
    }
}

//-----------------------------------------------------
// Pas de bouton "Lire plus" dans les archives
//-----------------------------------------------------

add_filter('excerpt_more', 'generatepress_child_excerpt_more_link', 50);
function generatepress_child_excerpt_more_link()
{
    return '...';
}

//-----------------------------------------------------
// Génération de dates relatives avec human_time_diff() / Optimisé le 21/09/2023
//-----------------------------------------------------

function generatepress_child_relative_post_the_date($timestamp) {
    $time_diff = human_time_diff($timestamp, current_time('timestamp'));
    $result = '';
    switch ($time_diff) {
        case '1 semaine':
            $result = __('Last week', 'generateperf');
            break;
        case '1 mois':
            $result = __('Last month', 'generateperf');
            break;
        case '1 an':
            $result = __('Last year', 'generateperf');
            break;
        default:
            $result = sprintf(__('%s ago', 'generateperf'), $time_diff);
            break;
    }
    return $result;
}

//-----------------------------------------------------
// Afficher des dates de publication et mise à jour relatives / Optimisé le 21/09/2023
//-----------------------------------------------------

add_filter('get_the_date', 'generatepress_child_relative_post_date', 10, 3);

function generatepress_child_relative_post_date($the_date, $d, $post) {
    if (!get_option('generate_child_relative_dates') || $d !== '') {
        return $the_date;
    }
    $post_time = new DateTime(get_post_time('c', true, $post));
    $current_time = new DateTime('now', new DateTimeZone(get_option('timezone_string') ?: 'UTC'));
    $interval = $post_time->diff($current_time)->format('%a');
    if ($interval == 0) {
        return sprintf('%s %s %s', __('Today', 'generateperf'), __('at', 'generateperf'), get_post_time(get_option('time_format'), false, $post, true));
    }
    if ($interval == 1) {
        return __('Yesterday', 'generateperf');
    }
    return generatepress_child_relative_post_the_date(get_the_time('U', $post));
}

//-----------------------------------------------------
// Affichage en colonnes pour tous les types d'archives
//-----------------------------------------------------

add_filter('generate_blog_columns', 'generatepress_child_adjust_columns');
function generatepress_child_adjust_columns($columns)
{
    if (is_post_type_archive() || is_tax()) {
        return true;
    } elseif (is_404()) {
        return false;
    }
    return $columns;
}

//-----------------------------------------------------
// Affichage d'une sidebar sur certains types de contenus
//-----------------------------------------------------

/*
add_filter( 'generate_sidebar_layout', 'eneratepress_child_custom_post_sidebar_layout' );
function eneratepress_child_custom_post_sidebar_layout( $layout ) {
    $post_types = array( 'post' );
    if ( in_array( get_post_type(), $post_types ) ) {
        return 'right-sidebar';
    }
    return $layout;
}
*/

//-----------------------------------------------------
// Suppression du type de page d'archive devant le titre
//-----------------------------------------------------

add_filter('get_the_archive_title_prefix', '__return_empty_string');

//-----------------------------------------------------
// Chargement manuel du CSS des articles relatifs
//-----------------------------------------------------

add_action('wp_enqueue_scripts', 'generatepress_child_articles_relatifs_css', 10);
function generatepress_child_articles_relatifs_css()
{
    if (get_option('generate_child_similar') && is_single() && 'post' == get_post_type()) {
        if (function_exists('wp_should_load_separate_core_block_assets') && wp_should_load_separate_core_block_assets()) {
            wp_enqueue_style('wp-block-latest-posts', get_site_url() . '/wp-includes/blocks/latest-posts/style.min.css', array(), gp_c_version);
        }
    }
}

//-----------------------------------------------------
// Récupération d'une taxonomie en commun entre deux posts / Optimisé le 20/09/2023
//-----------------------------------------------------

function generateperf_get_common_taxonomy_term($post_id_1, $post_id_2) {
    $tags_1 = wp_get_post_tags($post_id_1, array('fields' => 'ids'));
    $tags_2 = wp_get_post_tags($post_id_2, array('fields' => 'ids'));
    $common_tags = array_intersect($tags_1, $tags_2);
    if (!empty($common_tags)) {
        $first_common_tag_id = reset($common_tags);
        $tag = get_tag($first_common_tag_id);
        return generateperf_ucfirst_first_letter($tag->name);
    }
    $categories_1 = wp_get_post_categories($post_id_1, array('fields' => 'ids'));
    $categories_2 = wp_get_post_categories($post_id_2, array('fields' => 'ids'));
    $common_categories = array_intersect($categories_1, $categories_2);
    if (!empty($common_categories)) {
        $first_common_category_id = reset($common_categories);
        $category = get_category($first_common_category_id);
        return generateperf_ucfirst_first_letter($category->name);
    }
    return false;
}

//-----------------------------------------------------
// Génération des contenus similaires // Optimisé le 03/10/2023
//-----------------------------------------------------

add_action('template_redirect', 'generatepress_child_articles_relatifs_array', 20);
function generatepress_child_articles_relatifs_array() {

    if (!is_single() || 'post' !== get_post_type()) {
        return;
    }

    $active_locations = json_decode(stripslashes(get_option('generate_child_similar')), true);

    if (empty($active_locations)) {
        return;
    }

    global $related_articles;

    $display_nb = count($active_locations) * 6;
    $related_articles = [];
    $post_id = get_the_ID();

    $args_base = [
        'post_type' => 'post',
        'post__not_in' => [$post_id],
        'tax_query' => [],
        'no_found_rows' => true,
        'fields' => 'ids',
        'update_post_term_cache' => true,
        'update_post_meta_cache' => false,
        'orderby' => ['date' => 'DESC'],
    ];

    $taxonomies = ['post_tag' => get_the_tags(), 'category' => get_the_category()];
    $found_posts = [];
    
    foreach($taxonomies as $tax => $terms) {
        if (!$terms) {
            continue;
        }

        $args = $args_base;
        $args['posts_per_page'] = $display_nb - count($found_posts);
        $args['tax_query'] = [
            [
                'taxonomy' => $tax,
                'field' => 'term_id',
                'terms' => wp_list_pluck($terms, 'term_id'),
            ],
        ];

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $found_posts = array_merge($found_posts, $query->posts);
            $args_base['post__not_in'] = array_merge($args_base['post__not_in'], $query->posts);
        }
    }

    generatepress_child_related_articles_store('set', $found_posts);
    wp_reset_query();
}

//-----------------------------------------------------
// Fonction de gestion des articles relatifs / Optimisé le 20/09/2023
//-----------------------------------------------------

function generatepress_child_related_articles_store($action = 'get', $value = '') {
    static $related_articles;

    switch ($action) {
        case 'set':
            if (is_array($value)) {
                $related_articles = $value;
            }
            break;
        case 'get':
            if (is_array($related_articles)) {
                return current($related_articles);
            }
            break;
        case 'reset':
            if (is_array($related_articles)) {
                reset($related_articles);
            }
            break;
        case 'next':
            if (is_array($related_articles)) {
                $current = current($related_articles);
                next($related_articles);
                return $current;
            }
            break;
    }
    return false;
}

//-----------------------------------------------------
// Affichage des contenus similaires dans les articles
//-----------------------------------------------------

add_filter('the_content', 'generatepress_child_related_articles_inside_content', 99999999);
function generatepress_child_related_articles_inside_content($content)
{
    $active_locations = json_decode(stripslashes(get_option('generate_child_similar')), true);
    if (is_single() && 'post' == get_post_type() && is_array($active_locations) && array_key_exists('inside_content', $active_locations)) {

        $nesting = 0;
        $paragraphs = str_contains($content, '</p>') ? explode('</p>', $content) : array();
        generatepress_child_related_articles_store('reset');

        $total_paragraphs = count($paragraphs);
        $max_insertions = 6;
        $minimum_split = 4;
        $options = [
            'read' => __('To read', 'generateperf'),
            'view' => __('To see', 'generateperf'),
            'discover' => __('To discover', 'generateperf'),
            'explore' => __('To explore', 'generateperf'),
            'browse' => __('To browse', 'generateperf'),
            'trendy' => __('Trendy', 'generateperf'),
        ];

        if ($total_paragraphs > $minimum_split) {

            $insert_after_x = $total_paragraphs > ($max_insertions * $minimum_split) ? ceil($total_paragraphs / $max_insertions) : $minimum_split;
            $i = 1;
            
            foreach ($paragraphs as $p_key => $paragraph) {
                if(str_contains($paragraph, '<blockquote') || str_contains($paragraph, '<table')) ++$nesting;
                if($nesting > 0){
                    if(str_contains($paragraph, '</blockquote') || str_contains($paragraph, '</table')) --$nesting;
                    continue;
                }
                
                if ($i % $insert_after_x == 0 && $max_insertions > 0) {
                    $current_article = generatepress_child_related_articles_store('next');
                    if ($current_article) {

                        if(get_option('generate_child_related_texts_in_text') === 'dynamic'){
                            $label = generateperf_get_common_taxonomy_term(get_the_ID(), $current_article);
                        } else {
                            $label = $options[get_option('generate_child_related_texts_in_text', 'read')];
                        }

                        $paragraphs[$p_key] = '</p><p><a class="related-article simple" href="'.get_permalink($current_article).'">
                        <span class="label">'.$label.'</span>
                        <span class="title">'.get_the_title($current_article).'</span>
                        '.generate_child_get_svg_icon('chevron-right', 24).'
                        </a></p>' . $paragraph;
                        
                        $max_insertions--;
                    }
                }
                $i++;
            }
            return implode('', $paragraphs);
        }
    }
    return $content;
}

//-----------------------------------------------------
// Affichage des contenus similaires sous les articles // Optimisé le 03/10/2023
//-----------------------------------------------------

add_action('generate_after_content', 'generatepress_child_related_articles_after_content', 20);
function generatepress_child_related_articles_after_content()
{
    if (!is_single() || 'post' !== get_post_type()) {
        return;
    }

    $active_locations = json_decode(stripslashes(get_option('generate_child_similar')), true);

    if (!is_array($active_locations) || !array_key_exists('after_content', $active_locations)) {
        return;
    }

    $current_article = generatepress_child_related_articles_store('get');

    if (false === $current_article) {
        return;
    }

    $cat_name = '';
    $terms_types = [get_the_category(), get_the_tags()];
    foreach ($terms_types as $terms) {
        if (is_array($terms) && count($terms)) {
            $cat_name = ' ' . sprintf(__('in “%1$s”', 'generateperf'), $terms[0]->name);
            break;
        }
    }

    $labels = [
        'view' => __('See also', 'generateperf'),
        'other' => __('Other articles', 'generateperf'),
        'topics' => __('Related topics', 'generateperf'),
        'also' => __('Also published', 'generateperf'),
        'publications' => __('Other publications', 'generateperf'),
        'previous' => __('Previous publications', 'generateperf'),
        'like' => __('You will also like', 'generateperf'),
        'currently' => __('Currently', 'generateperf'),
    ];

    $selected_label = $labels[get_option('generate_child_related_texts', 'view')] . $cat_name;

    echo '<nav class="related-articles">';
    echo '<h2 class="simple">' . $selected_label . '</h2>';
    echo '<ul class="wp-block-latest-posts__list is-grid columns-2 has-dates wp-block-latest-posts">';

    for ($incr = 0; $incr < 6; ++$incr) {

        $current_article = generatepress_child_related_articles_store('next');
        if (false === $current_article) {
            break;
        }

        $thumbnail = get_the_post_thumbnail($current_article, 'medium');
        $permalink = get_permalink($current_article);
        $title = get_the_title($current_article);
        $date_iso = get_the_date('c', $current_article);
        $date_readable = get_the_date('', $current_article);

        echo '<li>';
        echo '<figure class="wp-block-latest-posts__featured-image">' . $thumbnail . '</figure>';
        echo '<a class="simple" href="' . $permalink . '">' . $title . '</a>';
        echo '<time datetime="' . $date_iso . '" class="wp-block-latest-posts__post-date">' . $date_readable . '</time>';
        echo '</li>';
    }

    echo '</ul></nav>';
}

//-----------------------------------------------------
// Affichage des contenus similaires dans la sidebar // Optimisé le 03/10/2023
//-----------------------------------------------------

add_action('generate_before_right_sidebar_content', 'generatepress_child_related_articles_sidebar', 20);
function generatepress_child_related_articles_sidebar()
{
    if (!is_single() || 'post' !== get_post_type()) {
        return;
    }

    $active_locations = json_decode(stripslashes(get_option('generate_child_similar')), true);

    if (!is_array($active_locations) || !array_key_exists('sidebar', $active_locations)) {
        return;
    }

    $current_article = generatepress_child_related_articles_store('get');
    if (false === $current_article) {
        return;
    }

    echo '<nav class="related-articles">';
    echo '<h2 class="simple">' . __('Not to be missed', 'generateperf') . '</h2>';
    echo '<ul class="wp-block-latest-posts__list has-dates wp-block-latest-posts">';

    for ($incr = 0; $incr < 6; ++$incr) {
        $current_article = generatepress_child_related_articles_store('next');
        if (false === $current_article) {
            break;
        }

        $thumbnail = get_the_post_thumbnail($current_article, 'medium');
        $permalink = get_permalink($current_article);
        $title = get_the_title($current_article);
        $date_iso = get_the_date('c', $current_article);
        $date_readable = get_the_date('', $current_article);

        echo '<li>';
        echo '<figure class="wp-block-latest-posts__featured-image">' . $thumbnail . '</figure>';
        echo '<a class="simple" href="' . $permalink . '">' . $title . '</a>';
        echo '<time datetime="' . $date_iso . '" class="wp-block-latest-posts__post-date">' . $date_readable . '</time>';
        echo '</li>';
    }

    echo '</ul></nav>';
}

//-----------------------------------------------------
// Affichage des post types similaires de même niveau
//-----------------------------------------------------

add_action('generate_after_content', 'generatepress_child_related_pages_after_content', 20);
function generatepress_child_related_pages_after_content()
{
    if (generate_child_option_active_on_current_page('generate_child_similar_post_types') && !is_front_page()) {

        $exclusions = array(
            get_the_ID(),
            get_option( 'page_on_front' ),
            get_option( 'wp_page_for_privacy_policy' ),
        );

        $args = array(
            'posts_per_page' => 6,
            'post_type' => get_post_type(),
            'post__not_in' => $exclusions,
            'no_found_rows' => true,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
            'orderby' => array(
                'date' => 'DESC',
            ),
        );

        $parent_id = wp_get_post_parent_id();
        if($parent_id){
            $args['post_parent'] = $parent_id;
        }

        $query = new WP_Query($args);

        if ($query->have_posts()) {

            $label = array(
                'view' => __('See also', 'generateperf'),
                'other' => __('Other articles', 'generateperf'),
                'topics' => __('Related topics', 'generateperf'),
                'also' => __('Also published', 'generateperf'),
                'publications' => __('Other publications', 'generateperf'),
                'like' => __('You will also like', 'generateperf'),
            );

            echo '<nav class="related-articles">';
            echo '<h2 class="simple">'.$label[get_option('generate_child_related_texts', 'view')].'</h2>';
            echo '<ul class="wp-block-latest-posts__list is-grid columns-3 has-dates wp-block-latest-posts">';

            while ($query->have_posts()) {
                $query->the_post();

                echo '<li><figure class="wp-block-latest-posts__featured-image">' . get_the_post_thumbnail(get_the_ID(), 'medium') . '</figure>';
                echo '<a class="simple" href="' . get_permalink() . '">' . get_the_title() . '</a>';
                echo '<time datetime="' . get_the_date('c', get_the_ID()) . '" class="wp-block-latest-posts__post-date">' . get_the_date('', get_the_ID()) . '</time>';
                echo '</li>';
            }

            echo '</ul></nav>';

        }
    }
}

//-----------------------------------------------------
// Chargement manuel du CSS des articles relatifs
//-----------------------------------------------------

add_action('wp_enqueue_scripts', 'generatepress_child_articles_relatifs_css_pages', 10);
function generatepress_child_articles_relatifs_css_pages()
{
    if (generate_child_option_active_on_current_page('generate_child_similar_post_types') && !is_front_page()) {
        if (function_exists('wp_should_load_separate_core_block_assets') && wp_should_load_separate_core_block_assets()) {
            wp_enqueue_style('wp-block-latest-posts', get_site_url() . '/wp-includes/blocks/latest-posts/style.min.css', array(), gp_c_version);
        }
    }
}

//-----------------------------------------------------
// Services de partage social
//-----------------------------------------------------

function generatepress_child_social_share_services()
{
    return array(
    'facebook' => array(
        'button' => _x('Post', 'verb', 'generateperf') . '<span class="hide-on-mobile"> '.__('on', 'generateperf').' Facebook</span>',
        'label' => 'Facebook',
    ),
    'flipboard' => array(
        'button' => _x('Post', 'verb', 'generateperf') . '<span class="hide-on-mobile"> '.__('on', 'generateperf').' Flipboard</span>',
        'label' => 'Flipboard',
    ),
    'linkedin' => array(
        'button' => __('Share', 'generateperf') . '<span class="hide-on-mobile"> '.__('on', 'generateperf').' LinkedIn</span>',
        'label' => 'Linkedin',
    ),
    'pinterest' => array(
        'button' => _x('Pin', 'verb', 'generateperf') . '<span class="hide-on-mobile"> '.__('on', 'generateperf').' Pinterest</span>',
        'label' => 'Pinterest',
    ),
    'reddit' => array(
        'button' => _x('Post', 'verb', 'generateperf') . '<span class="hide-on-mobile"> '.__('on', 'generateperf').' Reddit</span>',
        'label' => 'Reddit',
    ),
    'snapchat' => array(
        'button' => __('Share', 'generateperf') . '<span class="hide-on-mobile"> '.__('on', 'generateperf').' Snapchat</span>',
        'label' => 'Snapchat',
    ),
    'telegram' => array(
        'button' => __('Share', 'generateperf') . '<span class="hide-on-mobile"> '.__('on', 'generateperf').' Telegram</span>',
        'label' => 'Telegram',
    ),
    'threads' => array(
        'button' => __('Share', 'generateperf') . '<span class="hide-on-mobile"> '.__('on', 'generateperf').' Threads</span>',
        'label' => 'Threads',
    ),
    'x' => array(
        'button' => __('Share', 'generateperf') . '<span class="hide-on-mobile"> '.__('on', 'generateperf').' X</span>',
        'label' => 'X',
    ),
    'twitter' => array(
        'button' => _x('Tweet', 'verb', 'generateperf') . '<span class="hide-on-mobile"> '.__('on', 'generateperf').' Twitter</span>',
        'label' => 'Twitter',
    ),
    'whatsapp' => array(
        'button' => __('Send', 'generateperf') . '<span class="hide-on-mobile"> '.__('through', 'generateperf').' Whatsapp</span>',
        'label' => 'Whatsapp',
    ),
    'copy-link' => array(
        'button' => __('Copy', 'generateperf') . '<span class="hide-on-mobile"> '.__('the link', 'generateperf').'</span>',
        'label' => __('Copy', 'generateperf') . ' ' . __('the link', 'generateperf'),
    ),
);
}

//-----------------------------------------------------
// Affichage des boutons de partage social
//-----------------------------------------------------

add_action('template_redirect', 'generatepress_child_share_links_init', 0);

function generatepress_child_share_links_init()
{
    if (!generate_child_option_active_on_current_page('generate_child_social_share')) {
        return;
    }
    $active_locations = json_decode(stripslashes(get_option('generate_child_social_share_position')), true);
    if (empty($active_locations) || !is_array($active_locations)) {
        return;
    }
    if (array_key_exists('before_content', $active_locations)) {
        add_action('generate_after_entry_header', function(){
            generatepress_child_share_links('top');
        }, PHP_INT_MAX);
    }
    if (array_key_exists('after_content', $active_locations)) {
        add_action('generate_after_content', function(){
            generatepress_child_share_links('bottom');
        }, 0);
    }
}

//-----------------------------------------------------
// Génération des boutons de partage social
//-----------------------------------------------------

function generatepress_child_share_links($position)
{
    $postType = get_post_type_object(get_post_type());
    $label = $postType->labels->singular_name;

    $share_buttons = '<div class="component">';

    if($position !== 'top'){
        $share_buttons .= generateperf_separator();
        $share_buttons .=  '<h2 class="simple">' . sprintf(__('Like this %s? Share it!', 'generateperf'), $label) . '</h2>';
    }

    // Mode natif
    if(get_option('generate_child_social_share_native')){
        $share_buttons .= '<div class="component share-buttons-toggler">';
        $share_buttons .= '<button class="button button-native" data-share-url="native">' . generate_child_get_svg_icon('share', '24') . ' ' . sprintf(__('Share this %s', 'generateperf'), $label) . '</button>';
        $share_buttons .=  '</div>';
    }

    $share_buttons .=  '<div class="social-share">';
    $services = generatepress_child_social_share_services();
    $active_services = json_decode(stripslashes(get_option('generate_child_active_social_services')), true);
    if (is_array($active_services)) {
        foreach ($active_services as $active_service => $true) {
            $share_buttons .=  '<span class="button button-'.$active_service.'" data-share-url="'.$active_service.'">' . generate_child_get_svg_icon($active_service) . ' ' . $services[$active_service]['button'].'</span>';
        }
    }
    $share_buttons .=  '</div>';
    $share_buttons .=  '</div>';

    echo $share_buttons;
}

//-----------------------------------------------------
// Sommaire automatique (Table Of Contents)
//-----------------------------------------------------

add_filter('the_content', 'generatepress_child_display_toc');
function generatepress_child_display_toc($content)
{
    if (generate_child_option_active_on_current_page('generate_child_toc')) {
        $label_1 = array(
            'show' => __('show %s', 'generateperf'),
            'view' => __('see %s', 'generateperf'),
            'add' => __('display %s', 'generateperf'),
            'visualize' => __('visualize %s', 'generateperf'),
            'deploy' => __('unfold %s', 'generateperf'),
            'reveal' => __('reveal %s', 'generateperf'),
        );
        $label_2 = array(
            'show' => __('hide %s', 'generateperf'),
            'view' => __('unsee %s', 'generateperf'),
            'add' => __('cover up %s', 'generateperf'),
            'visualize' => __('occult %s', 'generateperf'),
            'deploy' => __('fold up %s', 'generateperf'),
            'reveal' => __('conceal %s', 'generateperf'),
        );
        $toggles = array(
            'summary' => __('summary', 'generateperf'),
            'toc' => __('table of content', 'generateperf'),
            'index' => __('index', 'generateperf'),
            'abstract' => __('abstract', 'generateperf'),
            'sections' => __('sections', 'generateperf'),
            'titles' => __('titles', 'generateperf'),
          );
        $toc = '<details class="toc"><summary>
        <span class="show">'.ucfirst(sprintf($label_1[get_option('generate_child_toc_texts', 'show')] , $toggles[get_option('generate_child_toc_label', 'summary')] )).' </span>
        <span class="hide">'.ucfirst(sprintf($label_2[get_option('generate_child_toc_texts', 'show')] , $toggles[get_option('generate_child_toc_label', 'summary')] )).' </span>
        </summary><ul class="intoc">';
        $index = 1;
        $content = preg_replace_callback('#<(h[1-3])(.*?)>(.*?)</\1>#si', function ($matches) use (&$index, &$toc) {
            $tag = $matches[1];
            $title = strip_tags($matches[3]);
            $hasid = preg_match('/id=(["\'])(.*?)\1/si', $matches[2], $currentid);
            $id = $hasid ? (string) 'toc-' . $currentid[2] : 'toc-title-' . $index;
            $toc .= '<li class="item-'.$tag.'"><a class="simple" href="#'.$id.'">'.$title.'</a></li>';
            ++$index;
            return sprintf('<%1$s%2$s id="%3$s">%4$s</%1$s>', $tag, $matches[2], $id, $matches[3]);
        }, $content);
        $toc .= '</ul></details>';

        if ($index > 2) {
            if(get_option('generate_child_toc_area') === 'sidebar_top'){
                global $toc_content;
                $toc_content = $toc;
            } elseif(get_option('generate_child_toc_area') === 'under_content'){
                $content = $content . $toc;
            } else {
                $content = $toc . $content;
            }
        }
    }
    return $content;
}

//-----------------------------------------------------
// Affichage sommaire en sidebar
//-----------------------------------------------------

add_action('generate_before_right_sidebar_content', 'generate_child_display_toc_in_sidebar', 11);
function generate_child_display_toc_in_sidebar(){
    if(generate_child_option_active_on_current_page('generate_child_toc') && get_option('generate_child_toc_area') === 'sidebar_top'){
        global $toc_content;
        echo $toc_content;
    }
}

//-----------------------------------------------------
// Affichage des réseaux sociaux des auteurs
//-----------------------------------------------------

function generatepress_child_display_author_social()
{
    $content = '';
    $social_medias = array(
        'facebook' => '',
        'linkedin' => '',
        'twitter' => 'https://twitter.com/',
        'instagram' => '',
    );

    foreach($social_medias as $social_media => $prefix){
        if (get_the_author_meta($social_media)) {
            $content .= '<a class="simple" aria-label="' . sprintf( esc_html__( '%1$s’s profile on %2$s', 'generateperf' ), get_the_author_meta('display_name'), ucfirst($social_media) ) . '" href="' . $prefix . get_the_author_meta($social_media) . '" rel="external noopener" target="_blank">' . generate_child_get_svg_icon($social_media, '24') . '</a>';
        }
    }

    if( get_user_meta( get_the_author_meta('ID'), 'telephone', true ) ){
        $content .= '<a class="simple" aria-label="' . sprintf( esc_html__( 'Discuss on WhatsApp with %1$s', 'generateperf' ), get_user_meta( get_the_author_meta('ID'), 'telephone', true ) ) . '" href="https://wa.me/' . substr( get_user_meta( get_the_author_meta('ID'), 'telephone', true ) , 3) . '" rel="external noopener" target="_blank">' . generate_child_get_svg_icon('whatsapp', '24') . '</a>';
    }

    if( !empty($content) ) {
        return '<div class="social-links"><span class="label">' . __('Social medias:', 'generateperf') . '</span>' . $content . '</div>';
    }
    return '';
}

//-----------------------------------------------------
// Affichage de l'encart auteur
//-----------------------------------------------------

add_action('generate_after_content', 'generatepress_child_display_author_box', 5);
function generatepress_child_display_author_box()
{
    if (generate_child_option_active_on_current_page('generate_child_author_box')) {
        $content = '<div class="component">';
        $content .= generateperf_separator();
        $content .= '<div class="author-box" itemprop="author" itemscope="itemscope" itemtype="http://schema.org/Person">';
        if(get_avatar_url(get_the_author_meta('ID'))){
            $content .= '<div class="avatar"><img src="' . get_avatar_url(get_the_author_meta('ID')) . '" alt="'.get_the_author_meta('display_name').'" width="100" height="100"></div>';
        } else {
            $content .= '<div class="avatar">'.generate_child_get_svg_icon('person-fill', 100).'</div>';
        }
        if (get_option('generate_child_author_link')) {
            $author = '<a class="author-name simple" href="'.get_author_posts_url(get_the_author_meta('ID')).'" itemprop="name">'.get_the_author_meta('display_name').'</a>';
        } else {
            $author = '<span itemprop="name">'.get_the_author_meta('display_name').'</span>';
        }
        $label = array(
            'about' => sprintf(__('About the author, %s', 'generateperf'), $author),
            'information' => sprintf(__('Information on the author, %s', 'generateperf'), $author),
            'written' => sprintf(__('This content was written by %s', 'generateperf'), $author),
            'discover' => sprintf(__('Discover the author, %s', 'generateperf'), $author),
            'whois' => sprintf(__('Who is the author, %s?', 'generateperf'), $author),
            'learn' => sprintf(__('Learn about %s, the author', 'generateperf'), $author),
        );
        $content .= '<div><h2 class="simple">'.$label[get_option('generate_child_written_by_text', 'about')].'</h2>';
        if (get_the_author_meta('description')) {
            $content .= '<p itemprop="description">'.wp_kses(get_the_author_meta('description'), null).'</p>';
        }
        $content .= generatepress_child_display_author_social();
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
        echo $content;
    }
}

//-----------------------------------------------------
// Affichage des infos auteur sur les pages de profil
//-----------------------------------------------------

add_action('generate_after_archive_description', 'generatepress_child_add_author_meta_in_archives', 10, 1);

function generatepress_child_add_author_meta_in_archives() {
    if(is_author()) {
        echo generatepress_child_display_author_social();
    }
}

//-----------------------------------------------------
// Affichage de la date de publication + modification sur les articles
//-----------------------------------------------------

add_filter('generate_post_date_output', 'generatepress_child_add_edited_to_post', 10, 1);
function generatepress_child_add_edited_to_post($output)

{
        if(is_singular()){

        $label_published = array(
            'published' => __('Published on', 'generateperf'),
            'uploaded' => __('Posted on', 'generateperf'),
            'added' => __('Added on', 'generateperf'),
            'issued' => __('Issued on', 'generateperf'),
            'released' => __('Released on', 'generateperf'),
            'unveiled' => __('Unveiled on', 'generateperf'),
            'publicized' => __('Publicized on', 'generateperf'),
            'communicated' => __('Press release from', 'generateperf'),
        );

        $label_updated = array(
            'modified' => __('Modified on', 'generateperf'),
            'modified_last' => __('Last modification on', 'generateperf'),
            'edited' => __('Edited on', 'generateperf'),
            'edited_last' => __('Last edition on', 'generateperf'),
            'updated' => __('Updated on', 'generateperf'),
            'updated_last' => __('Last update on', 'generateperf'),
            'corrected' => __('Corrected on', 'generateperf'),
            'corrected_last' => __('Last correction on', 'generateperf'),
            'revised' => __('Revised on', 'generateperf'),
            'revised_last' => __('Last revision on', 'generateperf'),
            'reviewed' => __('Reviewed on', 'generateperf'),
            'reviewed_last' => __('Last review on', 'generateperf'),
            'fact_checked' => __('Fact checked on', 'generateperf'),
        );

        $output = '<time class="entry-date published" datetime="%2$s" itemprop="datePublished">%1$s %3$s</time> ';


        if ( (int) get_the_modified_time('U') > ( (int) get_the_time('U') + 600 ) ) {
            $output .= '<time class="updated%7$s" datetime="%4$s" itemprop="dateModified">'.generateperf_splitter().' %5$s %6$s</time>';
        }

        $output = sprintf($output,
            $label_published[get_option('generate_child_published_texts', 'published')],
            esc_attr(get_the_date('c')),
            esc_html(get_the_date( get_option('date_format')) ) . ' ' . __('at', 'generateperf') . ' ' . esc_html(get_the_date(get_option('time_format'))),
            esc_attr(get_the_modified_date('c')),
            $label_updated[get_option('generate_child_last_modified_texts', 'modified')],
            esc_html(get_the_modified_date( get_option('date_format')) ) . ' ' . __('at', 'generateperf') . ' ' . esc_html(get_the_modified_date(get_option('time_format'))),
            generate_child_option_active_on_current_page('generate_child_modified_time') ? '-visible' : '',
        );

    $output = sprintf('<span class="posted-on">%1$s</span> ', $output);
}
return $output;
}

//-----------------------------------------------------
// Activation des sous-titres via Advanced Custom Fields
//-----------------------------------------------------

add_action('acf/init', 'generate_child_register_acf_subtitles');

function generate_child_register_acf_subtitles(){
    if( get_option('generate_child_activate_subtitles') === 'acf' && function_exists('acf_add_local_field_group') ){
        acf_add_local_field_group(array(
            'key' => 'subtitles',
            'title' => 'Sous-titres',
            'fields' => array (
                array (
                    'key' => 'subtitle',
                    'label' => 'Sous-titre',
                    'name' => 'subtitle',
                    'type' => 'text',
                )
            ),
            'location' => array (
                array (
                    array (
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',
                    ),
                ),
            ),
            'position' => 'acf_after_title',
	        'style' => 'seamless',
        ));
    }
}

//-----------------------------------------------------
// Affichage des sous-titres sur les pages concernées
//-----------------------------------------------------

add_action('generate_after_entry_title', 'generate_child_display_post_subtitles', 11);
function generate_child_display_post_subtitles(){
    if( is_singular() && get_option('generate_child_activate_subtitles') ){
        if( get_option('generate_child_activate_subtitles') === 'excerpt' && has_excerpt() ){
            echo '<p role="doc-subtitle">' . get_the_excerpt() . '</p>';
        } elseif( get_option('generate_child_activate_subtitles') === 'acf' && function_exists('get_field') && get_field('subtitle') ) {
            echo '<p role="doc-subtitle">' . get_field('subtitle') . '</p>';
        }
    }
}

//-----------------------------------------------------
// Activation des sources via Advanced Custom Fields (1 champ) ou Pro (Repeater)
//-----------------------------------------------------

add_action('acf/init', 'generate_child_register_acf_source');

function generate_child_register_acf_source(){
    if( function_exists('acf_add_local_field_group') && !empty(json_decode(stripslashes(get_option('generate_child_acf_content_source'))))){
        
        if( defined('ACF_PRO') ){
            acf_add_local_field_group( array(
                'key' => 'sources',
                'title' => __('Sources', 'generateperf'),
                'fields'     => array(
                    array(
                        'key'          => 'source',
                        'name'         => 'source',
                        'type'         => 'repeater',
                        'sub_fields'   => array(
                            array(
                                'key'   => 'subsource',
                                'label' => __('Source', 'generateperf'),
                                'name'  => 'subsource',
                                'type'  => 'text',
                            ),
                        ),
                        'button_label' => __('Add a source', 'generateperf'),
                        'min'          => '',
                        'max'          => '',
                    ),
                ),
                'location' => array (
                    array (
                        array (
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'post',
                        ),
                    ),
                ),
                'position'   => 'normal',
                'style' => 'default',
            ) );
        } else {
            acf_add_local_field_group(array(
                'key' => 'sources',
                'title' => 'Sources',
                'fields' => array (
                    array (
                        'key' => 'source',
                        'label' => __('Source', 'generateperf'),
                        'name' => 'source',
                        'type' => 'url',
                    )
                ),
                'location' => array (
                    array (
                        array (
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'post',
                        ),
                    ),
                ),
                'position' => 'normal',
                'style' => 'default',
            ));
        }
    }
}

//-----------------------------------------------------
// Affichage des sources sur les pages concernées
//-----------------------------------------------------

add_action('generate_after_entry_content', 'generate_child_display_acf_sources', PHP_INT_MIN);
function generate_child_display_acf_sources(){
    if( function_exists('get_field') && generate_child_option_active_on_current_page('generate_child_acf_content_source') && get_field('source') ){
        echo '<div class="content-source"><p>' . generate_child_get_svg_icon('link-horizontal', 24) . ' Sources :';
        $counter = 0;
        if(is_array(get_field('source'))){
            while( have_rows('source') ){
                the_row();
                if( wp_http_validate_url( get_sub_field('subsource') ) ){
                    if(++$counter > 1){
                        echo ',';
                    }
                    echo ' <a class="simple" href="' . get_sub_field('subsource') . '" target="_blank" rel="nofollow noopener">' . str_replace('www.','',parse_url(get_sub_field('subsource'))['host']) . '</a>';
                }
            }
        } else {
            echo ' <a class="simple" href="' . get_field('source') . '" target="_blank" rel="nofollow noopener">' . str_replace('www.','',parse_url(get_field('source'))['host']) . '</a>';
        }
        echo '</p></div>';
    }
}

//-----------------------------------------------------
// Fonction de calcul de la durée de lecture
//-----------------------------------------------------

function generatepress_child_estimate_reading_time($post_id, $post_content) {
    $count_words = str_word_count( strip_tags( $post_content ) );
    return update_post_meta( $post_id, 'reading_time', ceil($count_words / 250) );
}

//-----------------------------------------------------
// Calcul d’une durée de lecture à la publication
//-----------------------------------------------------

add_action('save_post', 'generatepress_child_initial_reading_time', 10, 3);
function generatepress_child_initial_reading_time($post_id, $post, $update) {
    if (!$update || wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }
    if ('post' !== $post->post_type) {
        return;
    }
    generatepress_child_estimate_reading_time($post_id, $post->post_content);
}

//-----------------------------------------------------
// Calcul d’une durée de lecture sur les contenus si inexistante
//-----------------------------------------------------

add_filter('template_redirect', 'generatepress_child_calculate_reading_time', 15);
function generatepress_child_calculate_reading_time()
{
    if (generate_child_option_active_on_current_page('generate_child_reading_time') && !get_post_meta(get_the_ID(), 'reading_time', true)) {
        $content = get_post_field( 'post_content', get_the_ID() );
        generatepress_child_estimate_reading_time( get_the_ID(), $content );
    }
}

//-----------------------------------------------------
// Affichage d’une durée de lecture sur les contenus
//-----------------------------------------------------

add_filter('generate_post_author_output', 'generatepress_child_reading_time', 15, 1);
function generatepress_child_reading_time($output)
{
    if (generate_child_option_active_on_current_page('generate_child_reading_time')) {

        $read_time = get_post_meta(get_the_ID(), 'reading_time', true);

        if($read_time > 1){
            $label = array(
                'classic' => __('Reading duration', 'generateperf'),
                'time' => __('Reading time', 'generateperf'),
                'estimation' => __('Estimated length', 'generateperf'),
                'length' => __('Article length', 'generateperf'),
                'takes' => __('It will take you', 'generateperf'),
                'duration' => __('This reading lasts', 'generateperf'),
            );
            $output .= '<span class="meta-item"> '.generateperf_splitter().' '.$label[get_option('generate_child_reading_time_labels', 'classic')].' : ' . $read_time . ' ' . __('minutes', 'generateperf') . '</span>';
        }
    }
    return $output;
}

//-----------------------------------------------------
// Modification de style de la navigation
//-----------------------------------------------------

/*
add_filter( 'generate_previous_link_text', function() {
    return '<';
} );
add_filter( 'generate_next_link_text', function() {
    return '>';
} );
*/

//-----------------------------------------------------
// Tri par ordre alphabétique pour un custom post type
//-----------------------------------------------------

/*
add_action('pre_get_posts', 'generatepress_child_sort_order_alphabetic');
    function generatepress_child_sort_order_alphabetic($query)
    {
        if (is_post_type_archive('mycpt')) {
            $query->set('orderby', 'title');
            $query->set('order', 'ASC');
        }
    };
*/
