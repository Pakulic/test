<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Ajout de la gestion des blocs dans Apparence
//-----------------------------------------------------

add_action('admin_menu', 'generatepress_child_reusable_links_linked_url');
function generatepress_child_reusable_links_linked_url()
{
    global $submenu;
    $submenu['themes.php'][] = array( __('Patterns', 'generateperf'), 'manage_options', get_admin_url() . 'edit.php?post_type=wp_block' );
}

//-----------------------------------------------------
// CSS du paneau de gestion personnalisé
//-----------------------------------------------------

add_action('admin_enqueue_scripts', 'generatepress_child_admin_panel_css');

function generatepress_child_admin_panel_css($hook)
{
    $current_screen = get_current_screen();
    if ($current_screen->base == 'settings_page_generateperf') {
        wp_enqueue_style('generatepress_child_admin_panel', get_stylesheet_directory_uri() . '/backend/css/panel.css');
        wp_enqueue_script('generatepress_child_admin_panel_tabs', get_stylesheet_directory_uri() . '/backend/js/panel-tabs.js', array('jquery'));
        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');
    }
    else {
        return;
    }
}

//-----------------------------------------------------
// CSS dans l'admin aussi, notamment les fontes
//-----------------------------------------------------

add_action('after_setup_theme', 'generatepress_child_admin_setup');
function generatepress_child_admin_setup()
{
    add_theme_support('editor-styles');
    add_editor_style('style.css');
    $active_fonts = generatepress_child_active_fonts();
    $local_fonts = generatepress_child_local_fonts();
    foreach ($active_fonts as $font_name) {
        if (!empty($font_name) && array_key_exists($font_name, $local_fonts)) {
            add_editor_style('css/local-fonts-'.$local_fonts[$font_name].'.css');
        }
    }
}

function generatepress_child_custom_dashboard_display()
{
    $elements = array();

    if (!defined('WP_ROCKET_VERSION')) {
        $elements['WP Rocket'] = array(
            'bg-color' => '#192250',
            'color' => '#fff',
            'image' => 'wp-rocket.svg',
            'image-w' => '240',
            'image-h' => '67',
            'cta' => 'Obtenir WP Rocket',
            'cta-color' => '#fff',
            'cta-bg-color' => '#f56f46',
            'link' => 'https://wp-rocket.me/?ref=08171dc2',
            'content' => 'Vous n’avez pas installé de plugin pour optimiser la web performance. Nous vous recommandons chaudement WP Rocket, avec lequel GeneratePerf fait des merveilles.',
        );
    }

    if (!defined('IMAGIFY_VERSION')) {
        $elements['Imagify'] = array(
            'bg-color' => '#1F2332',
            'color' => '#fff',
            'image' => 'imagify.svg',
            'image-w' => '300',
            'image-h' => '35',
            'cta' => 'Essayez gratuitement',
            'cta-color' => '#fff',
            'cta-bg-color' => '#40B1D0',
            'link' => get_admin_url(null, 'plugin-install.php?tab=plugin-information&plugin=imagify&TB_iframe=true&width=640&height=550'),
            'content' => 'Imagify est le meilleur service de compression d’images pour WordPress. Il allège votre bibliothèque média et génère des alternatives Webp pour les navigateurs qui le supportent.',
        );
    }

    if (!defined('GP_PREMIUM_VERSION')) {
        $elements['GeneratePress Premium'] = array(
            'bg-color' => '#181b29',
            'color' => '#fff',
            'image' => 'generatepress-premium.svg',
            'image-w' => '280',
            'image-h' => '64',
            'cta' => 'Débrider mon GeneratePress',
            'cta-color' => '#fff',
            'cta-bg-color' => '#00bcf5',
            'link' => 'https://generatepress.com/premium/?ref=4657',
            'content' => 'GeneratePress Premium permet d’aller beaucoup, beaucoup plus loin que la version standard et gratuite. Débridez tout le potentiel de ce formidable thème WordPress dès maintenant !',
        );
    }

    if (!defined('GENERATEBLOCKS_PRO_VERSION')) {
        $elements['GenerateBlocks Pro'] = array(
            'bg-color' => '#f7f8f9',
            'color' => '#000',
            'image' => 'generateblocks.svg',
            'image-w' => '260',
            'image-h' => '73',
            'cta' => 'En profiter dès maintenant',
            'cta-color' => '#fff',
            'cta-bg-color' => '#000',
            'link' => 'https://generateblocks.com/pro/?ref=7',
            'content' => 'GenerateBlocks Pro offre des possibilités de conception infinies avec un minimum d’impact sur la performance de votre site. C’est le complément idéal de l’éditeur natif Gutenberg.',
        );
    }

    if (!defined('WPSEO_VERSION')) {
        $elements['Yoast SEO'] = array(
            'bg-color' => '#f4f1f4',
            'color' => '#000',
            'image' => 'yoast-seo.svg',
            'image-w' => '200',
            'image-h' => '92',
            'cta' => 'Installer Yoast',
            'cta-color' => '#fff',
            'cta-bg-color' => '#a4286a',
            'link' => get_admin_url(null, 'plugin-install.php?tab=plugin-information&plugin=wordpress-seo&TB_iframe=true&width=640&height=550'),
            'content' => 'Yoast SEO est le plugin dédié au SEO le plus populaire. C’est celui que nous vous recommandons, et que GeneratePerf utilise pour améliorer certaines composantes SEO on-site.',
        );
    }

    $elements['Agence Web Performance'] = array(
        'bg-color' => '#F1F2F6',
        'color' => '#04062C',
        'image' => 'agence-web-performance.svg',
        'image-w' => '240',
        'image-h' => '59',
        'cta' => 'Se faire accompagner',
        'cta-color' => '#fff',
        'cta-bg-color' => '#04062C',
        'link' => 'https://agencewebperformance.fr/?utm_source=generateperf&utm_campaign='.urlencode(get_site_url()),
        'content' => 'Ce thème enfant a été développé par l’Agence Web Performance. Vous avez besoin de support, d’un accompagnement ou avez un nouveau projet de site performant ? Prenez contact avec nous !',
    );

    // S'il reste des étapes à finaliser
    if (count($elements) > 0) {
        echo '<h3>Pour tirer le meilleur de WordPress + GeneratePress !</h3>';
        echo '<div class="scards">';
        foreach ($elements as $el_title => $el_val) {
            if(str_contains($el_val['link'], 'plugin-install.php')){
                $classes = ' thickbox open-plugin-details-modal';
            } else {
                $classes = '';
            }
            echo '<a class="scard'.$classes.'" href="'.$el_val['link'].'" target="_blank" rel="nofollow noopener" style="background-color:'.$el_val['bg-color'].';color:'.$el_val['color'].';border:3px solid '.$el_val['cta-bg-color'].';">';
            echo '<img alt="'.$el_title.'" width="'.$el_val['image-w'].'" height="'.$el_val['image-h'].'" src="'.get_stylesheet_directory_uri().'/backend/images/'.$el_val['image'].'">';
            echo '<p>'.$el_val['content'].'</p>';
            echo '<span class="button" style="background-color:'.$el_val['cta-bg-color'].';border-color:'.$el_val['cta-bg-color'].';color:'.$el_val['cta-color'].'">'.$el_val['cta'].'</span>';
            echo '</a>';
        }
        echo '</div>';
    }

}

//-----------------------------------------------------
// Génération des sections
//-----------------------------------------------------

