<?php

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// On supprime la prise en charge des Emoji
//-----------------------------------------------------

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');

//-----------------------------------------------------
// On supprime les filtres SVG ajoutés par WordPress (depuis 5.9)
//-----------------------------------------------------

add_action('init', 'generatepress_child_disable_svg_filters');
function generatepress_child_disable_svg_filters()
{
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
}

//-----------------------------------------------------
// On supprime les global styles ajoutés par WordPress (depuis 5.9)
//-----------------------------------------------------

add_action('init', 'generatepress_child_disable_global_styles');
function generatepress_child_disable_global_styles()
{
    remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
}

//-----------------------------------------------------
// On supprime le classic theme ajouté par WordPress (depuis 6.1)
//-----------------------------------------------------

add_filter('wp_enqueue_scripts', 'generatepress_child_disable_classic_theme_styles', PHP_INT_MAX);
function generatepress_child_disable_classic_theme_styles()
{
    wp_deregister_style('classic-theme-styles');
    wp_dequeue_style('classic-theme-styles');
}

//-----------------------------------------------------
// On ne charge que le CSS des blocs Gutenberg utilisés
//-----------------------------------------------------

add_filter('should_load_separate_core_block_assets', '__return_true');

//-----------------------------------------------------
// On envoie un maximum de CSS en externe pour le concaténer
//-----------------------------------------------------

add_filter('styles_inline_size_limit', '__return_zero');

//-----------------------------------------------------
// Suppression de la version de WordPress dans les metas
//-----------------------------------------------------

add_filter('the_generator', '__return_empty_string');

//-----------------------------------------------------
// Suppression des link rel="dns-prefetch" inutiles
//-----------------------------------------------------

add_filter('wp_resource_hints', 'generateperf_remove_bad_hints', PHP_INT_MAX, 2);
function generateperf_remove_bad_hints($urls, $relation_type)
{
    if ('dns-prefetch' !== $relation_type) {
        return $urls;
    }
    return array_filter($urls, function($url) {
        return !str_contains($url, 'fonts.gstatic.com') &&
               !str_contains($url, 'stats.wp.com') &&
               !str_contains($url, 'fonts.googleapis.com');
    });
}

//-----------------------------------------------------
// Gestion d'un nombre de révisions maximum
//-----------------------------------------------------

if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 3);
}

//-----------------------------------------------------
// Suppression de la sauvegarde automatique
//-----------------------------------------------------

if (!defined('AUTOSAVE_INTERVAL')) {
    define('AUTOSAVE_INTERVAL', 86400);
}

//-----------------------------------------------------
// Pas de nouveau thème par défaut chaque année
//-----------------------------------------------------

if (!defined('CORE_UPGRADE_SKIP_NEW_BUNDLED')) {
    define('CORE_UPGRADE_SKIP_NEW_BUNDLED', true);
}

//-----------------------------------------------------
// Suppression de comment-reply.min.js si pas nécessaire
//-----------------------------------------------------

add_action('wp_enqueue_scripts', 'generatepress_child_dequeue_comment_reply', PHP_INT_MAX);
function generatepress_child_dequeue_comment_reply()
{
    if (is_singular() && (!comments_open() || !get_option('thread_comments') || !get_comments_number(get_the_ID()))) {
        wp_deregister_script('comment-reply');
    }
}

//-----------------------------------------------------
// Contact Form 7 sur les pages pertinentes uniquement
//-----------------------------------------------------

add_action( 'wp_enqueue_scripts', 'generatepress_child_remove_contact_form_7', PHP_INT_MAX);
function generatepress_child_remove_contact_form_7() {
    if (!defined('WPCF7_VERSION')) return;
    global $post;
    if( !is_a( $post, 'WP_Post' ) || ( is_a( $post, 'WP_Post' ) && !has_shortcode( $post->post_content, 'contact-form-7') ) ) {
        wp_dequeue_script( 'contact-form-7' );
        wp_dequeue_script( 'google-recaptcha' );
        wp_dequeue_script( 'wpcf7-recaptcha' );
        wp_dequeue_style( 'contact-form-7' );
    }
}

//-----------------------------------------------------
// Remplacement des paramètres par défaut (utilisé à l'activation)
//-----------------------------------------------------

