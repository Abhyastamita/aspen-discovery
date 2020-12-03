<?php
require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/Admin.php';

class Translation_ImportTranslations extends Admin_Admin
{
	function launch(){
		global $interface;
		if (isset($_REQUEST['submit'])){
			$overrideExistingTranslations = isset($_REQUEST['overwriteExisting']);

			$languagesToImport = [];
			$validLanguage = new Language();
			$validLanguage->orderBy("weight");
			$validLanguage->find();
			$codeToLanguageId = [];
			while ($validLanguage->fetch()){
				if (isset($_REQUEST['import_' . $validLanguage->code])){
					$languagesToImport[$validLanguage->code] = $validLanguage->code;
				}
				$codeToLanguageId[$validLanguage->code] = $validLanguage->id;
			}

			if (empty($languagesToImport)){
				$interface->assign('error', 'Please select at least one language to import');
			}else {
				//Import the translations and redirect back to the main translations page
				if (isset ($_FILES['importFile'])) {
					if (isset($_FILES['importFile']["error"]) && $_FILES['importFile']["error"] != 0) {
						switch ($_FILES['importFile']["error"]) {
							case UPLOAD_ERR_INI_SIZE:
								$message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
								break;
							case UPLOAD_ERR_FORM_SIZE:
								$message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
								break;
							case UPLOAD_ERR_PARTIAL:
								$message = "The uploaded file was only partially uploaded";
								break;
							case UPLOAD_ERR_NO_FILE:
								$message = "No file was uploaded";
								break;
							case UPLOAD_ERR_NO_TMP_DIR:
								$message = "Missing a temporary folder";
								break;
							case UPLOAD_ERR_CANT_WRITE:
								$message = "Failed to write file to disk";
								break;
							case UPLOAD_ERR_EXTENSION:
								$message = "File upload stopped by extension";
								break;

							default:
								$message = "Unknown upload error";
								break;
						}
						$interface->assign('error', $message);
					} else {
						$fileToLoad = $_FILES['importFile']['tmp_name'];
						$fHnd = fopen($fileToLoad, 'r');
						$headerRow = fgetcsv($fHnd);
						//Map columns form export to what we want to import
						for ($i = 1; $i < count($headerRow); $i++){
							foreach ($languagesToImport as $code => $index){
								if ($code == $headerRow[$i]){
									$languagesToImport[$code] = $i;
								}
							}
						}
						global $memCache;
						while ($curRow = fgetcsv($fHnd)) {
							$term = $curRow[0];
							$translationTerm = new TranslationTerm();
							$translationTerm->term = $term;
							if (!$translationTerm->find(true)){
								$translationTerm->insert();
							}
							foreach ($languagesToImport as $code => $columnIndex){
								$newValue = $curRow[$columnIndex];
								if (!empty($newValue)){
									$translation = new Translation();
									$translation->languageId = $codeToLanguageId[$code];
									$translation->termId = $translationTerm->id;
									if ($translation->find(true)){
										if (!$translation->translated || $overrideExistingTranslations){
											$translation->translation = $newValue;
											$translation->translated = true;
											$translation->update();
										}
									}else{
										$translation->translation = $newValue;
										$translation->translated = true;
										$translation->insert();
									}
									$memCache->delete('translation_' . $codeToLanguageId[$code] . '_0_' . $term);
									$memCache->delete('translation_' . $codeToLanguageId[$code] . '_1_' . $term);
								}
							}
						}
						fclose($fHnd);
						header('Location: /Translation/Translations');
						die();
					}
				} else {
					$interface->assign('error', translate('Please select a file to import'));
				}
			}

		}
		$this->display('importTranslationsForm.tpl', 'Import Translations');
	}

	function getBreadcrumbs()
	{
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#translations', 'Languages and Translations');
		$breadcrumbs[] = new Breadcrumb('/Translation/Translations', 'Translations');
		$breadcrumbs[] = new Breadcrumb('', 'Import Translations');
		return $breadcrumbs;
	}

	function getActiveAdminSection()
	{
		return 'translations';
	}

	function canView()
	{
		return UserAccount::userHasPermission('Translate Aspen');
	}
}