function generatepress_child_get_panel_tabs(){
    return array(
        'published' => array(
            'title' => __('Publication dates', 'generateperf'),
            'dashicon' => 'clock',
            'description' => '<p>Gérez la façon dont les dates de publication sont affichées sur votre site.</p>',
        ),
        'subtitles' => array(
            'title' => __('Subtitles', 'generateperf'),
            'dashicon' => 'paperclip',
            'description' => '<p>Activez les sous-titres sur certains élements du site en 1 clic.</p>',
        ),
        'sources' => array(
            'title' => __('Sources', 'generateperf'),
            'dashicon' => 'text',
            'description' => '<p>Citez les sources de vos contenus et images afin d’envoyer des signaux positifs à Google.</p>',
        ),
        'toc' => array(
            'title' => __('Summary', 'generateperf'),
            'dashicon' => 'editor-ul',
            'description' => '<p>Affichez automatiquement un joli sommaire rétractable à vos publications sans le moindre plugin.</p>',
        ),
        'views' => array(
            'title' => __('Views count', 'generateperf'),
            'dashicon' => 'visibility',
            'description' => '<p>Comptez le nombre de vues de vos publications, y compris avec un plugin de cache, pour détecter automatiquement vos contenus les plus populaires.</p>',
        ),
        'reading' => array(
            'title' => __('Reading time', 'generateperf'),
            'dashicon' => 'hourglass',
            'description' => '<p>Informez vos visiteurs sur la durée de lecture de vos publications via différents outils.</p>',
        ),
        'relative' => array(
            'title' => __('Related content', 'generateperf'),
            'dashicon' => 'networking',
            'description' => '<p>Affichez des encarts présentant des contenus similaires sous une forme attrayante.</p>',
        ),
        'social' => array(
            'title' => __('Social Share', 'generateperf'),
            'dashicon' => 'share',
            'description' => '<p>Gérez la façon dont vous souhaitez proposer des boutons de partage social.</p>',
        ),
        'comments' => array(
            'title' => __('Comments &amp; Reviews', 'generateperf'),
            'dashicon' => 'admin-comments',
            'description' => '<p>Permettez à vos visiteurs d’interagir au mieux avec vos contenus.</p>',
        ),
        'contact' => array(
            'title' => __('Contact form', 'generateperf'),
            'dashicon' => 'email-alt',
            'description' => '<p>Intégrez facilement un formulaire de contact à vos pages grâce au sortcode <code>[contact_form]</code> et sécurisez-le grâce à l’outil anti-spam de votre choix.</p>',
        ),
        'authors' => array(
            'title' => __('Authors', 'generateperf'),
            'dashicon' => 'groups',
            'description' => '<p>Gérez la façon dont les auteurs des publications sont mis en avant sur votre site. Vous avez la possibilité d’intégrer une liste de vos rédacteurs grâce au shortcode <code>[team_list]</code></p>',
        ),
        'search' => array(
            'title' => __('Search', 'generateperf'),
            'dashicon' => 'search',
            'description' => '<p>Gérez certains paramètres du moteur de recherche de votre site.</p>',
        ),
        'consent' => array(
            'title' => __('Consent', 'generateperf'),
            'dashicon' => 'shield',
            'description' => '<p>Mettez votre site en conformité avec le RGPD en utilisant une CMP native ultra-légère. Conditionnez vos appels de scripts en remplaçant l’attribut <code>src</code> par <code>data-behind-cmp</code>.</p>
            <p class="description">Nous recommanons l’utilisation des <a href="'.get_admin_url().'edit.php?post_type=gp_elements">Éléments</a> de GeneratePress pour intégrer les appels avec le hook wp_footer.</p>',
        ),
        'code' => array(
            'title' => __('Custom code', 'generateperf'),
            'dashicon' => 'editor-code',
            'description' => '<p>Intégrez les scripts, tags et balises meta 3rd party de façon optimale.</p>',
        ),
        'performance' => array(
            'title' => __('Web performance', 'generateperf'),
            'dashicon' => 'performance',
            'description' => '<p>Optimisez votre site pour qu’il s’affiche le plus rapidement possible.</p>',
        ),
        'seo' => array(
            'title' => __('SEO', 'generateperf'),
            'dashicon' => 'welcome-view-site',
            'description' => '<p>Modifiez certaines options qui ont impact sur le SEO on-site. Notez que vous avez aussi accès à des shortcodes :<br><code>[sitemap]</code> pour lister les catégories et articles et <code>[tags_list]</code> pour afficher un répertoire des tags utilisés.</p>',
        ),
        'news' => array(
            'title' => __('News', 'generateperf'),
            'dashicon' => 'rss',
            'description' => '<p>Des optimisations destinées spécifiquement aux sites d’actualité ciblant Google News et Google Discover.</p>',
        ),
        '3rdparties' => array(
            'title' => __('3rd party tools', 'generateperf'),
            'dashicon' => 'forms',
            'description' => '<p>Activez certains outils populaires en 1 clic pour en profiter sur votre site.</p>',
        ),
        'i18n' => array(
            'title' => __('Translations', 'generateperf'),
            'dashicon' => 'translation',
            'description' => '<p>Gérez la traduction de votre site avec Polylang.</p>',
        ),
        'style' => array(
            'title' => __('Design', 'generateperf'),
            'dashicon' => 'admin-customizer',
            'description' => '<p>Gérez certains aspects visuels du thème en quelques clics.</p>',
        ),
    );
}

//-----------------------------------------------------
// Ajout de la page d'options et des sections personnalisées
//-----------------------------------------------------

add_action('admin_init', 'generatepress_child_options');
function generatepress_child_options()
{
    register_setting('generatepress_child', 'generatepress_child_settings');

    foreach(generatepress_child_get_panel_tabs() as $id => $values){
        add_settings_section(
            'generatepress_child_settings',
            null,
            null,
            'generatepress_child_'.$id.'_section',
        );
    }
}

//-----------------------------------------------------
// Génération du formulaire de rendu dans l'admin
//-----------------------------------------------------

