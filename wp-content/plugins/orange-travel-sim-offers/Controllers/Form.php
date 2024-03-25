<?php


namespace Controllers;

use Models\Form as ModelForm;

class Form
{

    public function plugin_sim_create_form_render($file, $params = [])
    {
        ob_start();
        $template = PLUGIN_DIR_PATH_WIDGET_SIM . 'templates/' . $file;
        extract($params);
        include($template);
        $pageContent = ob_get_clean();
        return $pageContent;
    }

    public function plugin_sim_create_form_offers($params = [] )
    {
        $modelForm = new ModelForm();
        $query = $modelForm->get_post_type($params['postType']);
        $params['query'] = $query;
        $pageContent = $this->plugin_sim_create_form_render($params['file'], $params);
        return $pageContent;
    }

    public function plugin_sim_countries_list()
    {
        $modelForm = new ModelForm();
        $query = $modelForm->get_post_type('esim-purchase-offer');
        $this->plugin_sim_instant_search($query);
    }
    public function plugin_sim_country_search()
    {

        $modelForm = new ModelForm();
        $query = $modelForm->plugin_sim_get_search_country('esim-purchase-offer');
        $this->plugin_sim_instant_search($query);
    }

    public function plugin_sim_instant_search($query)
    {
        // Vérifions si la requête retourne des résultats
        if ($query->have_posts()) {

            $results = array();
            $flagURL = " ";
            $translate = new Translate();
            $params = $translate->gp_child_translate_purchase_sim_form();

            if (get_field('flags_picture_url', 'option')) {
              $flagURL = get_field('flags_picture_url', 'option');
            }

            while ($query->have_posts()) {
                $query->the_post();
                $results[] = array(
                    'title'     => get_the_title(),
                    'link'     => get_field($params['link']), // Champ ACF
                    'flag' => get_field('flag_name_ACF'),
                    'flagURL' => $flagURL
                );

            }

            // Envoi du tableau $results vers la response
            wp_send_json_success($results, 200);
            wp_die();
        } else {
            // Envoi d'une erreur si aucun résultats
            wp_send_json_error('no_matches');
            wp_die();
        }

    }
}
