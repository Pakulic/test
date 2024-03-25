<?php

if (!empty($related_posts)) {

    echo '<div class="related-articles">';
    echo '<h3>À voir aussi</h3>';
    echo '<ul class="wp-block-latest-posts__list is-grid columns-3 has-dates has-author wp-block-latest-posts">';

    array_walk(
        $related_posts,
        function ($related_post_id) {
            echo '<li>';
            echo '<div class="wp-block-latest-posts__featured-image aligncenter">';
            echo get_the_post_thumbnail($related_post_id);
            echo '</div>';
            echo '<a href="'.get_permalink($related_post_id).'">'.get_the_title($related_post_id).'</a>';
            echo '</li>';
        }
    );

    echo '</ul>';
    echo '</div>';

}
