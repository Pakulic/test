<?php
if ($query->have_posts()) :
?>
    <form id="sim-recharge-form" class="widget-sim-form" method="POST">
        <label class="gb-headline gb-headline-text" for="sim-recharge-select"><?php the_field($title, 'option'); ?></label>
        <select name="esim-buy" id="sim-recharge-select" class="widget-sim-select">
            <option value=""><?php the_field($placeholder, 'option'); ?>
            </option>
            <?php
            while ($query->have_posts()) : $query->the_post();
                $option = $query->post;
            ?>
                <option class="gb-headline gb-headline-text" value="<?= get_field($link)?>"><?= $option->post_title ?>
                </option>
            <?php
            endwhile;
            ?>
        </select>

        <input type="hidden" class="domain" name="domain" value="<?= WIDGET_SIM_DOMAIN ?>" />
        <div>
            <button type="button" class="widget-sim-btn"><?php the_field($buttonName, 'option'); ?></button>
        </div>
    </form>

<?php
endif;
wp_reset_query();