function generatepress_child_options_page()
{
    global $pagenow;
  
    if ($pagenow == 'options-general.php' && $_GET['page'] == 'generateperf') {
    
        // Header
        echo '<header class="panel-header">
        <h1><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600"><path d="M485.2 427.8l-99.1-46.2 15.8-34c5.6-11.9 8.8-24.3 10-36.7 3.3-33.7-9-67.3-33.2-91.1-8.9-8.7-19.3-16.1-31.3-21.7-11.9-5.6-24.3-8.8-36.7-10-33.7-3.3-67.4 9-91.1 33.2-8.7 8.9-16.1 19.3-21.7 31.3l-15.8 34-30.4 65.2c-.7 1.5-.1 3.3 1.5 4l65.2 30.4 34 15.8 34 15.8 68 31.7 74.7 34.8c-65 45.4-152.1 55.2-228.7 17.4C90.2 447.4 44.1 313.3 97.3 202.6c53.3-110.8 186-158.5 297.8-106.3 88.1 41.1 137.1 131.9 129.1 223.4-.1 1.3.6 2.4 1.7 3l65.6 30.6c1.8.8 3.9-.3 4.2-2.2 22.6-130.7-44-265.4-170.5-323.5-150.3-69-327-4.1-396.9 145.8-70 150.1-5.1 328.5 145.1 398.5 114.1 53.2 244.5 28.4 331.3-52.3 17.9-16.6 33.9-35.6 47.5-56.8 1-1.5.4-3.6-1.3-4.3l-65.7-30.7zm-235-109.6l15.8-34c8.8-18.8 31.1-26.9 49.8-18.1s26.9 31 18.1 49.8l-15.8 34-34-15.8-33.9-15.9z" fill="currentColor"></path></svg>
        GeneratePerf by Agence Web Performance <small>v. '.gp_c_version.'</small></h1></header>';

        // Tabs
        echo '<div id="tabs-section" class="tabs"><ul class="tab-head">';
        echo '<li><a href="#tab-dashboard" class="tab-link active" data-submit="false"><span class="dashicons dashicons-admin-home"></span><span class="tab-label">Tableau de bord</span></a></li>';
        foreach(generatepress_child_get_panel_tabs() as $id => $values){
            echo '<li><a href="#tab-'.$id.'" class="tab-link" data-submit="true"><span class="dashicons dashicons-'.$values['dashicon'].'"></span><span class="tab-label">'.$values['title'].'</span></a></li>';
        }
        echo '</ul>';
        echo '<div class="tab-body">';

        echo '<form action="options.php" method="post">';

        // Tableau de bord
        echo '<section id="tab-dashboard" class="tab-body active">';
        echo '<h2>Tableau de bord</h2>';
        echo '<p>Ce panneau de réglages vous permet de paramétrer votre thème GeneratePerf.</p>';
        echo '<p>Il s’agit d’un thème enfant non officiel pour GeneratePress, également installé sur ce site.</p>';
        echo '<p>Son objectif est de répondre aux besoins de votre site et de vos visiteurs en se reposant sur un nombre minimum de plugins.</p>';
        echo '<p>Toutes ses fonctionnalités sont développées sur-mesure dans le but d’avoir un impact réduit sur la web performance.</p>';
        echo '<p>Gardez enfin en tête que de multiples optimisations SEO et webperf sont déployées automatiquement et de façon transparente, en plus des options accessibles ici.</p>';
        echo '<p>Bonne utilisation !</p>';
        generatepress_child_custom_dashboard_display();
        echo '</section>';

        // Onglets
        foreach(generatepress_child_get_panel_tabs() as $id => $values){
            echo '<section id="tab-'.$id.'" class="tab-body">';
            echo '<h2>'.$values['title'].'</h2>';
            echo '<div class="title-desc">'.$values['description'].'</div>';
            settings_fields('generatepress_child_settings');
            do_settings_sections('generatepress_child_'.$id.'_section');
            echo '</section>';
        }

        echo '<div class="submit-container hidden">';
        submit_button();
        echo '</div>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }
}

//-----------------------------------------------------
// Ajout du lien vers la page d'options dans le menu réglages
//-----------------------------------------------------

add_action('admin_menu', 'generatepress_child_options_in_menu');
function generatepress_child_options_in_menu()
{
    add_options_page(
        __('GeneratePerf Settings', 'generateperf'),
        __('GeneratePerf', 'generateperf'),
        'manage_options',
        'generateperf',
        'generatepress_child_options_page'
    );
}

//-----------------------------------------------------
// Création des champs de type choix de post types
//-----------------------------------------------------

function generatepress_child_get_post_types_checkboxes($option, $exclusions = false) {
    $default_exclusions = ['attachment'];
    $excluded = is_array($exclusions) ? array_merge($default_exclusions, $exclusions) : array_merge($default_exclusions, [$exclusions]);
    $args = ['public' => true];
    $post_types = get_post_types($args, 'objects');
    $active_post_types = json_decode(stripslashes(get_option($option)), true);
    $content = '';
    foreach ($post_types as $post_type) {
        if (!in_array($post_type->name, $excluded)) {
            $isChecked = (is_array($active_post_types) && array_key_exists($post_type->name, $active_post_types)) ? ' checked="checked"' : '';
            $content .= sprintf('<input type="checkbox" id="%s-%s" name="%s[%s]" value="1"%s> <label for="%s-%s">%s</label><br>', 
                $option, $post_type->name, 
                $option, $post_type->name, 
                $isChecked, 
                $option, $post_type->name, 
                $post_type->label
            );
        }
    }
    return $content;
}

//-----------------------------------------------------
// Gestion de l’enregistrement des champs de listes dans des textarea
//-----------------------------------------------------

function generatepress_child_list_field_sanitize($content) {
    $content = sanitize_textarea_field($content);
    if(str_contains($content, PHP_EOL)){
        $content = preg_split('/\n|\r\n?/', $content);
    } else {
        $content = array($content);
    }
    $content = array_unique($content);
    return json_encode($content);
}

//-----------------------------------------------------
// Nettoyage des champs de saisie de code
//-----------------------------------------------------

function generateperf_sanitize_custom_code($html) {
    if (empty($html)) return;
    return preg_replace_callback(
        '/<!--.*?-->|<\/?([^>]+)>/',
        function($matches) {
            if (strpos($matches[0], '<!--') === 0) {
                return $matches[0];
            }
            if(preg_match('/^\/?(script|meta|link)/i', $matches[1])) {
                return $matches[0];
            }
            return '';
        },
        $html
    );
}

//-----------------------------------------------------
// Initialisation des règles de permaliens à l'enregistrement de certaines options
//-----------------------------------------------------

add_action('update_option_generate_child_news_sitemap', 'generateperf_flash_permalinks_rules', 10, 2);
add_action('update_option_generate_child_optimal_post_slugs', 'generateperf_flash_permalinks_rules', 10, 2);
function generateperf_flash_permalinks_rules($old_value, $new_value) {
    if ($old_value !== $new_value) {
        flush_rewrite_rules();
    }
}

//-----------------------------------------------------
// Ajout des champs personnalisés dans les réglages de l'admin
//-----------------------------------------------------

add_action('admin_init', 'generatepress_child_info_field_settings');
function generatepress_child_info_field_settings()
{
    $text_field_args = array(
      'sanitize_callback' => 'sanitize_text_field',
      'default' => null
    );

    $code_field_args = array(
        'sanitize_callback' => 'generateperf_sanitize_custom_code',
        'default' => null
    );

    $list_field_args = array(
        'sanitize_callback' => 'generatepress_child_list_field_sanitize',
        'default' => null
      );

    $array_field_args = array(
      'sanitize_callback' => 'json_encode',
      'default' => '[]'
    );

    register_setting('generatepress_child_settings', 'generate_child_ratings', $array_field_args);
    add_settings_field(
        'generate_child_ratings',
        'Notation des visiteurs avec étoiles',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_ratings');
        },
        'generatepress_child_comments_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_comment_texts', $text_field_args);
    add_settings_field(
        'generate_child_comment_texts',
        'Libellé de l’encart',
        function () {
            $entries = array(
                'reviews' => __('Share your opinion', 'generateperf'),
                'comments' => __('Post a comment', 'generateperf'),
                'reactions' => __('React to this article', 'generateperf'),
                'answers' => __('Reply to this article', 'generateperf'),
                'observations' => __('Share an observation', 'generateperf'),
                'remarks' => __('Share your feedback', 'generateperf'),
            );
            echo '<select name="generate_child_comment_texts">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_comment_texts') ? ' selected="selected"' : '').'>'.$label.'</option>';
            }
            echo '</select>';
        },
        'generatepress_child_comments_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_disable_comments', 'intval');
    add_settings_field(
        'generate_child_disable_comments',
        'Désactiver complètement les commentaires',
        function () {
            echo '<input type="checkbox" name="generate_child_disable_comments" value="1"'.(get_option('generate_child_disable_comments') ? ' checked="checked"' : '').'>';
        },
        'generatepress_child_comments_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_views', $array_field_args);
    add_settings_field(
        'generate_child_views',
        'Décompter les lectures',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_views');
        },
        'generatepress_child_views_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_views_public', $array_field_args);
    add_settings_field(
        'generate_child_views_public',
        'Rendre les lectures publiques',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_views_public');
        },
        'generatepress_child_views_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_views_texts', $text_field_args);
    add_settings_field(
        'generate_child_views_texts',
        'Libellé de l’encart',
        function () {
            $entries = array(
                'read' => sprintf(__( 'read %s times', 'generateperf' ), 'XX'),
                'consultations' => sprintf(__( '%s consultations', 'generateperf' ), 'XX'),
                'views' => sprintf(__( '%s views', 'generateperf' ), 'XX'),
                'displays' => sprintf(__( 'displayed %s times', 'generateperf' ), 'XX'),
                'visits' => sprintf(__( '%s visits', 'generateperf' ), 'XX'),
                'visualizations' => sprintf(__( '%s visualizations', 'generateperf' ), 'XX'),
            );
            echo '<select name="generate_child_views_texts">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_views_texts') ? ' selected="selected"' : '').'>'.$label.'</option>';
            }
            echo '</select>';
        },
        'generatepress_child_views_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_views_archives', $array_field_args);
    add_settings_field(
        'generate_child_views_archives',
        'Classer les archives par populatité',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_views_archives');
        },
        'generatepress_child_views_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_reading_time', $array_field_args);
    add_settings_field(
        'generate_child_reading_time',
        'Durée de lecture estimée',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_reading_time');
        },
        'generatepress_child_reading_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_reading_time_labels', $text_field_args);
    add_settings_field(
        'generate_child_reading_time_labels',
        'Libellé de l’encart',
        function () {
            $entries = array(
                'classic' => __('Reading duration', 'generateperf'),
                'time' => __('Reading time', 'generateperf'),
                'estimation' => __('Estimated length', 'generateperf'),
                'length' => __('Article length', 'generateperf'),
                'takes' => __('It will take you', 'generateperf'),
                'duration' => __('This reading lasts', 'generateperf'),
            );
            echo '<select name="generate_child_reading_time_labels">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_reading_time_labels') ? ' selected="selected"' : '').'>'.$label.'</option>';
            }
            echo '</select>';
        },
        'generatepress_child_reading_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_progress_bar', $array_field_args);
    add_settings_field(
        'generate_child_progress_bar',
        'Barre de progression de lecture',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_progress_bar');
        },
        'generatepress_child_reading_section',
        'generatepress_child_settings',
    );


    register_setting('generatepress_child_settings', 'generate_child_relative_dates', 'intval');
    add_settings_field(
        'generate_child_relative_dates',
        'Afficher des dates relatives',
        function () {
            echo '<input type="checkbox" name="generate_child_relative_dates" value="1"'.(get_option('generate_child_relative_dates') ? ' checked="checked"' : '').'>';
        },
        'generatepress_child_published_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_published_texts', $text_field_args);
    add_settings_field(
        'generate_child_published_texts',
        'Libellé de la date de mise en ligne',
        function () {
            $entries = array(
                'published' => __('Published on', 'generateperf'),
                'uploaded' => __('Posted on', 'generateperf'),
                'added' => __('Added on', 'generateperf'),
                'issued' => __('Issued on', 'generateperf'),
                'released' => __('Released on', 'generateperf'),
                'unveiled' => __('Unveiled on', 'generateperf'),
                'publicized' => __('Publicized on', 'generateperf'),
                'communicated' => __('Press release from', 'generateperf'),
            );
            echo '<select name="generate_child_published_texts">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_published_texts') ? ' selected="selected"' : '').'>'.$label.' XX</option>';
            }
            echo '</select>';
        },
        'generatepress_child_published_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_modified_time', $array_field_args);
    add_settings_field(
        'generate_child_modified_time',
        'Afficher la date de modification',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_modified_time');
        },
        'generatepress_child_published_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_last_modified_texts', $text_field_args);
    add_settings_field(
        'generate_child_last_modified_texts',
        'Libellé de la date de modification',
        function () {
            $entries = array(
                'modified' => __('Modified on', 'generateperf'),
                'modified_last' => __('Last modification on', 'generateperf'),
                'edited' => __('Edited on', 'generateperf'),
                'edited_last' => __('Last edition on', 'generateperf'),
                'updated' => __('Updated on', 'generateperf'),
                'updated_last' => __('Last update on', 'generateperf'),
                'corrected' => __('Corrected on', 'generateperf'),
                'corrected_last' => __('Last correction on', 'generateperf'),
                'revised' => __('Revised on', 'generateperf'),
                'revised_last' => __('Last revision on', 'generateperf'),
                'reviewed' => __('Reviewed on', 'generateperf'),
                'reviewed_last' => __('Last review on', 'generateperf'),
                'fact_checked' => __('Fact checked on', 'generateperf'),
            );
            echo '<select name="generate_child_last_modified_texts">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_last_modified_texts') ? ' selected="selected"' : '').'>'.$label.' XX</option>';
            }
            echo '</select>';
        },
        'generatepress_child_published_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_toc', $array_field_args);
    add_settings_field(
        'generate_child_toc',
        'Sommaire automatique',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_toc');
        },
        'generatepress_child_toc_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_toc_style', $text_field_args);
    add_settings_field(
        'generate_child_toc_style',
        __('Summary style', 'generateperf'),
        function () {
            $entries = array(
                'classic' => __('Classic', 'generateperf'),
                'numbering' => __('Numbering', 'generateperf'),
            );
            echo '<select name="generate_child_toc_style">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_toc_style') ? ' selected="selected"' : '').'>'.$label.'</option>';
            }
            echo '</select>';
        },
        'generatepress_child_toc_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_toc_area', $text_field_args);
    add_settings_field(
        'generate_child_toc_area',
        __('Summary placement', 'generateperf'),
        function () {
            $entries = array(
                'above_content' => __('Above content', 'generateperf'),
                'under_content' => __('Under content', 'generateperf'),
                'sidebar_top' => __('Sidebar top', 'generateperf'),
            );
            echo '<select name="generate_child_toc_area">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_toc_area') ? ' selected="selected"' : '').'>'.$label.'</option>';
            }
            echo '</select>';
        },
        'generatepress_child_toc_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_toc_copy_links', 'intval');
    add_settings_field(
        'generate_child_toc_copy_links',
        __('Add copy links to titles', 'generateperf'),
        function () {
            echo '<input type="checkbox" name="generate_child_toc_copy_links" value="1"'.(get_option('generate_child_toc_copy_links') ? ' checked="checked"' : '').'>';
        },
        'generatepress_child_toc_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_toc_label', $text_field_args);
    add_settings_field(
        'generate_child_toc_label',
        'Label du sommaire',
        function () {
            $entries = array(
                'summary' => ucfirst(__('summary', 'generateperf')),
                'toc' => ucfirst(__('table of content', 'generateperf')),
                'index' => ucfirst(__('index', 'generateperf')),
                'abstract' => ucfirst(__('abstract', 'generateperf')),
                'sections' => ucfirst(__('sections', 'generateperf')),
                'titles' => ucfirst(__('titles', 'generateperf')),
            );
            echo '<select name="generate_child_toc_label">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_toc_label') ? ' selected="selected"' : '').'>'.$label.'</option>';
            }
            echo '</select>';
        },
        'generatepress_child_toc_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_toc_texts', $text_field_args);
    add_settings_field(
        'generate_child_toc_texts',
        'Libellé du toggle',
        function () {
            $entries = array(
                'show' => __('Show', 'generateperf') . ' / ' . __('Hide', 'generateperf'),
                'view' => __('See', 'generateperf') . ' / ' . __('Unsee', 'generateperf'),
                'add' => __('Display', 'generateperf') . ' / ' . __('Cover up', 'generateperf'),
                'visualize' => __('Visualize', 'generateperf') . ' / ' . __('Occult', 'generateperf'),
                'deploy' => __('Deploy', 'generateperf') . ' / ' . __('Fold up', 'generateperf'),
                'reveal' => __('Reveal', 'generateperf') . ' / ' . __('Conceal', 'generateperf'),
            );
            echo '<select name="generate_child_toc_texts">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_toc_texts') ? ' selected="selected"' : '').'>'.$label.'</option>';
            }
            echo '</select>';
        },
        'generatepress_child_toc_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_similar', $array_field_args);
    add_settings_field(
        'generate_child_similar',
        'Articles (single)',
        function () {
            $locations = array(
                'inside_content' => __('Inside content', 'generateperf'), 
                'after_content' => __('After content', 'generateperf'), 
                'sidebar' => __('Inside sidebar', 'generateperf'),
            );
            $active_locations = json_decode(stripslashes(get_option('generate_child_similar')), true);
            foreach ($locations as $id => $location) {
                echo '<input type="checkbox" id="similar-'.$id.'" name="generate_child_similar['.$id.']" value="1"'.(is_array($active_locations) && array_key_exists($id, $active_locations) ? ' checked="checked"' : '').'> <label for="similar-'.$id.'">' . $location . '</label><br>';
            }
        },
        'generatepress_child_relative_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_similar_post_types', $array_field_args);
    add_settings_field(
        'generate_child_similar_post_types',
        'Autres post types',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_similar_post_types', 'post');
        },
        'generatepress_child_relative_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_related_texts_in_text', $text_field_args);
    add_settings_field(
        'generate_child_related_texts_in_text',
        'Libellé des inserts in-text',
        function () {
            $entries = array(
                'read' => __('To read', 'generateperf'),
                'view' => __('To see', 'generateperf'),
                'discover' => __('To discover', 'generateperf'),
                'explore' => __('To explore', 'generateperf'),
                'browse' => __('To browse', 'generateperf'),
                'trendy' => __('Trendy', 'generateperf'),
                'dynamic' => __('Dynamic (taxonomy)', 'generateperf'),
            );
            echo '<select name="generate_child_related_texts_in_text">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_related_texts_in_text') ? ' selected="selected"' : '').'>'.$label.'</option>';
            }
            echo '</select>';
        },
        'generatepress_child_relative_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_related_texts', $text_field_args);
    add_settings_field(
        'generate_child_related_texts',
        'Libellé des encarts',
        function () {
            $entries = array(
                'view' => __('See also', 'generateperf'),
                'other' => __('Other articles', 'generateperf'),
                'topics' => __('Related topics', 'generateperf'),
                'also' => __('Also published', 'generateperf'),
                'publications' => __('Other publications', 'generateperf'),
                'previous' => __('Previous publications', 'generateperf'),
                'like' => __('You will also like', 'generateperf'),
                'currently' => __('Currently', 'generateperf'),
            );
            echo '<select name="generate_child_related_texts">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_related_texts') ? ' selected="selected"' : '').'>'.$label.'</option>';
            }
            echo '</select>';
        },
        'generatepress_child_relative_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_contact_form_email', $text_field_args);
    add_settings_field(
        'generate_child_contact_form_email',
        'Destinataire du formulaire de contact',
        function () {
            echo '<input type="email" name="generate_child_contact_form_email" value="'.get_option('generate_child_contact_form_email', get_bloginfo('admin_email')).'">';
        },
        'generatepress_child_contact_section',
        'generatepress_child_settings',
    );


    register_setting('generatepress_child_settings', 'generate_child_contact_captcha_partner', $text_field_args);
    add_settings_field(
        'generate_child_contact_captcha_partner',
        __('Captcha provider', 'generateperf'),
        function () {
            $entries = array(
                '' => __('None', 'generateperf'),
                'recaptcha' => __('ReCaptcha (Google)', 'generateperf'),
                'hcaptcha' => __('Hcaptcha', 'generateperf'),
                'turnstile' => __('Turnstile (Cloudflare)', 'generateperf'),
            );
            echo '<select name="generate_child_contact_captcha_partner">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_contact_captcha_partner') ? ' selected="selected"' : '').'>'.$label.'</option>';
            }
            echo '</select>';
        },
        'generatepress_child_contact_section',
        'generatepress_child_settings',
    );

    
    register_setting('generatepress_child_settings', 'generate_child_contact_captcha_public', $text_field_args);
    add_settings_field(
        'generate_child_contact_captcha_public',
        __('Captcha Public key', 'generateperf'),
        function () {
            echo '<input type="text" name="generate_child_contact_captcha_public" value="'.get_option('generate_child_contact_captcha_public').'">';
        },
        'generatepress_child_contact_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_contact_captcha_private', $text_field_args);
    add_settings_field(
        'generate_child_contact_captcha_private',
        __('Captcha Secret/Private key', 'generateperf'),
        function () {
            echo '<input type="text" name="generate_child_contact_captcha_private" value="'.get_option('generate_child_contact_captcha_private').'">';
        },
        'generatepress_child_contact_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_activate_subtitles', $text_field_args);
    add_settings_field(
        'generate_child_activate_subtitles',
        __('Post subtitles', 'generateperf'),
        function () {
            $entries = array(
                '' => __('None', 'generateperf'),
                'excerpt' => __('Using excerpts (better for performance)', 'generateperf'),
                'acf' => __('Through ACF (legacy)', 'generateperf'),
            );
            echo '<select name="generate_child_activate_subtitles">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_activate_subtitles') ? ' selected="selected"' : '').'>'.$label.'</option>';
            }
            echo '</select>';
        },
        'generatepress_child_subtitles_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_acf_content_source', $array_field_args);
    add_settings_field(
        'generate_child_acf_content_source',
        'Gérer les sources des publications',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_acf_content_source');
            echo '<p class="description">Nécessite l’installation de l’extension gratuite Advanced Custom Fields. <a class="thickbox open-plugin-details-modal" href="'.get_admin_url(null, 'plugin-install.php?tab=plugin-information&plugin=advanced-custom-fields&TB_iframe=true&width=640&height=550').'" target="_blank" rel="noopener">'.__('More Details').'</a></p>';
        },
        'generatepress_child_sources_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_featured_image_caption', $array_field_args);
    add_settings_field(
        'generate_child_featured_image_caption',
        'Légende de l’image mise en avant',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_featured_image_caption');
        },
        'generatepress_child_sources_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_social_share_native', 'intval');
    add_settings_field(
        'generate_child_social_share_native',
        __('Enable native sharing', 'generateperf'),
        function () {
            echo '<input type="checkbox" name="generate_child_social_share_native" value="1"'.(get_option('generate_child_social_share_native') ? ' checked="checked"' : '').'>';
        },
        'generatepress_child_social_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_social_share_position', $array_field_args);
    add_settings_field(
        'generate_child_social_share_position',
        __('Position', 'generateperf'),
        function () {
            $locations = array(
                'before_content' => __('Before content', 'generateperf'), 
                'after_content' => __('After content', 'generateperf'), 
            );
            $active_locations = json_decode(stripslashes(get_option('generate_child_social_share_position')), true);
            foreach ($locations as $id => $location) {
                echo '<input type="checkbox" id="toc-position-'.$id.'" name="generate_child_social_share_position['.$id.']" value="1"'.(is_array($active_locations) && array_key_exists($id, $active_locations) ? ' checked="checked"' : '').'> <label for="toc-position-'.$id.'">' . $location . '</label><br>';
            }
        },
        'generatepress_child_social_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_social_share', $array_field_args);
    add_settings_field(
        'generate_child_social_share',
        'Boutons de partage social',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_social_share');
        },
        'generatepress_child_social_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_active_social_services', $array_field_args);
    add_settings_field(
        'generate_child_active_social_services',
        'Services de partage social',
        function () {
            $services = generatepress_child_social_share_services();
            $active_services = json_decode(stripslashes(get_option('generate_child_active_social_services')), true);
            foreach ($services as $id => $service) {
                echo '<input type="checkbox" id="social-'.$id.'" name="generate_child_active_social_services['.$id.']" value="1"'.(is_array($active_services) && array_key_exists($id, $active_services) ? ' checked="checked"' : '').'> <label for="social-'.$id.'">' . $service['label'] . '</label><br>';
            }
        },
        'generatepress_child_social_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_author_write_action_name', $text_field_args);
    add_settings_field(
        'generate_child_author_write_action_name',
        __('Displayed writing action', 'generateperf'),
        function () {
            $entries = array(
                'written' => __('Written by', 'generateperf'),
                'composed' => __('Composed by', 'generateperf'),
                'authored' => __('Authored by', 'generateperf'),
                'crafted' => __('Crafted by', 'generateperf'),
                'scribed' => __('Scribed by', 'generateperf'),
                'conceived' => __('Conceived by', 'generateperf'),
            );
            echo '<select name="generate_child_author_write_action_name">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_author_write_action_name') ? ' selected="selected"' : '').'>'.$label.' XX</option>';
            }
            echo '</select>';
        },
        'generatepress_child_authors_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_author_box', $array_field_args);
    add_settings_field(
        'generate_child_author_box',
        'Encart auteur',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_author_box');
        },
        'generatepress_child_authors_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_written_by_text', $text_field_args);
    add_settings_field(
        'generate_child_written_by_text',
        'Libellé de l’encart',
        function () {
            $entries = array(
                'about' => sprintf(__('About the author, %s', 'generateperf'), 'XX'),
                'information' => sprintf(__('Information on the author, %s', 'generateperf'), 'XX'),
                'written' => sprintf(__('This content was written by %s', 'generateperf'), 'XX'),
                'discover' => sprintf(__('Discover the author, %s', 'generateperf'), 'XX'),
                'whois' => sprintf(__('Who is the author, %s?', 'generateperf'), 'XX'),
                'learn' => sprintf(__('Learn about %s, the author', 'generateperf'), 'XX'),
            );
            echo '<select name="generate_child_written_by_text">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_written_by_text') ? ' selected="selected"' : '').'>'.$label.'</option>';
            }
            echo '</select>';
        },
        'generatepress_child_authors_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_author_link', 'intval');
    add_settings_field(
        'generate_child_author_link',
        'Lien vers la page auteur',
        function () {
            echo '<input type="checkbox" name="generate_child_author_link" value="1"'.(get_option('generate_child_author_link') ? ' checked="checked"' : '').'>';
            echo '<p class="description">Faut-il faire des liens vers les pages auteur depuis l’encart et le shortcode ?</p>';
        },
        'generatepress_child_authors_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_load_aos', 'intval');
    add_settings_field(
        'generate_child_load_aos',
        'Utiliser AOS',
        function () {
            echo '<input type="checkbox" name="generate_child_load_aos" value="1"'.(get_option('generate_child_load_aos') ? ' checked="checked"' : '').'>';
            echo '<p class="description">Active la librairie Animate On Scroll que l’on peut utiliser sur les blocs Gutenberg de GenerateBlocks. <a href="https://michalsnik.github.io/aos/" target="_blank" rel="noopener" style="text-decoration:none;"><span class="dashicons dashicons-editor-help"></span></a></p>';
        },
        'generatepress_child_3rdparties_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_load_rellax', 'intval');
    add_settings_field(
        'generate_child_load_rellax',
        'Utiliser Rellax',
        function () {
            echo '<input type="checkbox" name="generate_child_load_rellax" value="1"'.(get_option('generate_child_load_rellax') ? ' checked="checked"' : '').'>';
            echo '<p class="description">Active la librairie d’effets parallaxes Rellax que l’on peut utiliser sur les blocs Gutenberg de GenerateBlocks. <a href="https://dixonandmoe.com/rellax/" target="_blank" rel="noopener" style="text-decoration:none;"><span class="dashicons dashicons-editor-help"></span></a></p>';
        },
        'generatepress_child_3rdparties_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_exclude_from_search', $list_field_args);
    add_settings_field(
        'generate_child_exclude_from_search',
        'Slugs à exclure de la recherche',
        function () {
            $slugs_to_exclude = implode("\n", generatepress_child_search_excluded_terms_as_array());
            echo '<textarea name="generate_child_exclude_from_search" style="height:200px;">'.$slugs_to_exclude.'</textarea>';
            echo '<p class="description">GeneratePerf exclut la liste des pages ci-dessus. Modifiez les exclusions en ajoutant vos slugs d’url (un par ligne).</p>';
        },
        'generatepress_child_search_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_cse_search', $text_field_args);
    add_settings_field(
        'generate_child_cse_search',
        'Clé Google CSE',
        function () {
            echo '<input type="text" name="generate_child_cse_search" value="'.get_option('generate_child_cse_search').'" placeholder="CXXXXXXXXXXXXXX">';
            echo '<p class="description">Remplace le moteur de recherche public de WordPress par un moteur Google propre au site. <a href="https://cse.google.fr/cse/all" target="_blank" rel="noopener" style="text-decoration:none;"><span class="dashicons dashicons-editor-help"></span></a></p>';
        },
        'generatepress_child_search_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_native_cmp', 'intval');
    add_settings_field(
        'generate_child_native_cmp',
        'Activer la CMP native',
        function () {
            echo '<input type="checkbox" name="generate_child_native_cmp" value="1"'.(get_option('generate_child_native_cmp') ? ' checked="checked"' : '').'>';
        },
        'generatepress_child_consent_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_native_cmp_toggler', 'intval');
    add_settings_field(
        'generate_child_native_cmp_toggler',
        'Afficher un toggle fixe',
        function () {
            echo '<input type="checkbox" name="generate_child_native_cmp_toggler" value="1"'.(get_option('generate_child_native_cmp_toggler') ? ' checked="checked"' : '').'>';
            echo '<p class="description">Permet aux visiteurs de modifier leur choix initial en affichant un bouton "Cookies" discret.</p>';
        },
        'generatepress_child_consent_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_custom_header_code', $code_field_args);
    add_settings_field(
        'generate_child_custom_header_code',
        __('Meta tags, Resource hints &amp; CMP scripts', 'generateperf'),
        function () {
            echo '<textarea name="generate_child_custom_header_code" rows="5" style="width:100%">'.get_option('generate_child_custom_header_code').'</textarea>';
        },
        'generatepress_child_code_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_custom_footer_code', $code_field_args);
    add_settings_field(
        'generate_child_custom_footer_code',
        __('Analytics, Ads &amp; other JavaScript tags', 'generateperf'),
        function () {
            echo '<textarea name="generate_child_custom_footer_code" rows="15" style="width:100%">'.get_option('generate_child_custom_footer_code').'</textarea>';
        },
        'generatepress_child_code_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_inline_svg_logo', $text_field_args);
    add_settings_field(
        'generate_child_inline_svg_logo',
        'Chargement en inline du logo',
        function () {
            echo '<input type="checkbox" name="generate_child_inline_svg_logo" value="1"'.(get_option('generate_child_inline_svg_logo') ? ' checked="checked"' : '').'>';
            echo '<p class="description">Intègre le logo en SVG directement dans le code html (optimal si léger).</p>';
        },
        'generatepress_child_performance_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_preload_lcp', $array_field_args);
    add_settings_field(
        'generate_child_preload_lcp',
        'Pré-chargement des images mises en avant',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_preload_lcp');
            echo '<p class="description">Pré-charge l’image mise en avant en respectant le srcset responsive (optimal).</p>';
        },
        'generatepress_child_performance_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_fetchpriority_high_lcp', $array_field_args);
    add_settings_field(
        'generate_child_fetchpriority_high_lcp',
        'Priorité haute des images mises en avant',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_fetchpriority_high_lcp');
            echo '<p class="description">Ajoute un fetchpriority="high" sur les images mises en avant.</p>';
        },
        'generatepress_child_performance_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_no_lazy_thumbnail', $array_field_args);
    add_settings_field(
        'generate_child_no_lazy_thumbnail',
        'Suppression du lazy-loading des images mises en avant',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_no_lazy_thumbnail');
            echo '<p class="description">Désactive le lazy-loading sur l’image mise en avant.</p>';
        },
        'generatepress_child_performance_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_no_lazy_lcp', $array_field_args);
    add_settings_field(
        'generate_child_no_lazy_lcp',
        'Re-priorisation de la 1<sup>ère</sup> image',
        function () {
            echo generatepress_child_get_post_types_checkboxes('generate_child_no_lazy_lcp');
            echo '<p class="description">Désactive le lazy-loading et augmente la priorité de la première image des contenus.</p>';
        },
        'generatepress_child_performance_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_silo_menus', $text_field_args);
    add_settings_field(
        'generate_child_silo_menus',
        __('Automatic silos on Primary menu', 'generateperf'),
        function () {
            echo '<input type="checkbox" name="generate_child_silo_menus" value="1"'.(get_option('generate_child_silo_menus') ? ' checked="checked"' : '').'>';
        },
        'generatepress_child_seo_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_tags_and_cats_rss_links', $text_field_args);
    add_settings_field(
        'generate_child_tags_and_cats_rss_links',
        'Flux RSS des catégories et tags dans les articles',
        function () {
            echo '<input type="checkbox" name="generate_child_tags_and_cats_rss_links" value="1"'.(get_option('generate_child_tags_and_cats_rss_links') ? ' checked="checked"' : '').'>';
        },
        'generatepress_child_seo_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_cats_above_titles', $text_field_args);
    add_settings_field(
        'generate_child_cats_above_titles',
        'Afficher les catégories au-dessus des titres',
        function () {
            echo '<input type="checkbox" name="generate_child_cats_above_titles" value="1"'.(get_option('generate_child_cats_above_titles') ? ' checked="checked"' : '').'>';
        },
        'generatepress_child_seo_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_tags_above_titles', $text_field_args);
    add_settings_field(
        'generate_child_tags_above_titles',
        'Afficher les tags au-dessus des titres',
        function () {
            echo '<input type="checkbox" name="generate_child_tags_above_titles" value="1"'.(get_option('generate_child_tags_above_titles') ? ' checked="checked"' : '').'>';
        },
        'generatepress_child_seo_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_categories_children', $text_field_args);
    add_settings_field(
        'generate_child_categories_children',
        'Afficher les sous-catégories dans les archives',
        function () {
            echo '<input type="checkbox" name="generate_child_categories_children" value="1"'.(get_option('generate_child_categories_children') ? ' checked="checked"' : '').'>';
        },
        'generatepress_child_seo_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_categories_move_description', $text_field_args);
    add_settings_field(
        'generate_child_categories_move_description',
        'Déplacer les descriptions d’archives en bas',
        function () {
            echo '<input type="checkbox" name="generate_child_categories_move_description" value="1"'.(get_option('generate_child_categories_move_description') ? ' checked="checked"' : '').'>';
        },
        'generatepress_child_seo_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_tags_related_in_archives', $text_field_args);
    add_settings_field(
        'generate_child_tags_related_in_archives',
        'Afficher les tags relatifs dans les archives de tags',
        function () {
            echo '<input type="checkbox" name="generate_child_tags_related_in_archives" value="1"'.(get_option('generate_child_tags_related_in_archives') ? ' checked="checked"' : '').'>';
        },
        'generatepress_child_seo_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_news_sitemap', $text_field_args);
    add_settings_field(
        'generate_child_news_sitemap',
        __('Generate a News Sitemap', 'generateperf'),
        function () {
            echo '<input type="checkbox" name="generate_child_news_sitemap" value="1"'.(get_option('generate_child_news_sitemap') ? ' checked="checked"' : '').'>';
            echo '<p class="description">'.__('The news sitemap will be available at the url <code>/sitemap-news.xml</code>.', 'generateperf').'</p>';
        },
        'generatepress_child_news_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_optimal_post_slugs', $text_field_args);
    add_settings_field(
        'generate_child_optimal_post_slugs',
        'Permalien d’article optimal',
        function () {
            echo '<input type="datetime-local" name="generate_child_optimal_post_slugs" value="'.get_option('generate_child_optimal_post_slugs').'" >';
            echo '<p class="description">Modifie les permaliens des articles à partir d’une date donnée sans nécessiter de redirections 301</p>';
        },
        'generatepress_child_news_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_ggnews_url', $text_field_args);
    add_settings_field(
        'generate_child_ggnews_url',
        'URL Google News',
        function () {
            echo '<input type="url" name="generate_child_ggnews_url" value="'.get_option('generate_child_ggnews_url').'" size="70" pattern="https://.*" placeholder="https://news.google.com/publications/(.*)/?oc=3&hl=fr&gl=FR&ceid=FR:fr">';
            echo '<p class="description">Affiche un bouton de suivi Google News sous les articles.</p>';
        },
        'generatepress_child_news_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_display_lang_switcher', 'intval');
    add_settings_field(
        'generate_child_display_lang_switcher',
        'Afficher un sélecteur de langue dans les articles',
        function () {
            echo '<input type="checkbox" name="generate_child_display_lang_switcher" value="1"'.(get_option('generate_child_display_lang_switcher') ? ' checked="checked"' : '').'>';
        },
        'generatepress_child_i18n_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_style_border_radius', $text_field_args);
    add_settings_field(
        'generate_child_style_border_radius',
        'Border-radius global',
        function () {
            $entries = array(
              '0' => 'Aucun',
              '5px' => '5 pixels',
              '10px' => '10 pixels',
              '15px' => '15 pixels',
              '20px' => '20 pixels',
              '.5em' => '0,5 unité relative',
              '1em' => '1 unité relative',
              '1.5em' => '1,5 unité relative',
              '2em' => '2 unités relatives',
            );
            echo '<select name="generate_child_style_border_radius">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_style_border_radius') ? ' selected="selected"' : '').'>'.$label.'</option>';
            }
            echo '</select>';
        },
        'generatepress_child_style_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_style_articles_style', $text_field_args);
    add_settings_field(
        'generate_child_style_articles_style',
        'Style des listes d’articles',
        function () {
            $entries = array(
              'classic' => 'Classique (défaut)',
              'cards' => 'Cartes avec contour',
              'cover' => 'Cartes avec arrière-plan',
            );
            echo '<select name="generate_child_style_articles_style">';
            foreach ($entries as $value => $label) {
                echo '<option value="'.$value.'"'.($value == get_option('generate_child_style_articles_style') ? ' selected="selected"' : '').'>'.$label.'</option>';
            }
            echo '</select>';
        },
        'generatepress_child_style_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_sticky_sidebar', 'intval');
    add_settings_field(
        'generate_child_sticky_sidebar',
        'Sidebar sticky (desktop)',
        function () {
            echo '<input type="checkbox" name="generate_child_sticky_sidebar" value="1"'.(get_option('generate_child_sticky_sidebar') ? ' checked="checked"' : '').'>';
        },
        'generatepress_child_style_section',
        'generatepress_child_settings',
    );

    register_setting('generatepress_child_settings', 'generate_child_replace_main_css', 'intval');
    add_settings_field(
        'generate_child_replace_main_css',
        'Écrasement intégral du CSS',
        function () {
            echo '<input type="checkbox" name="generate_child_replace_main_css" value="1"'.(get_option('generate_child_replace_main_css') ? ' checked="checked"' : '').'>';
            echo '<p class="description">Faut-il écraser le CSS principal de GeneratePress ?</p>';
        },
        'generatepress_child_style_section',
        'generatepress_child_settings',
    );

}

