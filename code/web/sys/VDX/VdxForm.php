<?php

class VdxForm extends DataObject
{
	public $__table = 'vdx_form';
	public $id;
	public $name;
	public $introText;
	//We always show title
	public $showAuthor;
	public $showPublisher;
	public $showIsbn;
	public $showAcceptFee;
	public $showMaximumFee;
	public $feeInformationText;
	public $showCatalogKey;
	//We always show Note
	//We always show Pickup Library

	protected $_locations;

	public static function getObjectStructure(): array
	{
		$locationList = Location::getLocationList(!UserAccount::userHasPermission('Administer ILL Hold Groups'));

		return [
			'id' => ['property' => 'id', 'type' => 'label', 'label' => 'Id', 'description' => 'The unique id'],
			'name' => ['property' => 'name', 'type' => 'text', 'label' => 'Name', 'description' => 'The Name of the Hold Group', 'maxLength' => 50],
			'introText' => ['property' => 'introText', 'type' => 'textarea', 'label' => 'Intro Text', 'description' => 'Introductory Text to be displayed at the top of the form', 'maxLength' => 50],
			'showAuthor' => array('property' => 'showAuthor', 'type' => 'checkbox', 'label' => 'Show Author?', 'description' => 'Whether or not the user should be prompted to enter the author name'),
			'showPublisher' => array('property' => 'showPublisher', 'type' => 'checkbox', 'label' => 'Show Publisher?', 'description' => 'Whether or not the user should be prompted to enter the publisher name'),
			'showIsbn' => array('property' => 'showIsbn', 'type' => 'checkbox', 'label' => 'Show ISBN?', 'description' => 'Whether or not the user should be prompted to enter the ISBN'),
			'showAcceptFee' => array('property' => 'showAcceptFee', 'type' => 'checkbox', 'label' => 'Show Accept Fee?', 'description' => 'Whether or not the user should be prompted to accept the fee (if any)'),
			'showMaximumFee' => array('property' => 'showMaximumFee', 'type' => 'checkbox', 'label' => 'Show Maximum Fee?', 'description' => 'Whether or not the user should be prompted for the maximum fee they will pay'),
			'feeInformationText' => ['property' => 'feeInformationText', 'type' => 'textarea', 'label' => 'Fee Information Text', 'description' => 'Text to be displayed to give additional information about the fees charged.', 'maxLength' => 50],
			'showCatalogKey' => array('property' => 'showCatalogKey', 'type' => 'checkbox', 'label' => 'Show Catalog Key?', 'description' => 'Whether or not the user should be prompted for the catalog key'),

			'locations' => array(
				'property' => 'locations',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => 'Locations',
				'description' => 'Define locations that make up this hold group',
				'values' => $locationList,
				'hideInLists' => false
			),
		];
	}

	/**
	 * @return string[]
	 */
	public function getUniquenessFields(): array
	{
		return ['name'];
	}

	/**
	 * Override the update functionality to save related objects
	 *
	 * @see DB/DB_DataObject::update()
	 */
	public function update()
	{
		$ret = parent::update();
		if ($ret !== FALSE) {
			$this->saveLocations();
		}
		return $ret;
	}

	public function insert()
	{
		$ret = parent::insert();
		if ($ret !== FALSE) {
			$this->saveLocations();
		}
		return $ret;
	}

	public function delete($useWhere = false)
	{
		$ret = parent::delete($useWhere);
		if ($ret && !empty($this->id)) {
			$location = new Location();
			$location->vdxFormId = $this->id;
			$location->find();
			while ($location->fetch()){
				$location->vdxFormId = -1;
				$location->update();
			}
		}
		return $ret;
	}

	public function __get($name)
	{
		if ($name == "locations") {
			return $this->getLocations();
		} else {
			return $this->_data[$name];
		}
	}

	public function saveLocations(){
		if (isset ($this->_locations) && is_array($this->_locations)){
			$locationList = Location::getLocationList(!UserAccount::userHasPermission('Administer All VDX Forms'));
			foreach ($locationList as $locationId => $displayName) {
				$location = new Location();
				$location->locationId = $locationId;
				$location->find(true);
				if (in_array($locationId, $this->_locations)){
					if ($location->vdxFormId != $this->id){
						$location->vdxFormId = $this->id;
						$location->update();
					}
				}else{
					if ($location->vdxFormId == $this->id){
						$location->vdxFormId = -1;
						$location->update();
					}
				}
			}
			unset($this->_locations);
		}
		return $this->_locations;
	}

