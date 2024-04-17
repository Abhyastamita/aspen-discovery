<?php
/** @noinspection PhpUnused */
function getUpdates22_04_00(): array {
	return [
		/*'name' => [
			'title' => '',
			'description' => '',
			'sql' => [
				''
			]
		], //sample*/
		'restrictLoginToLibraryMembers' => [
			'title' => 'Restrict Login to Library Members',
			'description' => 'Allow restricting login to patrons of a specific home system',
			'sql' => [
				'ALTER TABLE library ADD COLUMN allowLoginToPatronsOfThisLibraryOnly TINYINT(1) DEFAULT 0',
				'ALTER TABLE library ADD COLUMN messageForPatronsOfOtherLibraries TEXT',
			],
		],
		//restrictLoginToLibraryMembers
		'catalogStatus' => [
			'title' => 'Catalog Status',
			'description' => 'Allow placing Aspen into offline mode via System Variables',
			'continueOnError' => true,
			'sql' => [
				'ALTER TABLE system_variables ADD COLUMN catalogStatus TINYINT(1) DEFAULT 0',
				"ALTER TABLE system_variables ADD COLUMN offlineMessage TEXT",
				"UPDATE system_variables set offlineMessage = 'The catalog is down for maintenance, please check back later.'",
				"DROP TABLE IF EXISTS offline_holds",
			],
		],
		//catalogStatus
		'user_hoopla_confirmation_checkout_prompt2' => [
			'title' => 'Hoopla Checkout Confirmation Prompt - recreate',
			'description' => 'Stores user preference whether or not to prompt for confirmation before checking out a title from Hoopla',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE `user` ADD COLUMN `hooplaCheckOutConfirmation` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1;",
			],
		],
		//user_hoopla_confirmation_checkout_prompt2
		'user_hideResearchStarters' => [
			'title' => 'User Hide Research Starters - recreate',
			'description' => 'Recreates column to hide research starters',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE user ADD COLUMN hideResearchStarters TINYINT(1) DEFAULT 0",
			],
		],
		//user_hideResearchStarters
		'user_role_uniqueness' => [
			'title' => 'User Role Uniqueness',
			'description' => 'Update Uniqueness for User Roles',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE user_roles DROP PRIMARY KEY",
				"ALTER TABLE user_roles ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY",
			],
		],
		//user_role_uniqueness
		'browse_category_times_shown' => [
			'title' => 'Browse Category Times Shown',
			'description' => 'Make times shown an int rather than medium int',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE browse_category CHANGE COLUMN numTimesShown numTimesShown INT NOT NULL DEFAULT  0",
			],
		],
		//browse_category_times_shown
		'permissions_create_events_springshare' => [
			'title' => 'Alters permissions for Events',
			'description' => 'Create permissions for Springshare LibCal; update permissions for LibraryMarket LibraryCalendar',
			'continueOnError' => true,
			'sql' => [
				"UPDATE permissions SET name = 'Administer LibraryMarket LibraryCalendar Settings', description = 'Allows the user to administer integration with LibraryMarket LibraryCalendar for all libraries.' WHERE name = 'Administer Library Calendar Settings'",
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Events', 'Administer Springshare LibCal Settings', 'Events', 20, 'Allows the user to administer integration with Springshare LibCal for all libraries.')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer Springshare LibCal Settings'))",
			],
		],
		// permissions_create_events_springshare
		'springshare_libcal_settings' => [
			'title' => 'Define events settings for Springshare LibCal integration',
			'description' => 'Initial setup of the Springshare LibCal integration',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS springshare_libcal_settings (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(100) NOT NULL UNIQUE,
					baseUrl VARCHAR(255) NOT NULL,
					calId SMALLINT NOT NULL,
					clientId SMALLINT NOT NULL,
					clientSecret VARCHAR(36) NOT NULL
				) ENGINE INNODB',
			],
		],
		// springshare_libcal_settings
		'springshare_libcal_settings_multiple_calId' => [
			'title' => 'Allow multiple calendar ids to be defined for libcal settings',
			'description' => 'Allow multiple calendar ids to be defined for libcal settings',
			'sql' => [
				'ALTER TABLE springshare_libcal_settings CHANGE calId calId VARCHAR(50) DEFAULT ""',
			],
		],
		// springshare_libcal_settings
		'springshare_libcal_events' => [
			'title' => 'Springshare LibCal Events Data',
			'description' => 'Setup tables to store events data for Springshare LibCal',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS springshare_libcal_events (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					settingsId INT NOT NULL,
					externalId varchar(36) NOT NULL,
					title varchar(255) NOT NULL,
					rawChecksum BIGINT,
					rawResponse MEDIUMTEXT,
					deleted TINYINT default 0,
					UNIQUE (settingsId, externalId)
				)',
			],
		],
		// springshare_libcal_events
		'ils_log_add_records_with_invalid_marc' => [
			'title' => 'ILS Log Records With Invalid MARC',
			'description' => 'Add Records With Invalid MARC to the ILS Log',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE ils_extract_log ADD COLUMN numRecordsWithInvalidMarc INT(11) NOT NULL DEFAULT 0",
			],
		],
		//ils_log_add_records_with_invalid_marc
		'increase_translation_map_value_length' => [
			'title' => 'Increase Translation Map Value Length',
			'description' => 'Increase Translation Map Value Length',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE translation_map_values CHANGE COLUMN value value varchar(255) COLLATE utf8mb4_general_ci NOT NULL",
			],
		],
		//increase_translation_map_value_length
		'fix_sideload_permissions' => [
			'title' => 'Fix sideload permissions',
			'description' => 'Fix permissions so sideload files can be uploaded properly',
			'sql' => [
				'fixSideLoadPermissions_22_04',
			],
		],
		//fix_sideload_permissions
		'xpressPay_settings' => [
			'title' => 'Add settings for Xpress-pay',
			'description' => 'Add settings for Xpress-pay integration',
			'continueOnError' => true,
			'sql' => [
				"CREATE TABLE IF NOT EXISTS xpresspay_settings (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					name VARCHAR(50) UNIQUE,
					paymentTypeCode VARCHAR(20)
				) ENGINE INNODB",
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('eCommerce', 'Administer Xpress-pay', '', 10, 'Controls if the user can change Xpress-pay settings. <em>This has potential security and cost implications.</em>')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer Xpress-pay'))",
				"ALTER TABLE library ADD COLUMN xpressPaySettingId INT(11) DEFAULT -1",
			],
		],
		//xpressPay_settings
		'add_worldpay_settings' => [
			'title' => 'Add additional FIS WorldPay Settings',
			'description' => 'Add additional FIS WorldPay Settings',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE worldpay_settings ADD COLUMN paymentSite VARCHAR(255) NOT NULL DEFAULT 0",
			],
		],
		//add_worldpay_settings
		'ticket_creation' => [
			'title' => 'Ticket Table Creation',
			'description' => 'Setup tables to handle tracking tickets within Aspen Greenhouse',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS ticket_status_feed (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					name VARCHAR(50) NOT NULL UNIQUE,
					rssFeed TEXT
				) ENGINE INNODB',
				'CREATE TABLE IF NOT EXISTS ticket_queue_feed (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					name VARCHAR(50) NOT NULL UNIQUE,
					rssFeed TEXT
				) ENGINE INNODB',
				'CREATE TABLE IF NOT EXISTS ticket_severity_feed (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					name VARCHAR(50) NOT NULL UNIQUE,
					rssFeed TEXT
				) ENGINE INNODB',
				'CREATE TABLE IF NOT EXISTS ticket_component_feed (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					name VARCHAR(50) NOT NULL UNIQUE,
					rssFeed TEXT
				) ENGINE INNODB',
				'CREATE TABLE IF NOT EXISTS ticket (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					ticketId VARCHAR(20) NOT NULL UNIQUE ,
					displayUrl VARCHAR(500),
					title TEXT,
					description TEXT, 
					dateCreated INT NOT NULL, 
					requestingPartner INT,
					status VARCHAR(50),
					queue VARCHAR(50),
					severity VARCHAR(50),
					partnerPriority INT DEFAULT 0,
					partnerPriorityChangeDate INT,
					dateClosed INT,
					developmentTaskId INT
				) ENGINE INNODB',
				'CREATE TABLE IF NOT EXISTS ticket_stats (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					year INT(4) NOT NULL,
					month INT(2) NOT NULL,
					day INT(2) NOT NULL,
					status VARCHAR(50),
					queue VARCHAR(50),
					severity VARCHAR(50),
					count INT DEFAULT 0,
					UNIQUE (year, month, day, status, queue, severity, count)
				) ENGINE INNODB',
				'CREATE TABLE IF NOT EXISTS development_task (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					name VARCHAR(255) NOT NULL UNIQUE,
					description MEDIUMTEXT,
					releaseId INT,
					weight INT,
					status VARCHAR(50),
					assignedTo INT
				) ENGINE INNODB',
				'CREATE TABLE IF NOT EXISTS aspen_release (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					name VARCHAR(10) NOT NULL UNIQUE,
					releaseDate INT
				) ENGINE INNODB',
			],
		],
		//ticket_creation
		'aspenSite_activeTicketFeed' => [
			'title' => 'Aspen Site - Active Ticket Feed',
			'description' => 'Add Active Ticket Feed to Aspen Site',
			'sql' => [
				"ALTER TABLE aspen_sites add COLUMN activeTicketFeed VARCHAR(255) DEFAULT ''",
			],
		],
		//aspenSite_activeTicketFeed
		'aspenSite_activeTicketFeed2' => [
			'title' => 'Aspen Site - Active Ticket Feed increase length',
			'description' => 'Increase length Active Ticket Feed in Aspen Site',
			'sql' => [
				"ALTER TABLE aspen_sites CHANGE COLUMN activeTicketFeed activeTicketFeed VARCHAR(1000) DEFAULT ''",
			],
		],
		//aspenSite_activeTicketFeed2
		'library_nameAndDobUpdates' => [
			'title' => 'Aspen Site - Active Ticket Feed increase length',
			'description' => 'Increase length Active Ticket Feed in Aspen Site',
			'sql' => [
				"ALTER TABLE library ADD COLUMN allowNameUpdates TINYINT(1) DEFAULT 1",
				"ALTER TABLE library ADD COLUMN allowDateOfBirthUpdates TINYINT(1) DEFAULT 1",
			],
		],
		//library_nameAndDobUpdates
		'reloadBadWords' => [
			'title' => 'Reload Bad Words',
			'description' => 'Reload the Bad Words List to remove some common words that should not be filtered',
			'continueOnError' => true,
			'sql' => [
				'TRUNCATE TABLE bad_words',
				'importBadWords',
			],
		],
		//reloadBadWords
		'permissions_bad_words' => [
			'title' => 'Create Bad Words Permissions',
			'description' => 'Create permissions for administration of bad words',
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Local Enrichment', 'Administer Bad Words', '', 65, 'Allows the user to administer bad words list.')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer Bad Words'))",
			],
		],
		// permissions_bad_words
		'hoopla_title_exclusion_updates' => [
			'title' => 'Hoopla Title Exclusion Updates',
			'description' => 'Move Hoopla title exclusion rules to scopes and add the ability to hide regardless of availability',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE hoopla_scopes ADD COLUMN excludeTitlesWithCopiesFromOtherVendors TINYINT(4) DEFAULT 0",
				"UPDATE hoopla_scopes SET excludeTitlesWithCopiesFromOtherVendors = (SELECT excludeTitlesWithCopiesFromOtherVendors FROM hoopla_settings WHERE hoopla_settings.id = hoopla_scopes.settingId)",
				"ALTER TABLE hoopla_settings DROP COLUMN excludeTitlesWithCopiesFromOtherVendors",
			],
		],
		// hoopla_title_exclusion_updates
		'ar_update_frequency' => [
			'title' => 'Accelerated Reader Update Frequency',
			'description' => 'Allow control over accelerated reader update frequency',
			'sql' => [
				'ALTER TABLE accelerated_reading_settings ADD COLUMN updateOn TINYINT DEFAULT 0',
				'ALTER TABLE accelerated_reading_settings ADD COLUMN updateFrequency TINYINT DEFAULT 0',
			],
		],
		//ar_update_frequency
		'treatLibraryUseOnlyGroupedStatusesAsAvailable' => [
			'title' => 'Indexing Profiles - treatLibraryUseOnlyGroupedStatusesAsAvailable',
			'description' => 'Add option in Indexing Profiles to choose if the Library Use Only Grouped Status is treated as available',
			'sql' => [
				'ALTER TABLE indexing_profiles ADD COLUMN treatLibraryUseOnlyGroupedStatusesAsAvailable TINYINT DEFAULT 1',
			],
		],
		//treatLibraryUseOnlyGroupedStatusesAsAvailable
	];
}

