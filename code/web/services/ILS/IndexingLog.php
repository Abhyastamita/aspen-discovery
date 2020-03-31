<?php

require_once ROOT_DIR . '/services/Admin/IndexingLog.php';
require_once ROOT_DIR . '/sys/ILS/IlsExtractLogEntry.php';

class ILS_IndexingLog extends Admin_IndexingLog
{
	function getIndexLogEntryObject(): BaseLogEntry
	{
		return new IlsExtractLogEntry();
	}

	function getTemplateName() : string
	{
		return 'ilsExtractLog.tpl';
	}

	function getTitle() : string
	{
		return 'ILS Export Log';
	}

	function getModule() : string{
		return 'ILS';
	}

	function applyMinProcessedFilter(DataObject $indexingObject, $minProcessed){
		if ($indexingObject instanceof IlsExtractLogEntry){
			$indexingObject->whereAdd('(numAdded + numDeleted + numUpdated) >= ' . $minProcessed);
		}
	}

}
