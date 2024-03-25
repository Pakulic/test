<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Shortcode d'affichage du lien vers les archives récentes
//-----------------------------------------------------

add_shortcode('archives_link', 'generateperf_recent_archives_link');
function generateperf_recent_archives_link() {
    $transient_name = 'generateperf_recent_archive_link';
    if ( false === ($link = get_transient( $transient_name ))) {
        $latest_post = get_posts(array(
            'numberposts' => 1,
            'post_status' => 'publish',
        ));
        if ($latest_post) {
            $latest_post_date = new DateTime($latest_post[0]->post_date);
            $year = $latest_post_date->format('Y');
            $month = $latest_post_date->format('m');
            $link = '<a href="' . get_home_url() . '/' . $year . '/' . user_trailingslashit($month) . '">'.__('Site archives', 'generateperf').'</a>';
            set_transient( $transient_name, $link, DAY_IN_SECONDS );
        } else {
            $link = '';
        }
    }
    return $link;
}

//-----------------------------------------------------
// Shortcode d'affichage des top tags
//-----------------------------------------------------

add_shortcode('popular_tags', 'generateperf_popular_tags_shortcode');
function generateperf_popular_tags_shortcode() {
    $args = array(
        'taxonomy' => 'post_tag',
        'orderby'  => 'count',
        'order'    => 'DESC',
        'number'   => 25,
    );
    $tags = get_terms($args);
    if( count($tags) ){
        wp_enqueue_style('badges-pills', get_stylesheet_directory_uri() . '/css/badges-pills.css', array('generate-style'), gp_c_version);
        $output = '<ul class="pills">';
        foreach ($tags as $tag) {
            $tag_link = get_tag_link($tag->term_id);
            $output .= '<li><a href="'.$tag_link.'" title="'.$tag->name.'">'.$tag->name.'</a></li>';
        }
        $output .= '</ul>';
        return $output;
    } else {
        return '<p>' . __('No tags to display', 'generateperf') . '</p>';
    }
}

//-----------------------------------------------------
// Shortcode de listing des tags
//-----------------------------------------------------

add_shortcode('tags_list', 'generateperf_tags_list');
function generateperf_tags_list() {
    $tags = get_tags(array(
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ));
    if (empty($tags)) {
        return '<p>' . __('No tags to display', 'generateperf') . '</p>';
    }

    wp_enqueue_style('tags-list', get_stylesheet_directory_uri() . '/css/tags-list.css', array('generate-style'), gp_c_version);
    wp_enqueue_style('splitted-list', get_stylesheet_directory_uri() . '/css/splitted-list.css', array('generate-style'), gp_c_version);

    $sommaire = '<ul class="tags_list_summary">';
    $sortie   = '';
    $groupe_actuel = '';

    foreach ($tags as $tag) {
        $premiere_lettre = strtoupper(remove_accents(mb_substr($tag->name, 0, 1)));
        $tag_name = mb_strtoupper(mb_substr($tag->name, 0, 1)) . mb_substr($tag->name, 1);
        if ($premiere_lettre !== $groupe_actuel) {
            if ($groupe_actuel !== '') {
                $sortie .= '</ul>';
            }
            $groupe_actuel = $premiere_lettre;
            $sommaire .= '<li><a href="#letter-' . $groupe_actuel . '" class="button">' . $groupe_actuel . '</a></li>';
            $sortie .= '<h2 id="letter-' . $groupe_actuel . '">' . strtoupper($groupe_actuel) . '</h2>';
            $sortie .= '<ul class="splitted-list" data-columns="3">';
        }
        $sortie .= '<li><a href="' . get_tag_link($tag->term_id) . '" class="simple">' . esc_html($tag_name) . '</a></li>';
    }
    $sortie .= '</ul>';
    $sommaire .= '</ul>';
    return $sommaire . $sortie;
}

//-----------------------------------------------------
// Shortcode de sitemap news
//-----------------------------------------------------

add_shortcode('sitemap', 'generateperf_posts_sitemap');
function generateperf_posts_sitemap() {
    global $wpdb;
    $categorie_ids = $wpdb->get_col("SELECT DISTINCT term_taxonomy_id FROM {$wpdb->term_relationships} WHERE object_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish')");
    $args = array(
        'hide_empty' => 1,
        'include'    => $categorie_ids,
    );
    $categories = get_categories($args);
	$sortie = '';
    foreach ($categories as $categorie) {
        $lien_archive = get_category_link($categorie->term_id);
        $sortie .= '<h2><a href="' . esc_url($lien_archive) . '" class="simple">' . esc_html($categorie->name) . '</a></h2>';
        $posts = get_posts(array(
            'category' => $categorie->term_id,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ));
        if (!empty($posts)) {
            $sortie .= '<ul>';
            foreach ($posts as $post) {
                $sortie .= '<li><a href="' . get_permalink($post->ID) . '" class="simple">' . esc_html($post->post_title) . '</a></li>';
            }
            $sortie .= '</ul>';
        }
    }
    return $sortie;
}

//-----------------------------------------------------
// Formulaire de contact : shortcode [contact_form]
//-----------------------------------------------------

