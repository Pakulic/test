<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Logo optimisé voire SVG en inline
//-----------------------------------------------------

add_filter('generate_logo_output', 'generatepress_child_inline_svg_logo', 10, 3);

function generatepress_child_inline_svg_logo($output, $logo_url, $html_attr) {
    if (get_option('generate_child_inline_svg_logo') && substr($logo_url, -4) === '.svg') {
        $custom_logo_id = get_theme_mod('custom_logo');
        $svg_file_path = get_attached_file($custom_logo_id, true);
        $svg_content = file_get_contents($svg_file_path, FILE_USE_INCLUDE_PATH);
        $output = str_replace('<svg ', '<svg class="header-image is-logo-image" ', $svg_content);
        if (false !== strpos($output, 'aria-label=')) {
            $output = str_replace('<svg ', sprintf('<svg aria-label="%s" ', esc_attr(get_bloginfo('name'))), $output);
        }
    } else {
        $output = sprintf('<img %s loading="eager" decoding="async" fetchpriority="high" data-skip-lazy="true">', $html_attr);
    }
    return $output;
}

//-----------------------------------------------------
// Logo mobile optimisé (SVG en inline non développé)
//-----------------------------------------------------

add_filter( 'generate_mobile_header_logo_output', 'generatepress_child_inline_mobile_svg_logo');
function generatepress_child_inline_mobile_svg_logo( $output ) {
    if ( ! function_exists( 'generate_menu_plus_get_defaults' ) ) {
        return $output;
    }

    $settings = wp_parse_args(
        get_option( 'generate_menu_plus_settings', array() ),
        generate_menu_plus_get_defaults()
    );

    return sprintf(
        '<div class="site-logo mobile-header-logo">
            <a href="%1$s" title="%2$s" rel="home">
                <img src="%3$s" alt="%4$s" loading="eager" decoding="async" fetchpriority="high" data-skip-lazy="true">
            </a>
        </div>',
        esc_url( apply_filters( 'generate_logo_href' , home_url( '/' ) ) ),
        esc_attr( apply_filters( 'generate_logo_title', get_bloginfo( 'name', 'display' ) ) ),
        esc_url( apply_filters( 'generate_mobile_header_logo', wp_get_attachment_image_url($settings['mobile_header_logo']) ) ),
        esc_attr( apply_filters( 'generate_logo_title', get_bloginfo( 'name', 'display' ) ) )
    );
}

//-----------------------------------------------------
// Changement du logo selon la page
//-----------------------------------------------------

/*
add_filter( 'generate_logo', function( $logo ) {
    if(function_exists('pll_current_language')){
        if ( pll_current_language() == 'fr-be' ) {
            return '/wp-content/uploads/logo-be.svg';
        } elseif ( pll_current_language() == 'fr-ch' ) {
            return '/wp-content/uploads/logo-ch.svg';
        }
    }
  return $logo; 
}, 1 );
*/

//-----------------------------------------------------
// Ajout de la photo de l'auteur sur les archives auteur si pas géré nativement
//-----------------------------------------------------

add_filter('get_the_archive_title', 'generatepress_child_archives_title', PHP_INT_MAX, 1 );
function generatepress_child_archives_title($title) {
    if( is_author() && !get_option( 'show_avatars' ) && get_avatar_url(get_the_author_meta('ID')) ){
        $title = '<img src="' . get_avatar_url(get_the_author_meta('ID')) . '" alt="'.get_the_author_meta('display_name').'" width="75" height="75" decoding="async" data-skip-lazy="true" loading="eager"> ' . $title;
    }
    return $title;
}

//-----------------------------------------------------
// Affichage d'une image par défaut s'il n'y a aucun thumbnail
//-----------------------------------------------------

add_filter('generate_before_entry_title', function ($output) {
    if (!get_the_post_thumbnail()) {
        echo '<div class="post-image"></div>';
    }
});

//-----------------------------------------------------
// Pas de lien sur les images dans les pages d'archives et priorisation de la première / Optimisé le 21/09/2023
//-----------------------------------------------------

