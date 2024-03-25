<h1>Import CSV</h1>
<p>Headers: ID;post_title;post_name;post_parent;post_type;purchase_esim_link;post_status;lang;translation_id</p>
<p>Example: 584;Danemark;danemark;436;esim-purchase-offer;/fr/acheter-une-sim/offres/danemark;publish;fr;3</p>
<p>lang (string) => examples :'es', 'es' or 'fr' </p>
<p>translation_id (INT) => unique id for post translations ('es', 'es' and 'fr') </p>
<p><strong>WARNING : possible errors when importing data with emphasis</strong></p>
<form enctype="multipart/form-data" method="POST" action="admin.php?page=import-csv%2Ftraitement.php">
<label for="importFilesInput"></label>
<input id="importFilesInput" type="file"  name="file_import" accept="text/csv"/>
<button id="importFormBTn" type="submit">ajouter le fichier csv</button>
</form>

