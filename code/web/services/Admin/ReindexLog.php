<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/Admin.php';
require_once ROOT_DIR . '/sys/Pager.php';

class ReindexLog extends Admin_Admin
{
	function launch()
	{
		global $interface;
		global $configArray;

		$logEntries = array();
		$logEntry = new ReindexLogEntry();
		if (!empty($_REQUEST['worksLimit']) && ctype_digit($_REQUEST['worksLimit'])) {
			// limits total count correctly
			$logEntry->whereAdd('numWorksProcessed >= '.$_REQUEST['worksLimit']);
		}
		$total = $logEntry->count();
		$logEntry = new ReindexLogEntry();
		$logEntry->orderBy('startTime DESC');
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$pageSize = isset($_REQUEST['pageSize']) ? $_REQUEST['pageSize'] : 30; // to adjust number of items listed on a page
		$interface->assign('recordsPerPage', $pageSize);
		$interface->assign('page', $page);
		if (!empty($_REQUEST['worksLimit']) && ctype_digit($_REQUEST['worksLimit'])) {
			$logEntry->whereAdd('numWorksProcessed > '.$_REQUEST['worksLimit']);
		}
		$logEntry->limit(($page - 1) * $pageSize, $pageSize);
		$logEntry->find();
		while ($logEntry->fetch()){
			$logEntries[] = clone($logEntry);
		}
		$interface->assign('logEntries', $logEntries);

		$options = array('totalItems' => $total,
		                 'fileName'   => '/Admin/ReindexLog?page=%d'. (empty($_REQUEST['worksLimit']) ? '' : '&worksLimit=' . $_REQUEST['worksLimit']). (empty($_REQUEST['pageSize']) ? '' : '&pageSize=' . $_REQUEST['pageSize']),
		                 'perPage'    => $pageSize,
		);
		$pager = new Pager($options);
		$interface->assign('pageLinks', $pager->getLinks());

		$this->display('reindexLog.tpl', 'Reindex Log');
	}

	function getAllowableRoles(){
		return array('opacAdmin', 'libraryAdmin', 'cataloging');
	}
}