add_filter('generate_featured_image_output', 'generateperf_optimized_featured_image_output', 10, 1);
function generateperf_optimized_featured_image_output($output) {
    if (is_archive() || is_front_page()) {
        $attributes = array(
            'itemprop' => 'image',
        );
        if (0 === get_query_var('current_post')) {
            $attributes += array(
                'fetchpriority' => 'high',
                'loading'       => 'eager',
                'decoding'      => 'async',
                'data-skip-lazy'=> 'true',
            );
        }
        $post_thumbnail = get_the_post_thumbnail(
            get_the_ID(),
            apply_filters('generate_page_header_default_size', 'medium'),
            $attributes
        );
        return sprintf('<figure class="post-image">%s</figure>', $post_thumbnail);
    }
    return $output;
}

//-----------------------------------------------------
// Préchargement des images mises en avant
//-----------------------------------------------------

add_action('wp_head', 'generatepress_child_preload_main_image', 11);
function generatepress_child_preload_main_image()
{
    if (generate_child_option_active_on_current_page('generate_child_preload_lcp') && get_the_post_thumbnail_url(get_the_ID())) {
        $html = get_the_post_thumbnail(get_the_ID());
        $dom = new DOMDocument();
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $nodes = $dom->getElementsByTagName('img');
        $attributes = array(
            'rel' => 'preload',
            'as' => 'image',
            'href' => $nodes->item(0)->getAttribute('src'),
            'imagesrcset' => $nodes->item(0)->getAttribute('srcset'),
            'imagesizes' => $nodes->item(0)->getAttribute('sizes'),
        );
        echo '<link';
        foreach($attributes as $attribute => $value){
            if(!empty($value)){
                echo ' '. $attribute . '="' . $value . '"';
            }
        }
        echo '>';
    }
}

//-----------------------------------------------------
// Optimisation du chargement des premières images des contenus
//-----------------------------------------------------

add_filter('the_content', 'generatepress_child_add_nolazy_class', 1);
function generatepress_child_add_nolazy_class($content)
{
    if (generate_child_option_active_on_current_page('generate_child_no_lazy_lcp') && !empty($content)) {
        $content = mb_encode_numericentity($content, [0x80, 0x10FFFF, 0, ~0], "UTF-8");
        $document = new DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML(mb_convert_encoding($content, "UTF-8", mb_detect_encoding($content)));
        $imgs = $document->getElementsByTagName('img');
        if (count($imgs)) {;
            $imgs[0]->setAttribute('loading', 'eager');
            $imgs[0]->setAttribute('decoding', 'async');
            $imgs[0]->setAttribute('fetchpriority', 'high');
            $imgs[0]->setAttribute('data-skip-lazy', 'true');
            $content = $document->saveHTML();
        }
    }
    return $content;
}

//-----------------------------------------------------
// Images mises en avant via Gutenberg : re-priorisation
//-----------------------------------------------------

add_filter('render_block', function ($content, $block) {
    if ( in_array($block['blockName'], array('generateblocks/image', 'core/image')) && array_key_exists('className', $block['attrs']) && str_contains($block['attrs']['className'], 'skip-lazy') && !is_admin() && !empty($content)) {
        $content = mb_encode_numericentity($content, [0x80, 0x10FFFF, 0, ~0], "UTF-8");
        $document = new DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML(mb_convert_encoding($content, "UTF-8", mb_detect_encoding($content)));
        $imgs = $document->getElementsByTagName('img');
        if (count($imgs)) {
            $imgs[0]->setAttribute('loading', 'eager');
            $imgs[0]->setAttribute('decoding', 'async');
            $imgs[0]->setAttribute('fetchpriority', 'high');
            $imgs[0]->setAttribute('data-skip-lazy', 'true');
            $content = $document->saveHTML();
        }
	}
	return $content;
}, 10, 2 );

//-----------------------------------------------------
// Images mises en avant : priorisation via Priority Hints et pas de lazy loading
//-----------------------------------------------------

