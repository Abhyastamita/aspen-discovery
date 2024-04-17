<?php

class AssabetEvent extends DataObject {
	public $__table = 'assabet_events';
	public $id;
	public $settingsId;
	public $externalId;
	public $title;
	public $rawChecksum;
	public $rawResponse;
	public $deleted;

	private $_rawDataDecoded = null;

	function getDecodedData() {
		if ($this->_rawDataDecoded == null) {
			$this->_rawDataDecoded = json_decode($this->rawResponse);
		}
		return $this->_rawDataDecoded;
	}
}