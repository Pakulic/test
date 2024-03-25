<?php

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Initialisation du block Gutenberg FAQ
//-----------------------------------------------------

add_action('init', 'generatepress_child_register_faq_block', 5);
function generatepress_child_register_faq_block()
{
    if (function_exists('acf_add_local_field_group')) {
        wp_register_script('faq', get_stylesheet_directory_uri() . '/blocks/faq/faq.js', array(), gp_c_version);
        register_block_type(get_stylesheet_directory() . '/blocks/faq/block.json');
    }
}

//-----------------------------------------------------
// Création des champs du Custom block Gutenberg FAQ (nécessite ACF)
//-----------------------------------------------------

add_action('acf/init', 'generatepress_child_create_faq_block');
function generatepress_child_create_faq_block()
{
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group(array(
            'key' => 'group_632c031f6ed45',
            'title' => 'Block FAQ',
            'fields' => array(
                    array(
                    'key' => 'field_632c031fcd4b4',
                    'label' => 'Questions',
                    'name' => 'questions',
                    'type' => 'repeater',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'layout' => 'row',
                    'pagination' => 0,
                    'min' => 0,
                    'max' => 0,
                    'collapsed' => '',
                    'button_label' => 'Ajouter un élément',
                    'rows_per_page' => 20,
                    'sub_fields' => array(
                            array(
                            'key' => 'field_632c033ecd4b5',
                            'label' => 'Question',
                            'name' => 'question',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'maxlength' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'parent_repeater' => 'field_632c031fcd4b4',
                        ),
                            array(
                            'key' => 'field_632c0351cd4b6',
                            'label' => 'Réponse',
                            'name' => 'reponse',
                            'type' => 'wysiwyg',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'delay' => 0,
                            'tabs' => 'all',
                            'toolbar' => 'full',
                            'media_upload' => 1,
                            'parent_repeater' => 'field_632c031fcd4b4',
                        ),
                    ),
                ),
            ),
            'location' => array(
                    array(
                        array(
                        'param' => 'block',
                        'operator' => '==',
                        'value' => 'acf/faq',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
            'show_in_rest' => 0,
        ));
    }
}
