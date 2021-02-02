<?php

require_once ROOT_DIR . '/sys/BaseLogEntry.php';
class HooplaExportLogEntry extends BaseLogEntry
{
	public $__table = 'hoopla_export_log';   // table name
	public $id;
	public $lastUpdate;
	public $notes;
	public $numProducts;
	public $numErrors;
	public $numAdded;
	public $numDeleted;
	public $numUpdated;
	public $numSkipped;

}