add_filter('generate_single_featured_image_output', 'generateperf_process_single_featured_image_output', 10, 1);
function generateperf_process_single_featured_image_output($output) {
    $attributes = [];
    $caption = '';
    if (generate_child_option_active_on_current_page('generate_child_fetchpriority_high_lcp')) {
        $attributes['fetchpriority'] = 'high';
    }
    if (generate_child_option_active_on_current_page('generate_child_no_lazy_thumbnail')) {
        $attributes += [
            'loading' => 'eager',
            'decoding' => 'async',
            'data-skip-lazy' => 'true',
        ];
    }
    if (generate_child_option_active_on_current_page('generate_child_featured_image_caption')) {
        $caption = get_the_post_thumbnail_caption(get_the_ID()) ?: (get_bloginfo('name') . ' - ' . get_the_title());
        $caption = '<figcaption>&copy; ' . $caption . '</figcaption>';
    }
    printf(
        '<figure class="featured-image">%1$s%2$s</figure>',
        get_the_post_thumbnail(get_the_ID(), 'large', $attributes),
        $caption,
    );
}

//-----------------------------------------------------
// Pas de lazy-loading sur les images priorisées par WordPress
//-----------------------------------------------------

add_filter('rocket_lazyload_excluded_attributes', 'generatepress_child_wp_rocket_nolazyload');
function generatepress_child_wp_rocket_nolazyload($attributes)
{
    $attributes[] = 'fetchpriority="high"'; // Native WP addition on first images
    return $attributes;
}

//-----------------------------------------------------
// Limite de taille maximale des images à 1600 px
//-----------------------------------------------------

add_filter('big_image_size_threshold', 'generatepress_child_image_size_threshold', PHP_INT_MAX, 1);
function generatepress_child_image_size_threshold($threshold)
{
    return 1600;
}

//-----------------------------------------------------
// Désactivation des .png redimentionnés
//-----------------------------------------------------

add_filter('intermediate_image_sizes_advanced', 'generatepress_child_disable_upload_sizes', PHP_INT_MAX, 2);
function generatepress_child_disable_upload_sizes($sizes, $metadata)
{
    $filetype = wp_check_filetype($metadata['file']);
    if ($filetype['type'] == 'image/png') {
        $sizes = array();
    }

    return $sizes;
}

//-----------------------------------------------------
// Suppression des images de grande taille
//-----------------------------------------------------

add_action('init', 'generatepress_child_remove_big_image_sizes');
function generatepress_child_remove_big_image_sizes()
{
    remove_image_size('2048x2048');
    remove_image_size('2560x2560');
}

//-----------------------------------------------------
// On ne conserve pas la version webp dans Imagify si elle est plus grosse
//-----------------------------------------------------

add_filter('imagify_keep_large_webp', '__return_false');

//-----------------------------------------------------
// Remplacement des paramètres par défaut (utilisé à l'activation)
//-----------------------------------------------------

function generate_child_imagify_set_default_parameters() {
    if(function_exists('get_imagify_option')){
        if ( 0 !== get_imagify_option( 'admin_bar_menu' ) ) {
            update_imagify_option( 'admin_bar_menu', 0 );
        }
        if ( 1 !== get_imagify_option( 'convert_to_webp' ) ) {
            update_imagify_option( 'convert_to_webp', 1 );
        }
        if ( 0 !== get_imagify_option( 'display_webp' ) ) {
            update_imagify_option( 'display_webp', 1 );
        }
        if ( 'rewrite' !== get_imagify_option( 'display_webp_method' ) ) {
            update_imagify_option( 'display_webp_method', 'rewrite' );
        }
    }
}

//-----------------------------------------------------
// On lance le cron d'optimisation et génération du Webp (utilisé à l'activation)
//-----------------------------------------------------

function generate_child_imagify_launch_bulk_optimization() {
	if(class_exists('Imagify\Bulk\Bulk')){
		Imagify\Bulk\Bulk::get_instance()->run_optimize( 'wp', 1 );
		Imagify\Bulk\Bulk::get_instance()->run_generate_webp( array('wp') );
	}
}

//-----------------------------------------------------
// Affichage des icônes en svg / Optimisé
//-----------------------------------------------------

