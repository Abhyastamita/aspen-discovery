<?php


class NewYorkTimesSetting extends DataObject
{
	public $__table = 'nyt_api_settings';    // table name
	public $id;
	public $booksApiKey;

	public static function getObjectStructure()
	{
		$structure = array(
			'id' => array('property' => 'id', 'type' => 'label', 'label' => 'Id', 'description' => 'The unique id'),
			'booksApiKey' => array('property' => 'booksApiKey', 'type' => 'storedPassword', 'label' => 'Books API Key', 'description' => 'The Key for the Books API', 'maxLength' => '32'),
		);
		return $structure;
	}
}