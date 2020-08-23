<?php

require_once ROOT_DIR . '/Action.php';

class Search_Home extends Action {

	function launch()
	{
		global $interface;
		global $library;
		global $locationSingleton;
		global $timer;

		// Include Search Engine Class
		require_once ROOT_DIR . '/sys/SolrConnector/GroupedWorksSolrConnector.php';
		$timer->logTime('Include search engine');

		$interface->assign('showBreadcrumbs', 0);

		// Load browse categories
		require_once ROOT_DIR . '/sys/Browse/BrowseCategory.php';

		// Get Location's Browse Categories if Location is set
		$activeLocation = $locationSingleton->getActiveLocation();
		/** @var BrowseCategory[] $browseCategories */
		if ($activeLocation != null){
			$browseCategories = $this->getBrowseCategories($activeLocation->getBrowseCategoryGroup()->getBrowseCategories());
		}else{
			$browseCategories = $this->getBrowseCategories($library->getBrowseCategoryGroup()->getBrowseCategories());
		}

		// Get All Browse Categories if Location & Library had none set
		if (empty($browseCategories)){
			$browseCategories = $this->getBrowseCategories();
		}

		$interface->assign('browseCategories', $browseCategories);

		//Set a Default Browse Mode
		if (count($browseCategories) > 0){
			require_once ROOT_DIR . '/services/Browse/AJAX.php';
			$browseAJAX = new Browse_AJAX();
			$browseAJAX->setBrowseMode(); // set default browse mode in the case that the user hasn't chosen one.
		}
		if (!$interface->get_template_vars('browseMode')) {
			$interface->assign('browseMode', 'covers'); // fail safe: if no browseMode is set at all, go with covers
		}

		$this->display('home.tpl', 'Catalog Home');
	}


	/**
	 * @param BrowseCategoryGroup|null $localBrowseCategories
	 * @return BrowseCategory[]
	 */
	public function getBrowseCategories($localBrowseCategories=null) {
		$user = UserAccount::getActiveUserObj();

		$browseCategories = array();
		$specifiedCategory = isset($_REQUEST['browseCategory']);
		$specifiedSubCategory = $specifiedCategory && isset($_REQUEST['subCategory']); // make a specified main browse category required
		if ($localBrowseCategories) {
			$first = key($localBrowseCategories); // get key of first category
			foreach ($localBrowseCategories as $index => $localBrowseCategory) {
				$browseCategory         = new BrowseCategory();
				$browseCategory->id = $localBrowseCategory->browseCategoryId;
				$browseCategory->find(true);

				// Only Show the Recommended for You browse category if the user is logged in and has rated titles
				if (($browseCategory->textId == 'system_recommended_for_you' && (!$user || !$user->hasRatings()))) {
					unset($localBrowseCategories[$index]);
					continue;
				}

				$browseCategories[] = clone($browseCategory);
				if (
					($specifiedCategory && $_REQUEST['browseCategory'] == $browseCategory->textId) // A category has been selected through URL parameter
					|| (!$specifiedCategory && $index == $first) // Or default to selecting the first browse category
				) {
					$this->assignBrowseCategoryInformation($browseCategory, $specifiedSubCategory);
				}
			}
		} else { // get All BrowseCategories
			$browseCategory = new BrowseCategory();
			$browseCategory->orderBy('numTitlesClickedOn');
			$browseCategory->limit(0, 20);
			$browseCategory->find();
			while($browseCategory->fetch()){
				//Do not use the browse category if it is a subcategory of any other category
				$subCategoryInfo = new SubBrowseCategories();
				$subCategoryInfo->subCategoryId = $browseCategory->id;
				$subCategoryInfo->find();
				if ($subCategoryInfo->getNumResults() > 0){
					continue;
				}

//				$browseCategory->getSubCategories(); // add subcategory information to the object
				$browseCategories[] = clone($browseCategory);
				if ($specifiedCategory && $_REQUEST['browseCategory'] == $browseCategory->textId) {
					$this->assignBrowseCategoryInformation($browseCategory, $specifiedSubCategory);
				}
			}
		}
		return $browseCategories;
	}

	/**
	 * @param BrowseCategory $browseCategory
	 * @param bool $specifiedSubCategory
	 */
	private function assignBrowseCategoryInformation(BrowseCategory $browseCategory, bool $specifiedSubCategory): void
	{
		global $interface;
		$selectedBrowseCategory = clone($browseCategory);
		$interface->assign('selectedBrowseCategory', $selectedBrowseCategory);
		if ($specifiedSubCategory) {
			$selectedBrowseCategory->getSubCategories();

			$validSubCategory = false;
			$subCategories = array();
			/** @noinspection PhpUndefinedFieldInspection */
			/** @var SubBrowseCategories $subCategory */
			foreach ($selectedBrowseCategory->subBrowseCategories as $subCategory) {
				// Get Needed Info about sub-category
				$temp = new BrowseCategory();
				$temp->get($subCategory->subCategoryId);
				if ($temp) {
					if ($temp->textId == $_REQUEST['subCategory']) $validSubCategory = true;
					$subCategories[] = array('label' => $temp->label, 'textId' => $temp->textId);
				}
			}
			if ($validSubCategory) {
				$interface->assign('subCategoryTextId', $_REQUEST['subCategory']);
				$interface->assign('subCategories', $subCategories);
			}
		}
	}

	function getBreadcrumbs()
	{
		return [];
	}
}