add_shortcode('contact_form', 'generatepress_child_contact_form_shortcode');
function generatepress_child_contact_form_shortcode()
{
    // Services de sécurisation
    $services = array(
        'recaptcha' => array(
            'public_key_option' => 'generate_child_recaptcha_public',
            'private_key_option' => 'generate_child_recaptcha_private',
            'api_url' => 'https://www.google.com/recaptcha/api/siteverify',
            'script_url' => 'https://www.google.com/recaptcha/api.js',
            'html_field' => function ($public_key) {
                return '<p class="g-recaptcha" data-theme="light" data-sitekey="' . $public_key . '" style="min-height:78px"></p>';
            },
            'response_key' => 'g-recaptcha-response'
        ),
        'hcaptcha' => array(
            'public_key_option' => 'generate_child_hcaptcha_public',
            'private_key_option' => 'generate_child_hcaptcha_private',
            'api_url' => 'https://hcaptcha.com/siteverify',
            'script_url' => 'https://www.hcaptcha.com/1/api.js',
            'html_field' => function ($public_key) {
                return '<p class="h-captcha" data-sitekey="' . $public_key . '" style="min-height:78px"></p>';
            },
            'response_key' => 'h-captcha-response'
        ),
        'turnstile' => array(
            'public_key_option' => 'generate_child_turnstile_public',
            'private_key_option' => 'generate_child_turnstile_private',
            'api_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            'script_url' => 'https://challenges.cloudflare.com/turnstile/v0/api.js',
            'html_field' => function ($public_key) {
                return '<p class="cf-turnstile" data-sitekey="' . $public_key . '" style="min-height:65px"></p>';
            },
            'response_key' => 'cf-turnstile-response'
        ),
    );

    // Chercher le service actif
    $active_service = get_option('generate_child_contact_captcha_partner');
    if ($active_service) {
        wp_enqueue_script('captcha-'.$active_service, $services[$active_service]['script_url'], array(), gp_c_version, true);
    }

    global $wp;
    $return = '';

    // Traitement du formulaire
    if (isset($_POST['submit']) and isset($_POST['email']) and !empty($_POST['email']) and isset($_POST['texte']) and !empty($_POST['texte'])) {

        if ($active_service) {
            $validation_data = [
                'secret'   => get_option('generate_child_contact_captcha_private'),
                'response' => $_POST[$services[$active_service]['response_key']],
                'remoteip' => $_SERVER['REMOTE_ADDR'],
             ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $services[$active_service]['api_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $validation_data);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $decode = json_decode(curl_exec($ch), true);
        } else {
            $decode['success'] = true;
        }

        if ($decode['success'] == true) {
            $headers = 'Return-Path: ' . get_bloginfo('admin_email') . "\n";
            $headers .= 'From: "' . get_bloginfo('admin_email') . '" <' . get_bloginfo('admin_email') . '>' . "\n";
            $headers .= 'X-Mailer: ' . get_bloginfo('name') . ' PHP MailSender' . "\n";
            $headers .= 'Reply-To: ' . sanitize_email($_POST['email']) . "\n";
            $headers .= 'Organization: ' . get_bloginfo('wpurl') . "\n";
            $headers .= 'X-Priority: 3 (Normal)' . "\n";
            $headers .= 'Mime-Version: 1.0' . "\n";
            $headers .= 'Content-type: text/html; charset= UTF-8' . "\n";
            $headers .= 'Content-Transfer-Encoding: 8bit' . "\n";
            $headers .= 'Date:' . date("r") . "\n";
            wp_mail(get_option('generate_child_contact_form_email', get_bloginfo('admin_email')), 'Contact via ' . get_bloginfo('name') . '(' . sanitize_email($_POST['email']) . ')', '<html><head></head><body>' . nl2br(stripslashes(sanitize_textarea_field($_POST['texte']))) . '</body></html>', $headers);
            $message = 'Votre email a bien été envoyé. Nous reviendrons vers vous au plus vite. Merci et à très bientôt.';
            $email = '';
            $texte = '';
        } else {
            $message = 'Merci de vous identifier comme un humain.';
            $email = stripslashes(sanitize_email($_POST['email']));
            $texte = stripslashes(sanitize_textarea_field($_POST['texte']));
        }
    } elseif (isset($_POST['submit'])) {
        $message = 'Merci de saisir votre adresse email et message dans les champs ci-dessous.';
        $email = stripslashes(sanitize_email($_POST['email']));
        $texte = stripslashes(sanitize_textarea_field($_POST['texte']));
    } else {
        $message = false;
        $email = '';
        $texte = '';
    }

    if ($message) {
        $return .= '<div style="position:relative;padding:.75rem 1.25rem;margin-bottom:1rem;border:1px solid transparent;border-radius:.25rem;color:#0c5460;background-color:#d1ecf1;border-color:#bee5eb;" role="alert">' . $message . '</div>';
    }

    $return .= '<form method="post" name="contact" action="' . home_url($wp->request) . '"><p><label for="email">Votre e-mail&nbsp;:</label><br><input type="email" id="email" name="email" autocomplete="email" value="' . $email . '" required></p><p><label for="message">Votre message&nbsp;:</label><br><textarea id="message" name="texte" cols="45" rows="8" required>' . $texte . '</textarea></p>';

    if ($active_service) {
        $return .= ($services[$active_service]['html_field'])(get_option('generate_child_contact_captcha_public'));
    }

    $return .= '<p><input type="submit" name="submit" class="button" value="' . __('Send', 'generateperf') . '"></p></form>';

    return $return;
}

//-----------------------------------------------------
// Liste des contributeurs : shortcode [team_list]
//-----------------------------------------------------

add_shortcode('team_list', 'generatepress_child_team_list_shortcode');
function generatepress_child_team_list_shortcode()
{
    $team = get_users(
        array(
            'role__in' => array('administrator', 'author', 'editor', 'contributor'),
            'has_published_posts' => array('post'),
            'orderby' => 'post_count',
            'order' => 'DESC',
        ),
    );
    if(!is_array($team)) return '';
    $content = '<div class="authors-list">';
    foreach ($team as $user) {
        $content .= '<div class="author-box" itemprop="author" itemscope="itemscope" itemtype="http://schema.org/Person">';
        if(get_avatar_url($user->ID)){
            $content .= '<div class="avatar"><img src="' . get_avatar_url($user->ID) . '" width="100" height="100"></div>';
        } else {
            $content .= '<div class="avatar">'.generate_child_get_svg_icon('person-fill', 'Bootstrap', $dimension = '100').'</div>';
        }
        if (get_option('generate_child_author_link')) {
            $author = '<a class="author-name simple" href="'.get_author_posts_url($user->ID).'" itemprop="name">'.esc_html($user->display_name).'</a>';
        } else {
            $author = '<span itemprop="name">'.esc_html($user->display_name).'</span>';
        }
        $content .= '<div><h4>' . $author . '</h4>';
        if (get_user_meta($user->ID, 'description', true)) {
            $content .= '<p itemprop="description">'.get_user_meta($user->ID, 'description', true).'</p>';
        }
        $content .= '</div></div>';
    }
    $content .= '</div>';
    return $content;
}

//-----------------------------------------------------
// Chargement du CSS pour le shortcode [team_list]
//-----------------------------------------------------

add_action( 'wp_enqueue_scripts', 'generatepress_child_team_list_css');
function generatepress_child_team_list_css() {
    global $post;
    if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'team_list') ) {
        wp_enqueue_style('authors', get_stylesheet_directory_uri() . '/css/authors.css', array(), gp_c_version);
    }
}