//-----------------------------------------------------
// Nettoyage des notifications inutiles
//-----------------------------------------------------

add_action('admin_init', 'generatepress_child_remove_dashboard_meta');
function generatepress_child_remove_dashboard_meta()
{
    remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
    remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
    remove_meta_box('dashboard_primary', 'dashboard', 'side');
    remove_meta_box('dashboard_secondary', 'dashboard', 'normal');
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');
    remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
    remove_meta_box('wpseo-dashboard-overview', 'dashboard', 'normal');
    remove_meta_box('wpseo-wincher-dashboard-overview', 'dashboard', 'normal');
}

//-----------------------------------------------------
// Désactivation du panneau de bienvenue
//-----------------------------------------------------

remove_action('welcome_panel', 'wp_welcome_panel');

//-----------------------------------------------------
// Masquage de certains messages d'alerte indésirables
//-----------------------------------------------------

add_action('admin_enqueue_scripts', 'generatepress_child_admin_hide_notices');
add_action('login_enqueue_scripts', 'generatepress_child_admin_hide_notices');
function generatepress_child_admin_hide_notices()
{
    echo '<style>#robotsmessage.notice{display:none}</style>';
}

//-----------------------------------------------------
// Texte en footer dans l'admin
//-----------------------------------------------------

