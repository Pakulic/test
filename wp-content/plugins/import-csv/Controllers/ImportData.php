<?php

namespace Controllers;

require_once PLUGIN_URL_IMPORT_CSV . 'Models/ImportData.php';

use Models\ImportData as ModelImportData;

class ImportData
{
    public function gp_child_import_data($postType, $filesTmpName)
    {

        $importData = new ModelImportData();
        $file = fopen($filesTmpName, 'r');
        $headers = fgetcsv($file, 0, ';');
        $translations = [];
        header('Content-Type: text/html; charset=utf-8');
        while ($line = fgetcsv($file, 0, ';')) {
            $line = array_combine($headers, $line);
            $result = $importData->add_or_update_data($line, $postType);
            $translations[$line['translation_id']][$line['lang']] = $result;
            pll_set_post_language($result, $line['lang']);
        }
        var_dump($translations);

        foreach ($translations as $translation) {
            pll_save_post_translations($translation);
        }
    }
}
