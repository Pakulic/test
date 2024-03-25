<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Fonction de récupération des notes moyennes
//-----------------------------------------------------

function generateperf_get_ratings_data($post_id) {
    $transient_key = 'ratings_data_' . $post_id;

    // Essayez d'obtenir les données de rating depuis les transients
    $cached_data = get_transient($transient_key);

    if ($cached_data !== false) {
        return $cached_data;
    }

    // Obtenir les données des post meta
    $post_score = get_post_meta($post_id, '_score', true) ?: 0;
    $post_votes = get_post_meta($post_id, '_votes', true) ?: 0;

    // Obtenir les données des commentaires
    $comments = get_comments(['post_id' => $post_id]);
    $comment_score_sum = 0;
    $comment_votes = 0;
    
    foreach ($comments as $comment) {
        $comment_rating = get_comment_meta($comment->comment_ID, 'rating', true);
        if($comment_rating) {
            $comment_score_sum += $comment_rating;
            $comment_votes++;
        }
    }

    // Calculer les totaux
    $total_score = $post_score + $comment_score_sum;
    $total_votes = $post_votes + $comment_votes;

    // Calculer et retourner la note moyenne et le nombre total de votes
    if($total_votes === 0) {
        return false;
    }

    $rating_data = [
        'rating' => $total_score / $total_votes,
        'number' => $total_votes
    ];

    // Mettre les données en cache pendant 24h (86400 secondes)
    set_transient($transient_key, $rating_data, 86400);

    return $rating_data;
}

//-----------------------------------------------------
// Enregistrement des notes au clic via l'API Rest
//-----------------------------------------------------

function generateperf_handle_vote_request( WP_REST_Request $request ) {
    $post_id = intval($request['postId']);
    $score = intval($request['score']);

    if( $post_id && $score ) {
        $current_score = get_post_meta($post_id, '_score', true) ?: 0;
        $current_votes = get_post_meta($post_id, '_votes', true) ?: 0;

        $new_total_votes = $current_votes + 1;
        $new_total_score = $current_score + $score;

        update_post_meta($post_id, '_score', $new_total_score);
        update_post_meta($post_id, '_votes', $new_total_votes);

        // Nettoyage
        delete_transient('ratings_data_' . $post_id);
        if( function_exists('rocket_clean_post') ){
            rocket_clean_post( $post_id );
        }

        return new WP_REST_Response(array('success' => true), 200);
    }

    return new WP_REST_Response(array('success' => false), 400);
}

add_action( 'rest_api_init', function() {
    register_rest_route( 'generateperf', '/vote', array(
        'methods' => 'POST',
        'callback' => 'generateperf_handle_vote_request',
    ));
});

//-----------------------------------------------------
// Affichage des étoiles sous les articles
//-----------------------------------------------------

add_action('generate_after_content', 'generateperf_add_star_rating', 0);

function generateperf_add_star_rating() {
    if(generate_child_option_active_on_current_page('generate_child_ratings')) {
        $post_id = get_the_ID();
        $post_rating = generateperf_get_ratings_data($post_id);
        $average_score = 0;
        $average_score_rounded = 0;
        $votes = 0;
        if(false !== $post_rating){
            $average_score = $post_rating['rating'];
            $average_score_rounded = round($average_score);
            $votes = $post_rating['number'];
        }

        echo '<div id="rate-content" class="stars-rater">';
        echo '<h2>'.__('Give your feedback', 'generateperf').'</h2><div class="stars-rating" data-postid="' . esc_attr($post_id) . '">';
        for($i=1; $i<=5; $i++) {
            $active_class = $i <= $average_score_rounded ? 'active' : '';
            echo '<span class="star ' . esc_attr($active_class) . '" data-rating="' . esc_attr($i) . '">★</span>';
        }
        echo '</div>';

        echo '<p class="vote-info">';
            if($votes > 0) {
                echo esc_html(number_format($average_score, 1)) . '/5';
                echo ' <span class="o50">·</span> ';
                echo __('based on', 'generateperf') . ' ' . esc_html($votes) . ' ';
                echo $votes > 1 ? __('ratings', 'generateperf') : __('rating', 'generateperf');
            } else {
                $postType = get_post_type_object(get_post_type());
                $label = $postType->labels->singular_name;
                echo sprintf(__('Be the first to rate this %s', 'generateperf'), $label);
            }
            if( comments_open($post_id) ){
                echo '<br>';
                echo __('or <a href="#comments">leave a detailed review</a>', 'generateperf');
            }
        echo '</p>';
        echo '</div>';
    }
}

//-----------------------------------------------------
// Génération des étoiles visuelles en fonction de la note (1 à 5)
//-----------------------------------------------------