add_filter('admin_footer_text', 'generatepress_child_footer_admin');
function generatepress_child_footer_admin()
{
    echo '<em>Paramétrez votre thème GeneratePerf via son <a href="'.get_admin_url().'options-general.php?page=generateperf">tableau de bord dédié</a>.</em>';
}

//-----------------------------------------------------
// Test de l'activation d'une option en fonction du post type courant dans l'admin
//-----------------------------------------------------

function generate_child_option_active_on_current_admin_page($option) {
    if (!is_admin()) {
        return false;
    }
    $current_screen = get_current_screen();
    if ($current_screen->base !== 'edit') {
        return false;
    }
    $allowed_post_types = json_decode(stripslashes(get_option($option)), true);
    if (!is_array($allowed_post_types)) {
        return false;
    }
    return array_key_exists($current_screen->post_type, $allowed_post_types);
}

//-----------------------------------------------------
// Colonnes custom pour les articles
//-----------------------------------------------------

add_filter( 'manage_post_posts_columns', 'generatepress_child_filter_posts_columns' );
function generatepress_child_filter_posts_columns( $columns ) {
    $new_columns = array(
        'thumbnail' => __( 'Image' ),
    );
  $columns = array_slice($columns, 0, 1, true) + $new_columns + array_slice($columns, 1, count($columns)-1, true);

    if(generate_child_option_active_on_current_admin_page('generate_child_views')){
        $new_columns = array(
            'views' => __( 'Vues' ),
        );
        $columns = array_slice($columns, 0, 3, true) + $new_columns + array_slice($columns, 3, count($columns)-3, true);
    }
  return $columns;
}

