<?php

require_once ROOT_DIR . '/RecordDrivers/AssabetEventRecordDriver.php';

class Assabet_Event extends Action {

	private $recordDriver;

	function launch() {
		global $interface;
		$id = urldecode($_REQUEST['id']);

		$this->recordDriver = new AssabetEventRecordDriver($id);
		if (!$this->recordDriver->isValid()) {
			global $interface;
			$interface->assign('module', 'Error');
			$interface->assign('action', 'Handle404');
			require_once ROOT_DIR . "/services/Error/Handle404.php";
			$actionClass = new Error_Handle404();
			$actionClass->launch();
			die();
		}
		$interface->assign('recordDriver', $this->recordDriver);

		require_once ROOT_DIR . '/sys/Events/AssabetSetting.php';
		$eventSettings = new AssabetSetting;
		$eventSettings->id = $this->recordDriver->getSource();
		if ($eventSettings->find(true)){
			$interface->assign('eventsInLists', $eventSettings->eventsInLists);
		}
		$interface->assign('isStaff', UserAccount::isStaff());

		// Display Page
		$this->display('event.tpl', $this->recordDriver->getTitle(), null, false);
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		if (!empty($this->lastSearch)) {
			$breadcrumbs[] = new Breadcrumb($this->lastSearch, 'Event Search Results');
		}
		$breadcrumbs[] = new Breadcrumb('', $this->recordDriver->getTitle());
		return $breadcrumbs;
	}
}