<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Génération du sitemap news
// Follows rules : https://developers.google.com/search/docs/crawling-indexing/sitemaps/news-sitemap?hl=fr
//-----------------------------------------------------

add_action('template_redirect', 'generate_google_news_sitemap');
function generate_google_news_sitemap() {
    if (get_query_var('news_sitemap')) {
        header('Content-Type: application/xml; charset=UTF-8');
        $sitemap = get_transient('google_news_sitemap');

        if (!$sitemap) {
            ob_start();
            
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => -1,
                'date_query' => array(
                    array(
                        'after' => '2 days ago',
                    )
                )
            );
            
            $query = new WP_Query($args);
            
            echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            echo '<!-- generated-on="' . date( 'c', current_time( 'timestamp', 0 )) . '" -->'."\n";
            echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">'."\n";
            
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    
                    echo '<url>'."\n";
                    echo '  <loc>' . get_permalink() . '</loc>'."\n";
                    echo '  <news:news>'."\n";
                    echo '      <news:publication>'."\n";
                    echo '          <news:name>' . esc_xml(get_bloginfo('name')) . '</news:name>'."\n";
                    echo '          <news:language>' . substr(get_bloginfo('language'), 0, 2) . '</news:language>'."\n";
                    echo '      </news:publication>'."\n";
                    echo '      <news:publication_date>' . get_post_time('c') . '</news:publication_date>'."\n";
                    echo '      <news:title>' . esc_xml(get_the_title()) . '</news:title>'."\n";
                    echo '  </news:news>'."\n";
                    echo '</url>'."\n";
                }
                wp_reset_postdata();
            }
            
            echo '</urlset>';

            $sitemap = ob_get_clean();
            set_transient('google_news_sitemap', $sitemap, 60);
        }
        echo $sitemap;
        exit;
    }
}

//-----------------------------------------------------
// Ajout de la variable de requête correspondante
//-----------------------------------------------------

add_filter('query_vars', 'generateperf_add_news_sitemap_query_var');
function generateperf_add_news_sitemap_query_var($query_vars) {
    $query_vars[] = 'news_sitemap';
    return $query_vars;
}

//-----------------------------------------------------
// Ajout de la règle de réécriture
//-----------------------------------------------------

add_action('init', 'generateperf_add_news_sitemap_rewrite_rule', 1);
function generateperf_add_news_sitemap_rewrite_rule() {
    add_rewrite_rule('sitemap-news.xml$', 'index.php?news_sitemap=1', 'top');
}

//-----------------------------------------------------
// Ajout du fichier dans le robots.txt WordPress
//-----------------------------------------------------

add_filter( 'robots_txt', 'generateperf_news_sitemap_robots_txt', PHP_INT_MAX, 2 );
function generateperf_news_sitemap_robots_txt( $output, $public ) {
    if ( '0' === $public ) {
        return $output;
    }

    $sitemap_url = home_url('/sitemap-news.xml');
    $output .= "\nSitemap: $sitemap_url\n";

    return $output;
}

//-----------------------------------------------------
// Pas de trailing slash pour le fichier de sitemap
//-----------------------------------------------------

add_filter('user_trailingslashit', 'generateperf_sitemap_no_post_slash', PHP_INT_MAX, 1);
function generateperf_sitemap_no_post_slash( $string ){
    if($string == '/sitemap-news.xml/') {
        $string = untrailingslashit($string);
    }
   return $string;
}

//-----------------------------------------------------
// Purge du sitemap à chaque nouvelle publication
//-----------------------------------------------------

add_action('transition_post_status', 'generateperf_purge_google_news_sitemap_transient', 10, 3);
function generateperf_purge_google_news_sitemap_transient($new_status, $old_status, $post) {
    if ($post->post_type == 'post' && ($new_status == 'publish' || $old_status == 'publish')) {
        delete_transient('google_news_sitemap');
    }
}