//-----------------------------------------------------
// Contenu des colonnes custom pour les articles
//-----------------------------------------------------

add_action('manage_post_posts_custom_column', 'generatepress_child_reg_rows', 10, 2);
function generatepress_child_reg_rows($column, $post_id) {
    switch ($column) {
        case 'thumbnail':
            echo get_the_post_thumbnail( $post_id, array(80, 80) );
        break;
        case 'views':
            echo get_post_meta( $post_id, 'views', true );
        break;
        default:
        break;
    }
}

//-----------------------------------------------------
// Colonnes custom triables
//-----------------------------------------------------

add_filter( 'manage_edit-post_sortable_columns', 'generatepress_child_sortable_columns');
function generatepress_child_sortable_columns( $columns ) {
  $columns['views'] = 'views';
  return $columns;
}

//-----------------------------------------------------
// Fonctions pour le tri des colonnes custom
//-----------------------------------------------------

add_action( 'pre_get_posts', 'generatepress_child_admin_posts_orderby' );
function generatepress_child_admin_posts_orderby( $query ) {
  if( ! is_admin() || ! $query->is_main_query() ) {
    return;
  }

  if ( 'views' === $query->get( 'orderby') ) {
    $query->set( 'orderby', 'meta_value' );
    $query->set( 'meta_key', 'views' );
    $query->set( 'meta_type', 'numeric' );
  }
}

