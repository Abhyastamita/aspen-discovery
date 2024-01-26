<?php

require_once ROOT_DIR . '/sys/DB/DataObject.php';

class DonationFormFields extends DataObject {
	public $__table = 'donations_form_fields';
	public $id;
	public $textId;
	public $category;
	public $label;
	public $type;
	public $note;
	public $required;
	public $donationSettingId;

	static $fieldTypeOptions = [
		'text' => 'Text',
		'textbox' => 'Textarea',
		'checkbox' => 'Checkbox (Yes/No)',
	];

	static function getObjectStructure($context = ''): array {
		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'textId' => [
				'property' => 'textId',
				'type' => 'text',
				'label' => 'Text Id',
				'description' => 'The unique text id',
				'required' => true,
			],
			'category' => [
				'property' => 'category',
				'type' => 'text',
				'label' => 'Form Category',
				'description' => 'The name of the section this field will belong in.',
				'required' => true,
			],
			'label' => [
				'property' => 'label',
				'type' => 'text',
				'label' => 'Field Label',
				'description' => 'Label for this field that will be displayed to users.',
				'required' => true,
			],
			'type' => [
				'property' => 'type',
				'type' => 'enum',
				'label' => 'Field Type',
				'description' => 'Type of data this field will be',
				'values' => self::$fieldTypeOptions,
				'default' => 'text',
				'required' => true,
			],
			'note' => [
				'property' => 'note',
				'type' => 'text',
				'label' => 'Field Note',
				'description' => 'Note for this field that will be displayed to users.',
			],
			'required' => [
				'property' => 'required',
				'type' => 'checkbox',
				'label' => 'Required',
				'description' => 'Whether or not the field is required.',
			],
		];
		return $structure;
	}


	static function getDefaults($donationSettingId) {
		$defaultFieldsToDisplay = [];

		// Donation Information
		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Choose an amount to donate';
		$defaultField->label = 'Donation Amount';
		$defaultField->textId = 'valueList';
		$defaultField->type = 'select';
		$defaultField->required = 0;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Choose an amount to donate';
		$defaultField->label = 'What would you like your donation to support?';
		$defaultField->textId = 'earmarkList';
		$defaultField->type = 'select';
		$defaultField->required = 0;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Choose an amount to donate';
		$defaultField->label = 'If your donation is for a specific branch, please select the branch';
		$defaultField->textId = 'locationList';
		$defaultField->type = 'select';
		$defaultField->required = 0;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Choose an amount to donate';
		$defaultField->label = 'Dedicate my donation in honor or in memory of someone';
		$defaultField->textId = 'shouldBeDedicated';
		$defaultField->type = 'checkbox';
		$defaultField->required = 1;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Honoree information';
		$defaultField->label = 'Choose an amount to donate';
		$defaultField->textId = 'dedicationType';
		$defaultField->type = 'radio';
		$defaultField->required = 0;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Honoree information';
		$defaultField->label = 'Honoree\'s First Name';
		$defaultField->textId = 'honoreeFirstName';
		$defaultField->type = 'text';
		$defaultField->required = 1;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Honoree information';
		$defaultField->label = 'Honoree\'s Last Name';
		$defaultField->textId = 'honoreeLastName';
		$defaultField->type = 'text';
		$defaultField->required = 0;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Honoree information';
		$defaultField->label = 'Notify someone about this in memory or in honor of gift';
		$defaultField->textId = 'shouldBeNotified';
		$defaultField->type = 'checkbox';
		$defaultField->required = 0;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Notification party information';
		$defaultField->label = 'Notification Party First Name';
		$defaultField->textId = 'notificationFirstName';
		$defaultField->type = 'text';
		$defaultField->required = 0;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Notification party information';
		$defaultField->label = 'Notification Party Last Name';
		$defaultField->textId = 'notificationLastName';
		$defaultField->type = 'text';
		$defaultField->required = 0;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Notification party information';
		$defaultField->label = 'Address';
		$defaultField->textId = 'notificationAddress';
		$defaultField->type = 'text';
		$defaultField->required = 0;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Notification party information';
		$defaultField->label = 'City';
		$defaultField->textId = 'notificationCity';
		$defaultField->type = 'text';
		$defaultField->required = 0;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Notification party information';
		$defaultField->label = 'State';
		$defaultField->textId = 'notificationState';
		$defaultField->type = 'text';
		$defaultField->required = 0;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Notification party information';
		$defaultField->label = 'Zipcode';
		$defaultField->textId = 'notificationZip';
		$defaultField->type = 'text';
		$defaultField->required = 0;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		// User Information
		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Enter your information';
		$defaultField->label = 'First Name';
		$defaultField->textId = 'firstName';
		$defaultField->type = 'text';
		$defaultField->required = 1;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Enter your information';
		$defaultField->label = 'Last Name';
		$defaultField->textId = 'lastName';
		$defaultField->type = 'text';
		$defaultField->required = 1;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Enter your information';
		$defaultField->label = 'Don\'t show my name publicly';
		$defaultField->textId = 'makeAnonymous';
		$defaultField->type = 'checkbox';
		$defaultField->required = 0;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Enter your information';
		$defaultField->label = 'Email Address';
		$defaultField->textId = 'emailAddress';
		$defaultField->type = 'text';
		$defaultField->note = 'Your receipt will be emailed here';
		$defaultField->required = 1;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Enter your information';
		$defaultField->label = 'Address';
		$defaultField->textId = 'address';
		$defaultField->type = 'text';
		$defaultField->required = 1;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Enter your information';
		$defaultField->label = 'Address 2';
		$defaultField->textId = 'address2';
		$defaultField->type = 'text';
		$defaultField->required = 0;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Enter your information';
		$defaultField->label = 'City';
		$defaultField->textId = 'city';
		$defaultField->type = 'text';
		$defaultField->required = 1;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Enter your information';
		$defaultField->label = 'State';
		$defaultField->textId = 'state';
		$defaultField->type = 'text';
		$defaultField->required = 1;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		$defaultField = new DonationFormFields();
		$defaultField->donationSettingId = $donationSettingId;
		$defaultField->category = 'Enter your information';
		$defaultField->label = 'Zip';
		$defaultField->textId = 'zip';
		$defaultField->type = 'text';
		$defaultField->required = 1;
		$defaultField->insert();
		$defaultFieldsToDisplay[] = $defaultField;

		return $defaultFieldsToDisplay;

	}

}