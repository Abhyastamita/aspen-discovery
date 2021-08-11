<?php
/** @noinspection PhpUnused */
function getUpdates21_10_00() : array
{
	return [
		'aspen_sites' => [
			'title' => 'Create Aspen Sites',
			'description' => 'Create Sites for the greenhouse',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS aspen_sites (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					name VARCHAR(50) UNIQUE,
					baseUrl VARCHAR(255) UNIQUE, 
					siteType INT DEFAULT 0,
					libraryType INT DEFAULT 0,
					libraryServes INT DEFAULT 0,
					implementationStatus INT DEFAULT 0,
					hosting VARCHAR(75), 
					operatingSystem VARCHAR(75), 
					notes TEXT
				) ENGINE INNODB'
			]
		], //aspen_sites
		'add_sorts_for_browsable_objects'=>[
			'title' => 'Add Sorts for Browsable Objects',
			'description' => 'Add new sorts for Browse Categories and Collection Spotlights',
			'sql' => [
				"ALTER TABLE collection_spotlight_lists CHANGE COLUMN defaultSort defaultSort ENUM('relevance', 'popularity', 'newest_to_oldest', 'oldest_to_newest', 'author', 'title', 'user_rating', 'holds', 'publication_year_desc', 'publication_year_asc') default 'relevance'",
				"ALTER TABLE browse_category CHANGE COLUMN defaultSort defaultSort ENUM('relevance', 'popularity', 'newest_to_oldest', 'oldest_to_newest', 'author', 'title', 'user_rating', 'holds', 'publication_year_desc', 'publication_year_asc') default 'relevance'"
			]
		], //add_sorts_for_browsable_objects
		'fix_ils_volume_indexes' => [
			'title' => 'Fix ILS Volume Indexes',
			'description' => 'Allow Volume Ids to be non unique',
			'sql' => [
				'ALTER TABLE ils_volume_info DROP index volumeId',
				'ALTER TABLE ils_volume_info DROP index recordId',
				'ALTER TABLE ils_volume_info Add unique index recordVolume(recordId, volumeId)',
			]
		], //fix_ils_volume_indexes
		'add_maxDaysToFreeze' => [
			'title' => 'Add max days to freeze option in library settings',
			'description' => 'Allow libraries to limit the amount of days out a user can freeze a hold',
			'sql' => [
				'ALTER TABLE library ADD COLUMN maxDaysToFreeze INT(11) DEFAULT -1'
			]
		], //add_maxDaysToFreeze
		'add_web_builder_portal_page_access' => [
			'title' => 'Store patron types allowed to access a custom page',
			'description' => 'Allow libraries to limit access to web builder custom pages based on patron type',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS web_builder_portal_page_access (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					portalPageId INT(11) NOT NULL, 
					patronTypeId INT(11) NOT NULL,
					UNIQUE INDEX (portalPageId, patronTypeId)
				) ENGINE INNODB'
			]
		], //add_web_builder_portal_page_access
		'add_requireLogin_to_portal_page' => [
			'title' => 'Add require login option to web builder custom pages',
			'description' => 'Allow libraries to require login to access custom pages',
			'sql' => [
				'ALTER TABLE web_builder_portal_page ADD COLUMN requireLogin TINYINT(1) DEFAULT 0'
			]
		], //add_requireLogin_to_portal_page
		'add_web_builder_basic_page_access' => [
			'title' => 'Store patron types allowed to access a basic page',
			'description' => 'Allow libraries to limit access to web builder basic pages based on patron type',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS web_builder_basic_page_access (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					basicPageId INT(11) NOT NULL, 
					patronTypeId INT(11) NOT NULL,
					UNIQUE INDEX (basicPageId, patronTypeId)
				) ENGINE INNODB'
			]
		], //add_web_builder_basic_page_access
		'add_requireLogin_to_basic_page' => [
			'title' => 'Add require login option to web builder basic pages',
			'description' => 'Allow libraries to require login to access basic pages',
			'sql' => [
				'ALTER TABLE web_builder_basic_page ADD COLUMN requireLogin TINYINT(1) DEFAULT 0'
			]
		], //add_requireLogin_to_basic_page
		'add_displayItemBarcode' => [
			'title' => 'Add ability to display barcodes for items checked out',
			'description' => 'Allow libraries to display barcodes for items that are checked out',
			'sql' => [
				'ALTER TABLE library ADD COLUMN displayItemBarcode TINYINT(1) DEFAULT 0'
			]
		], //add_displayItemBarcode
		'check_titles_in_user_list_entries' => [
			'title' => 'Check for titles in user list entries',
			'description' => 'If missing, populate existing user list entries with grouped work titles',
			'sql' => [
				"UPDATE user_list_entry SET user_list_entry.title=(SELECT LEFT(grouped_work.full_title, 50) FROM grouped_work WHERE grouped_work.permanent_id = user_list_entry.sourceId)",
			]
		], //check_titles_in_user_list_entries
		'propay_certStr_length' => [
			'title' => 'Fix ProPay CertStr Length',
			'description' => 'Add Additional Fields to ProPay Settings to create merchant profiles',
			'sql' => [
				'ALTER TABLE propay_settings CHANGE COLUMN certStr certStr VARCHAR(30)',
			]
		], //propay_certStr_length
	];
}