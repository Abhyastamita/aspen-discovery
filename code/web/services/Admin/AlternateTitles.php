<?php

require_once ROOT_DIR . '/sys/Grouping/GroupedWorkAlternateTitle.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
class Admin_AlternateTitles extends ObjectEditor
{
	function getObjectType() : string{
		return 'GroupedWorkAlternateTitle';
	}
	function getToolName() : string{
		return 'AlternateTitles';
	}
	function getPageTitle() : string{
		return 'Title / Author Authorities';
	}
	function getAllObjects($page, $recordsPerPage) : array{
		$object = new GroupedWorkAlternateTitle();
		$object->orderBy($this->getSort());
		$this->applyFilters($object);
		$object->limit(($page - 1) * $recordsPerPage, $recordsPerPage);
		$object->find();
		$objectList = array();
		while ($object->fetch()){
			$objectList[$object->id] = clone $object;
		}
		return $objectList;
	}
	function getDefaultSort() : string
	{
		return 'alternateTitle asc';
	}

	function getObjectStructure() : array {
		return GroupedWorkAlternateTitle::getObjectStructure();
	}
	function getPrimaryKeyColumn() : string{
		return 'id';
	}
	function getIdKeyColumn() : string{
		return 'id';
	}
	function getInstructions() : string{
//		global $interface;
//		return $interface->fetch('Admin/ungrouping_work_instructions.tpl');
		return '';
	}

	function getBreadcrumbs() : array
	{
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#cataloging', 'Catalog / Grouped Works');
		$breadcrumbs[] = new Breadcrumb('/Admin/AlternateTitles', 'Alternate Titles');
		return $breadcrumbs;
	}

	function getActiveAdminSection() : string
	{
		return 'cataloging';
	}

	function canView() : bool
	{
		return UserAccount::userHasPermission('Manually Group and Ungroup Works');
	}

	function canAddNew()
	{
		return false;
	}
}