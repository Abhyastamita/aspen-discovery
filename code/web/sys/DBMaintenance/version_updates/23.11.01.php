<?php

function getUpdates23_11_01(): array {
	$curTime = time();
	return [
        /*'name' => [
            'title' => '',
            'description' => '',
            'continueOnError' => false,
            'sql' => [
                ''
            ]
		], //name*/

		'user_barcode_index' => [
			'title'=> 'Add index of source and ils_barcode',
			'description' => 'Add index of source and ils_barcode',
			'sql' => [
				'ALTER TABLE user ADD INDEX user_barcode(source, ils_barcode)',
			]
		], //user_barcode_index
		'failed_login_index' => [
			'title'=> 'Add index to failed_logins_by_ip_address',
			'description' => 'Add index to failed_logins_by_ip_address',
			'sql' => [
				'alter table failed_logins_by_ip_address add index ipAddress(ipAddress);',
			]
		], //failed_login_index
    ];
}