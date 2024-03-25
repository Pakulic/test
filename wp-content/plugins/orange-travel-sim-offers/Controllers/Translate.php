<?php

namespace Controllers;

class Translate
{

    // ADD TRANSLATE FOR WIDGET FORM Sim purchase offers 
    function gp_child_get_translate($params)
    {

        $locale = get_locale();
        if(in_array( $locale, array_keys($params ))){
            return $params[$locale];
        }else{
            return $params['en_GB'];
        }
    }
    function gp_child_translate_purchase_sim_form()
    {
        $params = array(
            'en_GB' => array('title' => "title_sim_purchase_en", 'buttonName' => "button_sim_purchase_en", 'placeholder' => "placeholder_sim_purchase_en", 'link' =>'purchase_esim_link_en'),
            'fr_FR' => array('title' => "title_sim_purchase_fr", 'buttonName' => "button_sim_purchase_fr", 'placeholder' => "placeholder_sim_purchase_fr", 'link' =>'purchase_esim_link_fr'),
            'es_ES' => array('title' => "title_sim_purchase_es", 'buttonName' => "button_sim_purchase_es", 'placeholder' => "placeholder_sim_purchase_es", 'link' =>'purchase_esim_link_es')
        );
        return $this->gp_child_get_translate($params);
    }

    function gp_child_translate_recharge_sim_form()
    {
        $params = array(
            'en_GB' => array('title' => "title_sim_recharge_en", 'buttonName' => "button_sim_recharge_en", 'placeholder' => "placeholder_sim_recharge_en", 'link' =>'purchase_esim_link_en'),
            'fr_FR' => array('title' => "title_sim_recharge_fr", 'buttonName' => "button_sim_recharge_fr", 'placeholder' => "placeholder_sim_recharge_fr", 'link' =>'purchase_esim_link_fr'),
            'es_ES' => array('title' => "title_sim_recharge_es", 'buttonName' => "button_sim_recharge_es", 'placeholder' => "placeholder_sim_recharge_es", 'link' =>'purchase_esim_link_es')
        );
        return $this->gp_child_get_translate($params);
    }

    // ADD TRANSLATE FOR CUSTOM POST TYPE Sim purchase offers 
    function gp_child_get_labels_for_sim_purchase_post_type()
    {
        $params = array(
            'en_GB' => array( 'name' => 'sim_purchase_post_type_en'),
            'fr_FR' => array('name' => "sim_purchase_post_type_fr"),
            'es_ES' => array('name' => 'sim_purchase_post_type_es')
        );
        return $this->gp_child_get_translate($params);
    }

        // ADD TRANSLATE FOR CUSTOM POST TYPE Sim purchase offers 
        function gp_child_get_labels_for_sim_recharge_post_type()
        {
            $params = array(
                'en_GB' => array( 'name' => 'sim_recharge_post_type_en'),
                'fr_FR' => array('name' => "sim_recharge_post_type_fr"),
                'es_ES' => array('name' => 'sim_recharge_post_type_es')
            );
            return $this->gp_child_get_translate($params);
        }
    
}
