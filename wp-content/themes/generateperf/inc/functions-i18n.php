<?php

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Affichage des articles traduits via Polylang
//-----------------------------------------------------

add_action('generate_before_content', 'generatepress_child_display_language_switcher', 10);

function generatepress_child_display_language_switcher() {
    // Initial checks
    if (!is_single() || is_front_page() || !get_option('generate_child_display_lang_switcher') || !function_exists('pll_get_post_translations')) {
        return;
    }
    $translations = pll_get_post_translations(get_the_ID());
    unset($translations[pll_current_language('slug')]);
    if (count($translations) === 0) {
        return;
    }
    $output = '<nav class="translations"><p>';
    $output .= generate_child_get_svg_icon('translation', array(24, 26)) . ' ';
    $output .= esc_html__('This article is also available in:', 'generateperf') . ' ';
    foreach ($translations as $post_lang => $post_id) {
        $output .= sprintf(
            '<a rel="alternate" hreflang="%s" href="%s" title="%s">%s</a> ',
            esc_attr($post_lang),
            esc_url(get_permalink($post_id)),
            esc_attr(get_the_title($post_id)),
            esc_html(pll_get_post_language($post_id, 'name'))
        );
    }
    $output .= '</p></nav>';
    echo $output;
}

//-----------------------------------------------------
// Changement de la taille par défaut des drapeaux dans Polylang
//-----------------------------------------------------

add_filter( 'pll_custom_flag', 'generatepress_child_pll_custom_flag', 10, 1 );
 
function generatepress_child_pll_custom_flag( $flag ) {
    $flag['width'] = 32;
    $flag['height'] = 21;
    return $flag;
}

//-----------------------------------------------------
// Changement de la taille par défaut des drapeaux dans Polylang
//-----------------------------------------------------

add_shortcode( 'polylang_langswitcher', 'generateperf_polylang_langswitcher' );
function generateperf_polylang_langswitcher() {
	$output = '';
	if ( function_exists( 'pll_the_languages' ) ) {
		$args   = [
            'dropdown' => 1,
            'display_names_as' => 'slug',
			'show_flags' => 0,
			'show_names' => 1,
			'echo'       => 0,
		];
		$output = '<div class="polylang_langswitcher">'.pll_the_languages( $args ). '</div>';
	}
	return $output;
}

add_shortcode( 'polylang_langswitcher_articles', 'generateperf_polylang_langswitcher_articles' );
function generateperf_polylang_langswitcher_articles() {
	$output = '';
	if ( function_exists( 'pll_the_languages' ) ) {
		$args   = [
            'dropdown' => 0,
            'display_names_as' => 'slug',
			'show_flags' => 1,
			'show_names' => 0,
			'echo'       => 0,
            'hide_if_no_translation' => 1,
            'hide_current' => 1,
		];
		$output = '<div class="polylang_langswitcher_flags">'.pll_the_languages( $args ). '</div>';
	}
	return $output;
}