//-----------------------------------------------------
// Désactivation des commentaires dans l'admin
//-----------------------------------------------------

add_action('admin_init', 'generatepress_child_disable_comments_in_admin');
function generatepress_child_disable_comments_in_admin() {
    if(get_option('generate_child_disable_comments')){
        global $pagenow;
    
        if ($pagenow === 'edit-comments.php') {
            wp_safe_redirect(admin_url());
            exit;
        }
    
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
        foreach (get_post_types() as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }
}

//-----------------------------------------------------
// Désactivation des commentaires dans l'admin (menu)
//-----------------------------------------------------

add_action('admin_menu', 'generatepress_child_disable_comments_in_admin_menu');
function generatepress_child_disable_comments_in_admin_menu() {
    if(get_option('generate_child_disable_comments')){
        remove_menu_page('edit-comments.php');
    }
}

//-----------------------------------------------------
// Champ utilisateur téléphone : enregistrement
//-----------------------------------------------------

add_action( 'personal_options_update', 'generate_child_add_user_phone_save' );
add_action( 'edit_user_profile_update', 'generate_child_add_user_phone_save' );
function generate_child_add_user_phone_save( int $user_id ): bool {
	if ( !current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}
	return update_user_meta( $user_id, 'telephone', $_POST['telephone'] );
}

//-----------------------------------------------------
// Champ icônes de menu natif
//-----------------------------------------------------

add_action( 'wp_nav_menu_item_custom_fields', 'generate_child_add_menu_icon_field', 10, 2 );
function generate_child_add_menu_icon_field( $item_id, $item ) {
    echo '<p class="field-icon description description-wide">';
    echo '<label for="edit-menu-item-icon-' . esc_attr( $item_id ) . '">';
    echo esc_html_e( 'SVG icon', 'generateperf' ) . '<br>';
    echo '<textarea class="widefat code edit-menu-item-icon" name="menu-item-icon[' . esc_attr( $item_id ) . ']" id="edit-menu-item-icon-' . esc_attr( $item_id ) . '" rows="6">' . get_post_meta( $item_id, '_menu_item_icon', true ) . '</textarea>';
    echo '<p class="description">' . __( 'The icon will be displayed before labels in the menu', 'generateperf' ) . '</p>';
    echo '</label>';
    echo '</p>';
}

//-----------------------------------------------------
// Champ icônes de menu natif : enregistrement
//-----------------------------------------------------

add_action( 'wp_update_nav_menu_item', 'generate_child_save_menu_icon_content', 10, 3 );
function generate_child_save_menu_icon_content( $menu_id, $menu_item_db_id, $menu_item_args ) {
    if ( isset( $_POST['menu-item-icon'][ $menu_item_db_id ] ) ) {
        $icon_value = wp_kses_post( $_POST['menu-item-icon'][ $menu_item_db_id ] );
        update_post_meta( $menu_item_db_id, '_menu_item_icon', $icon_value );
    }
}

//-----------------------------------------------------
// Champ de sous-titre basé sur les excerpt
//-----------------------------------------------------

add_action('admin_menu', 'generateperf_display_remove_default_excerpt', 999);
function generateperf_display_remove_default_excerpt() {
    if( get_option('generate_child_activate_subtitles') === 'excerpt' ){
        remove_meta_box('postexcerpt', 'post', 'normal');
    }
}

//-----------------------------------------------------
// Champ de sous-titre basé sur les excerpt
//-----------------------------------------------------

add_action('edit_form_after_title', 'generateperf_display_excerpt_as_subtitle', 1);
function generateperf_display_excerpt_as_subtitle($post_id){
    if( get_option('generate_child_activate_subtitles') === 'excerpt' ){
        echo '<div style="margin-top:20px">';
        echo '<label for="excerpt" style="font-weight:500">' . __('Subtitle', 'generateperf') . '</label>';
        post_excerpt_meta_box($post_id);
        echo '</div>';
    }
}