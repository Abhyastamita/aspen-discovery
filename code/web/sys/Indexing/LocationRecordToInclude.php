<?php

require_once ROOT_DIR . '/sys/Indexing/RecordToInclude.php';

class LocationRecordToInclude extends RecordToInclude {
	public $__table = 'location_records_to_include';    // table name
	protected $locationId;

	static function getObjectStructure($context = ''): array {
		$location = new Location();
		$location->orderBy('displayName');
		if (!UserAccount::userHasPermission('Administer All Locations')) {
			$homeLibrary = Library::getPatronHomeLibrary();
			$location->libraryId = $homeLibrary->libraryId;
		}
		$location->find();
		$locationList = [];
		while ($location->fetch()) {
			$locationList[$location->locationId] = $location->displayName;
		}

		$structure = parent::getObjectStructure($context);
		$structure['locationId'] = [
			'property' => 'locationId',
			'type' => 'enum',
			'values' => $locationList,
			'label' => 'Location',
			'description' => 'The id of a location',
		];

		return $structure;
	}
}