function generatepress_child_wp_rocket_set_default_parameters() {

    if( function_exists('update_rocket_option') ) 
    {
        update_rocket_option( 'analytics_enabled', 0);
        update_rocket_option( 'cache_logged_user', 0);
        update_rocket_option( 'remove_unused_css', 1);
        update_rocket_option( 'cache_mobile', 1);
        update_rocket_option( 'optimize_css_delivery', 0);
        update_rocket_option( 'async_css', 0);
        update_rocket_option( 'async_css_mobile', 0);
        update_rocket_option( 'minify_css', 1);
        update_rocket_option( 'minify_concatenate_css', 0);
        update_rocket_option( 'minify_js', 1);
        update_rocket_option( 'minify_concatenate_js', 0);
        update_rocket_option( 'exclude_js', ['facebook.com', 'facebook.net', 'instagram.com', 'twitter.com', 'tiktok.com', 'pinterest.com', 'google-analytics.com', 'googletagmanager.com', 'statcounter.com', 'stats.wp.com', 'onesignal.com', 'viously.com', 'wonderpush.com', 'flashb.id', 'plausible.io']);
        update_rocket_option( 'defer_all_js', 1);
        update_rocket_option( 'exclude_defer_js', []);
        update_rocket_option( 'delay_js', 1);
        update_rocket_option( 'lazyload', 1);
        update_rocket_option( 'lazyload_css_bg_img', 1);
        update_rocket_option( 'lazyload_iframes', 1);
        update_rocket_option( 'lazyload_youtube', 1);
        update_rocket_option( 'sitemap_preload', 1);
        update_rocket_option( 'preload_links', 0);
        update_rocket_option( 'image_dimensions', 1);
        update_rocket_option( 'purge_cron_interval', 7);
        update_rocket_option( 'purge_cron_unit', 'DAY_IN_SECONDS');
        update_rocket_option( 'manual_preload', 1);
        update_rocket_option( 'delay_js_exclusions', ['/jquery-?[0-9.](.*)(.min|.slim|.slim.min)?.js', 'js-(before|after)', '/wp-content/(?!(.*)(plugins/insta-gallery/|plugins/onesignal)(.*))', '/wp-includes/(?!(.*)(js/twemoji.js|js/mediaelement/)(.*))', 'data-behind-cmp', 'using-mouse']);
        update_rocket_option( 'cache_webp', 0);
    }
}

//-----------------------------------------------------
// WP Rocket : sets how old the failed job should be to be cleared
//-----------------------------------------------------

add_filter('rocket_delay_remove_rucss_failed_jobs',
	function () {
		$new_interval = '2 days';
		return $new_interval;
	}
);

//-----------------------------------------------------
// WP Rocket : reduces the interval of the failed jobs cron
//-----------------------------------------------------

add_filter('rocket_remove_rucss_failed_jobs_cron_interval', function () {
	$new_interval = 6 * 3600;
	return $new_interval;
}, PHP_INT_MAX);

//-----------------------------------------------------
// WP Rocket : modification du nombre d'url à précharger dans chaque batch (45 par défaut)
//-----------------------------------------------------

add_filter( 'rocket_preload_cache_pending_jobs_cron_rows_count', 'generatepress_child_preload_batch_size'  );
function generatepress_child_preload_batch_size( $value ) {     
    $value = 50; 
    return $value;
}

//-----------------------------------------------------
// WP Rocket : modification de l'intervalle des crons de préchargement (60 par défaut)
//-----------------------------------------------------

add_filter( 'rocket_preload_pending_jobs_cron_interval', 'generatepress_child_preload_cron_interval'  );
function generatepress_child_preload_cron_interval( $interval ) {   
    $interval = 59;
    return $interval;
}

//-----------------------------------------------------
// WP Rocket : modification du délai entre les requêtes (0.5 par défaut)
//-----------------------------------------------------

add_filter( 'rocket_preload_delay_between_requests', 'generatepress_child_preload_requests_delay'  );
function generatepress_child_preload_requests_delay( $delay_between ) {   
    $seconds = 0.35;
    $delay_between = $seconds * 1000000;
    return $delay_between;
}

//-----------------------------------------------------
// WP Rocket : Processes the "img" tag that's included in <picture> tags
//-----------------------------------------------------

add_filter( 'rocket_specify_dimension_skip_pictures', '__return_false' );

//-----------------------------------------------------
// WP Rocket : Enable setting image dimensions for external images
//-----------------------------------------------------

add_filter( 'rocket_specify_image_dimensions_for_distant', '__return_true' );

//-----------------------------------------------------
// Execute inline JavaScript later to prevent errors due to deferring
//-----------------------------------------------------

/*
add_filter( 'rocket_defer_jquery_patterns', 'awp_report_inline_js_patterns_execution', 1 );
function awp_report_inline_js_patterns_execution() {
    return 'moment.updateLocale|jQuery|\$\.\(|\$\(';
}
*/

//-----------------------------------------------------
// Forcer l’activation de WP Rocket
//-----------------------------------------------------

/*
​add_action( 'admin_init', function(){
    $site_ids = Array( 1, 4 );
    foreach ( $site_ids as $site_id ) {
        switch_to_blog( $site_id );
        if ( ! is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
            activate_plugins( "wp-rocket/wp-rocket.php" );
        }
        restore_current_blog();
    }
} );
*/