function fixSideLoadPermissions_22_04(&$update) {
	//Make sure we have the
	$status = '';
	require_once ROOT_DIR . '/sys/Indexing/SideLoad.php';
	$sideLoads = new SideLoad();
	$sideLoads->find();
	$numSideLoadsUpdated = 0;
	while ($sideLoads->fetch()) {
		if (!file_exists($sideLoads->marcPath)) {
			if (!mkdir($sideLoads->marcPath, 0775, true)) {
				$status .= 'Could not create marc path ' . $sideLoads->marcPath . '<br/>';
			}
		}
		if (!@chgrp($sideLoads->marcPath, 'aspen_apache')) {
			$status .= 'Could not set group to aspen_apache for ' . $sideLoads->marcPath . '<br/>';
		}
		if (!@chmod($sideLoads->marcPath, 0775)) {
			$status .= 'Could not set permissions for ' . $sideLoads->marcPath . '<br/>';
		}
		$numSideLoadsUpdated++;
	}

	if (strlen($status) == 0) {
		$update['success'] = true;
		$update['status'] = translate([
			'text' => 'Update succeeded, updated %1% sideloads',
			1 => $numSideLoadsUpdated,
			'isAdminFacing' => true,
		]);
	} else {
		$update['success'] = false;
		$update['status'] = $status;
	}

}