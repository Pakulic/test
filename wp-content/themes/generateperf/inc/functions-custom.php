<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly


//-----------------------------------------------------
// Modification des liens de pagination
//-----------------------------------------------------

add_filter( 'generate_post_navigation_args', function( $args ) {
    if ( is_single() ) {

        $args['previous_format'] = '<div class="nav-previous"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M0 0h32v32H0z" fill="none"/><path fill="currentColor" d="M16 32c8.8 0 16-7.2 16-16S24.8 0 16 0 0 7.2 0 16s7.2 16 16 16zm0-29a13 13 0 1 1 0 26 13 13 0 1 1 0-26z"/><path fill="currentColor" d="m21 10-3-3-8.8 9 8.9 9 2.8-3-6-6 6-6h.1z"/></svg> <span class="prev" title="' . esc_attr__( '«« previous blog post', 'generatepress' ) . '">%link</span></div>';
        $args['next_format'] = '<div class="nav-next"><span class="next" title="' . esc_attr__( 'next blog post »»', 'generatepress' ) . '">%link</span> <svg width="32" height="32" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 33 32"><g clip-path="url(#next-a)"><path d="M16.5 0a16 16 0 1 0 0 32 16 16 0 0 0 0-32Zm0 29a13 13 0 1 1 0-26 13 13 0 0 1 0 26Z"/><path d="m11.6 22 2.8 3 9-9-9-9-2.8 3 6 6-6 6Z"/></g><defs><clipPath id="next-a"><path fill="#fff" d="M.5 0h32v32H.5z"/></clipPath></defs></svg></div>';
    }

    return $args;
} );

//-----------------------------------------------------
// Modification de la position des post meta
//-----------------------------------------------------

/*
add_action( 'wp', function() {
    if(is_singular() && get_post_type() == 'post'){
        remove_action( 'generate_after_entry_title', 'generate_post_meta' );
        add_action( 'generate_after_entry_header', 'generate_post_meta', 15 );
    }
} );
*/

//-----------------------------------------------------
// Modification des slugs des posts après une date
//-----------------------------------------------------

/*
add_filter( 'post_link', 'generatepress_child_conditional_custom_permalink', 10, 2 );
function generatepress_child_conditional_custom_permalink( $permalink, $post ) {
    $post_datetime = new DateTime( $post->post_date );
    $swap_datetime = DateTime::createFromFormat( 'd/m/Y h:i:s', '01/01/1950 08:00:00' );
    if ( $post_datetime > $swap_datetime ) {
        $category = get_the_category( $post->ID );
        $permalink = trailingslashit( home_url( $category[0]->slug . '/' . $post->ID .'-' . $post->post_name . '/' ) );
    }
    return $permalink;
}
*/

//-----------------------------------------------------
// Administration perso
//-----------------------------------------------------

/*
if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title' 	=> 'Options du thème',
        'menu_title'	=> 'Options',
        'menu_slug' 	=> 'theme-options',
        'capability'	=> 'edit_posts',
        'redirect'		=> false,
    'position' => '2',
    ));
}
*/

//-----------------------------------------------------
// Changement du slug auteurs
//-----------------------------------------------------

/*
add_action('init', 'generatepress_child_author_base_slug');
function generatepress_child_author_base_slug()
{
    global $wp_rewrite;
    $author_slug = 'auteur';
    $wp_rewrite->author_base = $author_slug;
}
*/

//-----------------------------------------------------
// Exclusion des anciennes catégories de l'admin
//-----------------------------------------------------

/*
add_filter( 'list_terms_exclusions', 'generatepress_child_post_editor_list_terms_exclusions', 10, 2 );
function generatepress_child_post_editor_list_terms_exclusions( $exclusions, $args ) {
	global $pagenow;
 
	if ( in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
		$exclusions = " {$exclusions} AND t.slug NOT IN ('actu', 'animaux', 'astuces', 'people')";
	}
	return $exclusions;
}
*/