function generatepress_child_display_stars($nb)
{
    $rounded_nb = round($nb);
    if (empty($nb) || !is_numeric($nb) || $nb <= 0 || $nb > 5) {
        return ;
    }
    $content = '<span title="' . sprintf(__('Rated %1$d/5', 'generateperf'), number_format($nb, 1)) . '">';
    for ($i = 0; $i < $rounded_nb; ++$i) {
        $content .= '<span class="star-rated">&#9733;</span>';
    }
    for ($i = $rounded_nb; $i < 5; ++$i) {
        $content .= '<span class="star-not-rated">&#9733;</span>';
    }
    $content .= '</span>';
    return $content;
}

//-----------------------------------------------------
// Affichage d'étoiles dans les commentaires en front
//-----------------------------------------------------

add_filter('get_comment_author', 'generatepress_child_ratings_in_comment_edit', 10, 3);
function generatepress_child_ratings_in_comment_edit($author, $comment_ID, $comment)
{
    if (generate_child_option_active_on_current_page('generate_child_ratings') && !did_action('generate_after_main_content')) {
        $rating = get_comment_meta($comment_ID, 'rating', true);
        if ($rating) {
            $author = $author . '</cite> <span class="o50">&middot;</span> <cite>' . generatepress_child_display_stars($rating);
        }
    }
    return $author;
}

//-----------------------------------------------------
// Ajout du champ de notation dans les commentaires
//-----------------------------------------------------

add_action('comment_form_logged_in_after', 'generatepress_child_additional_fields');
add_action('comment_form_before_fields', 'generatepress_child_additional_fields');
function generatepress_child_additional_fields()
{
  if(generate_child_option_active_on_current_page('generate_child_ratings')){
    echo '<div class="star-container"><label>' . __('Rate:', 'generateperf') . '</label><div class="star-position"><div class="star-rating">';
    for ($i=5; $i > 0; --$i) {
        echo '<input type="radio" id="star'.$i.'" name="rating" value="'.$i.'"><label for="star'.$i.'">&#9733;</label>';
    }
    echo '</div></div></div>';
  }
}

//-----------------------------------------------------
// Enregistrement de la note en meta pour les visiteurs
//-----------------------------------------------------

add_action('comment_post', 'generatepress_child_save_comment_rating', 10, 1);
function generatepress_child_save_comment_rating($comment_id)
{
    if ((isset($_POST['rating'])) && (!empty($_POST['rating']))) {
        $rating = intval($_POST['rating']);
        update_comment_meta($comment_id, 'rating', $rating);
        $comment_data = get_comment($comment_id);
        delete_transient('ratings_data_' . $comment_data->comment_post_ID);
    }
}

//-----------------------------------------------------
// Mise à jour de la note moyenne lors des changements de statuts
//-----------------------------------------------------

add_action('transition_comment_status', 'generatepress_child_approve_comments', 10, 3);
function generatepress_child_approve_comments($new_status, $old_status, $comment_data) {
    delete_transient('ratings_data_' . $comment_data->comment_post_ID);
}

//-----------------------------------------------------
// Affichage de la note moyenne dans les meta de l'article
//-----------------------------------------------------

add_filter('generate_post_author_output', 'generatepress_child_add_ratings_to_post', 10, 1);
function generatepress_child_add_ratings_to_post($output)
{
    if (generate_child_option_active_on_current_page('generate_child_ratings')) {
        $post_rating = generateperf_get_ratings_data(get_the_ID());
        if (false !== $post_rating) {
            $output .= ' <span class="o50">&middot;</span> <a href="#rate-content" class="meta-item">'.generatepress_child_display_stars($post_rating['rating']).'</a>';
        }
    }
    return $output ;
}

//-----------------------------------------------------
// Ajout du code json de balisage sémantique
//-----------------------------------------------------

add_action('wp_head', 'generatepress_child_json_schema', 3);
function generatepress_child_json_schema()
{
    if (generate_child_option_active_on_current_page('generate_child_ratings')) {
        $post_rating = generateperf_get_ratings_data(get_the_ID());
        if (false !== $post_rating) {
            echo "\n".'<script type="application/ld+json">'."\n";
            echo '{'."\n";
            echo '    "@context": "http://schema.org",'."\n";
            echo '    "@type": "CreativeWorkSeries",'."\n";
            echo '    "name": "'.htmlentities(get_the_title(), ENT_QUOTES).'",'."\n";
            echo '    "aggregateRating": {'."\n";
            echo '        "@type": "AggregateRating",'."\n";
            echo '        "ratingValue": "'.$post_rating['rating'].'",'."\n";
            echo '        "bestRating": "5",'."\n";
            echo '        "ratingCount": "'.$post_rating['number'].'"'."\n";
            echo '    }'."\n";
            echo '}'."\n";
            echo '</script>'."\n";
        }
    }
}
