<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Désactivation champ url commentaires
//-----------------------------------------------------

add_action('after_setup_theme', 'generatepress_child_remove_comment_url');
function generatepress_child_remove_comment_url()
{
    add_filter('comment_form_default_fields', 'generatepress_child_disable_comment_url', 20);
}

function generatepress_child_disable_comment_url($fields)
{
    unset($fields['url']);
    return $fields;
}

//-----------------------------------------------------
// Affichage du terme "avis" au lieu de "commentaires"
//-----------------------------------------------------

add_filter('generate_leave_comment', 'generatepress_child_custom_leave_comment');
function generatepress_child_custom_leave_comment()
{
    $label = array(
        'reviews' => __('Share your opinion', 'generateperf'),
        'comments' => __('Post a comment', 'generateperf'),
        'reactions' => __('React to this article', 'generateperf'),
        'answers' => __('Reply to this article', 'generateperf'),
        'observations' => __('Share an observation', 'generateperf'),
        'remarks' => __('Share your feedback', 'generateperf'),
    );

    return $label[get_option('generate_child_comment_texts') ?: 'reviews'];
}

add_filter('generate_post_comment', 'generatepress_child_custom_post_comment');
function generatepress_child_custom_post_comment()
{
  $label = array(
    'reviews' => __('Publish my opinion', 'generateperf'),
    'comments' => __('Post my comment', 'generateperf'),
    'reactions' => __('Share my reaction', 'generateperf'),
    'answers' => __('Reply to this article', 'generateperf'),
    'observations' => __('Share my observation', 'generateperf'),
    'remarks' => __('Share my feedback', 'generateperf'),
);
  return $label[get_option('generate_child_comment_texts') ?: 'reviews'];
}

add_filter('generate_comment_form_title', function () {
    $comments_number = get_comments_number();
    $label = array(
        'reviews' => __('opinions', 'generateperf'),
        'comments' => __('comments', 'generateperf'),
        'reactions' => __('reactions', 'generateperf'),
        'answers' => __('replies', 'generateperf'),
        'observations' => __('observations', 'generateperf'),
        'remarks' => __('feedbacks', 'generateperf'),
    );

    return number_format_i18n($comments_number) . ' '.$label[get_option('generate_child_comment_texts') ?: 'reviews'].' sur « ' . get_the_title() . ' »';
});

//-----------------------------------------------------
// Affichage des dates de commentaires dans les différentes dates
//-----------------------------------------------------

add_filter('get_comment_date', 'generatepress_child_reformat_comment_date');
function generatepress_child_reformat_comment_date($comment_date)
{
    if (!is_admin()) {
        $comment_date = sprintf(__('On %s', 'generateperf'), $comment_date);
    }
    return $comment_date;
}

//-----------------------------------------------------
// Suppression des commentaires dans la barre d'admin
//-----------------------------------------------------

add_action('init', 'generatepress_child_remove_admin_bar_comments');
function generatepress_child_remove_admin_bar_comments() {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
}

//-----------------------------------------------------
// Désactivation des commentaires
//-----------------------------------------------------

if(get_option('generate_child_disable_comments')){
    add_filter('comments_open', '__return_false', 20, 2);
    add_filter('pings_open', '__return_false', 20, 2);
    add_filter('comments_array', '__return_empty_array', 10, 2);
}
