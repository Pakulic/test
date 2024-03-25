<?php
require_once plugin_dir_path(__FILE__). 'Controllers/ImportData.php';

use Controllers\ImportData;


if (isset($_FILES))  :
        $importCsv = new ImportData();
        $importCsv->gp_child_import_data('esim-purchase-offer', $_FILES['file_import']['tmp_name']);
    else :
/* else :
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit(); */
endif;