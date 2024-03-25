<?php

namespace Models;

use Wp_query;

class ImportData
{
    public function add_or_update_data($line, $postType)
    {
        if (isset($line['id'])):
            return $this->update_data($line);
        else :
            return $this->insert_data($line, $postType);
        endif;
    }

    protected function update_data($data)
    {
        $args = $this->prismamatch_update_args($data);
        $args['ID'] = intval($data['id']);
        unset($data['id']);
        $query = wp_update_post($args);
        return $query;
    }

    protected function insert_data($data, $postType)
    {
        $data['post_type'] = $postType;
        $args = $this->prismamatch_update_args($data);
        $query = wp_insert_post($args);
        return $query;
    }
    function prismamatch_update_args($data)
    {
        $args = [];
        $metaArgs = [];
        foreach ($data as $key => $value) :
            if (str_contains($key, 'purchase_esim_link')):
                $metaArgs[$key.'_'.$data['lang']] = $value;
            else :
                $args[$key] = $value;
            endif;
        endforeach;
        $args['meta_input'] = $metaArgs;
        return $args;
    }


}