//-----------------------------------------------------
// Pour afficher les listes de custom taxonomies en petit (liste)
//-----------------------------------------------------

/*
add_shortcode('custom_tax_cloud', 'generatepress_child_display_custom_tax_cloud');
function generatepress_child_display_custom_tax_cloud( $atts )
{

    // Attributes
	$atts = shortcode_atts(
		array(
			'term' => 'sportifs',
            'limit' => 10,
		),
		$atts,
		''
	);

    $terms = get_terms([
        'taxonomy' => $atts['term'],
        'hide_empty' => true,
        'orderby' => 'count',
        'order' => 'DESC',
        'number' => $atts['limit'],
    ]);

    $content = '<ul>';
    foreach ($terms as $term){
        $content .= '<li><a href="'.get_term_link($term->term_id).'">'.$term->name.'</a></li>';
      }
    $content .= '</ul>';

    return $content;
}
*/

//-----------------------------------------------------
// Pour afficher les listes de custom taxonomies avec thumbnails
//-----------------------------------------------------

/*
add_shortcode('custom_tax', 'generatepress_child_display_custom_tax');
function generatepress_child_display_custom_tax( $atts )
{
    // Attributes
	$atts = shortcode_atts(
		array(
			'term' => '',
		),
		$atts,
		''
	);

    $terms = get_terms([
        'taxonomy' => $atts['term'],
        'hide_empty' => true,
    ]);

    $content = '<ul class="wp-block-latest-posts__list is-grid columns-3 has-dates wp-block-latest-posts">';
    foreach ($terms as $term){

        $args = array(
            'post_type' =>'post',
            'posts_per_page' => 1,
            'orderby'=>'date',
            'order' => 'DESC',
            'tax_query' => array(
                array (
                    'taxonomy' => $atts['term'],
                    'field' => 'slug',
                    'terms' => $term->slug,
                )
            ),
        );
        $tmp_query = new WP_Query($args);
        if ($tmp_query->have_posts()) {

        $content .= '<li><div class="wp-block-latest-posts__featured-image">'.get_the_post_thumbnail($tmp_query->posts[0]->ID, 'medium').'</div>
        <a href="'.get_term_link($term->term_id).'">'.$term->name.'</a>';
            if($term->count == 1){
                $content .= '<span class="wp-block-latest-posts__post-date">'.$term->count.' article</span>';
            } else {
                $content .= '<span class="wp-block-latest-posts__post-date">'.$term->count.' articles</span>';
            }
        $content .= '</li>';

        }

      }
      $content .= '</ul>';


    return $content;
}
*/