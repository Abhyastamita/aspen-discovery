<?php

require_once ROOT_DIR . '/sys/DB/DataObject.php';

class Session extends DataObject {
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'session';                        // table name
	protected $id;
	protected $session_id;
	protected $data;
	protected $last_used;
	protected $created;
	protected $remember_me;

	function getNumericColumnNames(): array {
		return [
			'remember_me',
			'last_used',
		];
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE

	function update($context = '') {
		if ($this->data == null) {
			$this->data = '';
		}
		$ret = parent::update();
		global $interface;
		if (isset($interface)) {
			$interface->assign('session', $this->session_id . ', remember me ' . $this->remember_me);
		}
		return $ret;
	}

	function insert($context = '') {
		if ($this->data == null) {
			$this->data = '';
		}
		return parent::insert();
	}
}