function generate_child_get_svg_icon($icon_name, $dimension = array('24', '24')) {
    $svg_file = get_stylesheet_directory() . '/images/icons/' . $icon_name . '.svg';
    if (!file_exists($svg_file)) {
        return;
    }
    $built_svg = file_get_contents($svg_file, FILE_USE_INCLUDE_PATH);
    $width = $height = '24';
    if (is_array($dimension) && count($dimension) === 2) {
        list($width, $height) = $dimension;
    } elseif (!is_array($dimension)) {
        $width = $height = $dimension;
    }
    $replace_str = '<svg aria-hidden="true" focusable="false" class="icon icon-' . $icon_name . '" width="' . $width . '" height="' . $height . '" fill="currentColor" ';
    $built_svg = str_replace('<svg ', $replace_str, $built_svg);
    return $built_svg;
}

//-----------------------------------------------------
// Autoriser l'upload de SVG pour les administrateurs
//-----------------------------------------------------

add_filter(
	'upload_mimes',
	function ( $upload_mimes ) {
		if ( ! current_user_can( 'administrator' ) ) {
			return $upload_mimes;
		}
		$upload_mimes['svg']  = 'image/svg+xml';
		$upload_mimes['svgz'] = 'image/svg+xml';
		return $upload_mimes;
	}
);

//-----------------------------------------------------
// Ajout du mime type adéquat pour les SVG
//-----------------------------------------------------

add_filter(
	'wp_check_filetype_and_ext',
	function ( $wp_check_filetype_and_ext, $file, $filename, $mimes, $real_mime ) {

		if ( ! $wp_check_filetype_and_ext['type'] ) {
			$check_filetype  = wp_check_filetype( $filename, $mimes );
			$ext             = $check_filetype['ext'];
			$type            = $check_filetype['type'];
			$proper_filename = $filename;
            if ( !is_null($type) && str_starts_with( $type, 'image/' ) && 'svg' !== $ext ) {
				$ext  = false;
				$type = false;
			}
			$wp_check_filetype_and_ext = compact( 'ext', 'type', 'proper_filename' );
		}
		return $wp_check_filetype_and_ext;
	},
	10,
	5
);

//-----------------------------------------------------
// Vidéos YouTube sans cookies, et donc non bloquées par certaines CMP
//-----------------------------------------------------

add_filter( 'embed_oembed_html', 'generate_child_filter_youtube_embed', 10, 2 );
function generate_child_filter_youtube_embed( $cached_html, $url = null ) {
if ( str_contains( $url, 'youtu' ) ) {
        $cached_html = preg_replace( '/youtube\.com\/(v|embed)\//s', 'youtube-nocookie.com/$1/', $cached_html );
    }
    return $cached_html;
 }
 
//-----------------------------------------------------
// Custom menu principal avec icônes
//-----------------------------------------------------

add_filter( 'wp_nav_menu_objects', 'generatepress_child_nav_custom_icons', 10, 4 );
function generatepress_child_nav_custom_icons( $items, $args ) {
    foreach ($items as $item) {
        if ( get_post_meta( $item->ID, '_menu_item_icon', true ) ) {
            $item->title = str_replace( '<svg ', '<svg class="menu-item-icon" ', get_post_meta( $item->ID, '_menu_item_icon', true ) ) . '<span class="menu-item-label">' . $item->title . '</span>';
        }
    }
    return $items;
}

//-----------------------------------------------------
// Autoriser le SVG dans les contenus
//-----------------------------------------------------

add_filter( 'wp_kses_allowed_html', 'generate_child_add_allowed_svg_tag', 10, 2 );
function generate_child_add_allowed_svg_tag( $tags, $context ) {
        $tags['svg']  = array(
            'xmlns'       => true,
            'class'       => true,
            'fill'        => true,
            'viewbox'     => true,
            'role'        => true,
            'aria-hidden' => true,
            'focusable'   => true,
        );
        $tags['path'] = array(
            'd'    => true,
            'fill' => true,
            'class'=> true,
        );
    return $tags;
}