	public function __set($name, $value)
	{
		if ($name == "locations") {
			$this->_locations = $value;
		} else {
			$this->_data[$name] = $value;
		}
	}

	public function okToExport(array $selectedFilters): bool
	{
		return parent::okToExport($selectedFilters);
	}

	public function getFormFields(?MarcRecordDriver $marcRecordDriver, ?String $volumeInfo) : array
	{
		$fields = [];
		if ($this->introText){
			$fields['introText'] =array('property' => 'introText', 'type' => 'label', 'label' => $this->introText, 'description' => '');
		}
		require_once ROOT_DIR . '/sys/Utils/StringUtils.php';
		$fields['title'] =array('property' => 'title', 'type' => 'text', 'label' => 'Title', 'description' => 'The title of the title to be request', 'maxLength' => 255, 'required' => true, 'default' => ($marcRecordDriver != null ? StringUtils::removeTrailingPunctuation($marcRecordDriver->getTitle()) : ''));
		$fields['author'] =array('property' => 'author', 'type' => ($this->showAuthor ? 'text' : 'hidden'), 'label' => 'Author', 'description' => 'The author of the title to request', 'maxLength' => 255, 'required' => false, 'default' => ($marcRecordDriver != null ? $marcRecordDriver->getAuthor() : ''));
		$publisher = '';
		if ($marcRecordDriver != null){
			$publishers = $marcRecordDriver->getPublishers();
			if (count($publishers) > 0){
				$publisher = reset($publishers);
			}
		}
		$fields['publisher'] =array('property' => 'publisher', 'type' => ($this->showPublisher ? 'text' : 'hidden'), 'label' => 'Publisher', 'description' => 'The publisher of the title to request', 'maxLength' => 255, 'required' => false, 'default' => $publisher);
		$fields['isbn'] =array('property' => 'isbn', 'type' => ($this->showIsbn ? 'text' : 'hidden'), 'label' => 'ISBN', 'description' => 'The ISBN of the title to request', 'maxLength' => 20, 'required' => false, 'default' => ($marcRecordDriver != null ? $marcRecordDriver->getCleanISBN() : ''));
		if ($this->showAcceptFee){
			$fields['feeInformationText'] =array('property' => 'feeInformationText', 'type' => 'label', 'label' => $this->feeInformationText, 'description' => '');
			if ($this->showMaximumFee){
				$fields['maximumFeeAmount'] =array('property' => 'maximumFeeAmount', 'type' => 'currency', 'label' => 'Maximum Fee ', 'description' => 'The maximum fee you are willing to pay to have this title transferred to the library.', 'default'=> 5, 'displayFormat'=>'%0.2f');
				$fields['acceptFee'] =array('property' => 'acceptFee', 'type' => 'checkbox', 'label' => 'I will pay any fees associated with this request up to the maximum amount defined above', 'description' => '');
			}
		}
		$user = UserAccount::getLoggedInUser();
		$locations = $user->getValidPickupBranches($user->getCatalogDriver()->accountProfile->recordSource);
		$pickupLocations = [];
		foreach ($locations as $key => $location){
			if ($location instanceof Location){
				$pickupLocations[$location->code] = $location->displayName;
			}else{
				if ($key == '0default'){
					$pickupLocations[-1] = $location;
				}
			}
		}
		$fields['pickupLocation'] =array('property' => 'pickupLocation', 'type' => 'enum', 'values' => $pickupLocations, 'label' => 'Pickup Location', 'description' => 'Where you would like to pickup the title', 'required' => true, 'default' => $user->getHomeLocationCode());
		$fields['note'] =array('property' => 'note', 'type' => 'textarea', 'label' => 'Note', 'description' => 'Any additional information you want us to have about this request', 'required' => false, 'default' => ($volumeInfo == null) ? '' : $volumeInfo);
		$fields['catalogKey'] = array('property' => 'catalogKey', 'type' => (($this->showCatalogKey && $marcRecordDriver != null) ? 'text' : 'hidden'), 'label' => 'Record Number', 'description' => 'The record number to be requested', 'maxLength' => 20, 'required' => false, 'default' => ($marcRecordDriver != null ? $marcRecordDriver->getId() : ''));
		return $fields;
	}

	private function getLocations()
	{
		if (!isset($this->_locations) && $this->id){
			$this->_locations = [];
			$obj = new Location();
			$obj->vdxFormId = $this->id;
			$obj->find();
			while($obj->fetch()){
				$this->_locations[$obj->locationId] = $obj->locationId;
			}
		}
		return $this->_locations;
	}
}