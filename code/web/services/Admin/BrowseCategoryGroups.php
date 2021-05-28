<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/Browse/BrowseCategory.php';

class Admin_BrowseCategoryGroups extends ObjectEditor
{

	function getObjectType() : string{
		return 'BrowseCategoryGroup';
	}
	function getToolName() : string{
		return 'BrowseCategoryGroups';
	}
	function getPageTitle() : string{
		return 'Browse Category Groups';
	}
	function canDelete(){
		return UserAccount::userHasPermission('Administer All Browse Categories');
	}
	function getAllObjects($page, $recordsPerPage) : array{
		$object = new BrowseCategoryGroup();
		$object->orderBy($this->getSort());
		$this->applyFilters($object);
		$object->limit(($page - 1) * $recordsPerPage, $recordsPerPage);
		if (!UserAccount::userHasPermission('Administer All Browse Categories')){
			$library = Library::getPatronHomeLibrary(UserAccount::getActiveUserObj());
			$object->id = $library->browseCategoryGroupId;
		}
		$object->find();
		$list = array();
		while ($object->fetch()){
			$list[$object->id] = clone $object;
		}
		return $list;
	}
	function getDefaultSort() : string
	{
		return 'name asc';
	}

	function getObjectStructure() : array{
		return BrowseCategoryGroup::getObjectStructure();
	}
	function getPrimaryKeyColumn() : string{
		return 'id';
	}
	function getIdKeyColumn() : string{
		return 'id';
	}

	function getInstructions() : string{
		return '';
	}

	function getBreadcrumbs() : array
	{
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#local_enrichment', 'Local Enrichment');
		$breadcrumbs[] = new Breadcrumb('/Admin/BrowseCategoryGroups', 'Browse Category Groups');
		return $breadcrumbs;
	}

	function getActiveAdminSection() : string
	{
		return 'local_enrichment';
	}

	function canView() : bool
	{
		return UserAccount::userHasPermission(['Administer All Browse Categories', 'Administer Library Browse Categories']);
	}

	protected function getDefaultRecordsPerPage()
	{
		return 100;
	}

	protected function showQuickFilterOnPropertiesList(){
		return true;
	}
}