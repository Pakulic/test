<?php
/**
 * FAQ Block Template.
 */

wp_enqueue_script( 'acf-faq-faq' );

$class_name = 'faq-block';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}

if( have_rows('questions') ): $counter = 0; ?>
<div class="<?php echo $class_name; ?>">
<?php while( have_rows('questions') ) : the_row(); ?>
<div class="faq-container<?php echo (++$counter === 1 ? ' active' : ''); ?>">
<div class="faq-question">
<?php echo generate_child_get_svg_icon('chevron-down', 20); the_sub_field('question'); ?>
</div>
<div class="faq-answer"><?php the_sub_field('reponse'); ?></div>
</div>
<?php endwhile; ?>
</div>
<?php else : ?>
<p><?php _e('There are no questions in this FAQ block at this time.', 'generateperf'); ?></p>
<?php endif; ?>