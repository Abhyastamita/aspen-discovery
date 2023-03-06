<?php


class GroupedWorkDisplayInfo extends DataObject {
	public $__table = 'grouped_work_display_info';
	public $id;
	public $permanent_id;
	public $title;
	public $author;
	public $seriesName;
	public $seriesDisplayOrder;
	public $addedBy;
	public $dateAdded;

	public function insert($context = '') {
		if (empty($this->seriesDisplayOrder)) {
			$this->seriesDisplayOrder = 0;
		}
		return parent::insert();
	}

	public function update($context = '') {
		if (empty($this->seriesDisplayOrder)) {
			$this->seriesDisplayOrder = 0;
		}
		return parent::update();
	}
}