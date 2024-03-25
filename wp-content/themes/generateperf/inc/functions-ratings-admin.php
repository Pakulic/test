<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Affichage d'étoiles dans la description des commentaires en admin
//-----------------------------------------------------

add_filter('comment_text', 'generatepress_child_comments_stars', 10, 3);
function generatepress_child_comments_stars($comment_text, $comment, $args)
{
    $rating = get_comment_meta($comment->comment_ID, 'rating', true);
    if ($rating) {
        $comment_text = '<p>' . generatepress_child_display_stars($rating) . '</p>' . $comment_text;
    }
    return $comment_text;
}

//-----------------------------------------------------
// Modification des notes dans l'admin
//-----------------------------------------------------

add_action('add_meta_boxes_comment', 'generatepress_child_extend_comment_rating');
function generatepress_child_extend_comment_rating()
{
    add_meta_box('rating', 'Note', 'generatepress_child_extend_comment_meta_box', 'comment', 'normal', 'high');
}

function generatepress_child_extend_comment_meta_box($comment)
{
    $rating = get_comment_meta($comment->comment_ID, 'rating', true);

    echo '<div>';
    for ($i=0; $i <= 5; ++$i) {
        if ($i == 0) {
            $i = '';
        }
        echo '<input type="radio" id="star'.$i.'" name="rating" value="'.$i.'" ';
        if ($rating == $i) {
            echo ' checked="checked"';
        }
        echo '><label for="star'.$i.'">'.(empty($i) ? '(aucune)' : $i).'</label> &nbsp; ';
    }
    echo '</div>';
}

//-----------------------------------------------------
// Enregistrement de la note en meta dans l'administration
//-----------------------------------------------------

add_filter('comment_edit_redirect', 'generatepress_child_save_admin_comments', 10, 2);
function generatepress_child_save_admin_comments($location, $comment_id)
{
    if (isset($_POST['rating']) && !empty($_POST['rating'])) {
        update_comment_meta($comment_id, 'rating', intval($_POST['rating']));
        $comment_data = get_comment($comment_id);
        delete_transient('ratings_data_' . $comment_data->comment_post_ID);
    }

    return $location;
}

//-----------------------------------------------------
// CSS dans l'admin en affichage des commentaires
//-----------------------------------------------------

add_action('admin_print_styles', 'generatepress_child_admin_ratings_css', PHP_INT_MAX);
function generatepress_child_admin_ratings_css()
{
    global $current_screen;
    if ($current_screen->id == 'edit-comments' || $current_screen->id == 'comment') {
        wp_enqueue_style('ratings', get_stylesheet_directory_uri() . '/css/ratings.css', array(), gp_c_version);
    }
}
