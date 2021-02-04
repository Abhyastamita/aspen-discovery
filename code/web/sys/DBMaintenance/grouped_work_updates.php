<?php

function getGroupedWorkUpdates(){
	return array(
		'grouped_works' => array(
			'title' => 'Setup Grouped Works',
			'description' => 'Sets up tables for grouped works so we can index and display them.',
			'sql' => array(
				"CREATE TABLE IF NOT EXISTS grouped_work (
					id BIGINT(20) NOT NULL AUTO_INCREMENT,
					permanent_id CHAR(36) NOT NULL,
					title VARCHAR(100) NULL,
					author VARCHAR(50) NULL,
					subtitle VARCHAR(175) NULL,
					grouping_category VARCHAR(25) NOT NULL,
					PRIMARY KEY (id),
					UNIQUE KEY permanent_id (permanent_id),
					KEY title (title,author,grouping_category)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8",
			),

		),

		'grouped_works_2' => array(
			'title' => 'Grouped Work update 2',
			'description' => 'Updates grouped works to add a full title field.',
			'sql' => array(
				"ALTER TABLE `grouped_work` ADD `full_title` VARCHAR( 276 ) NOT NULL",
				"ALTER TABLE `grouped_work` ADD INDEX(`full_title`)",
			),
		),

		'grouped_works_remove_split_titles' => array(
			'title' => 'Grouped Work Remove Split Titles',
			'description' => 'Updates grouped works to add a full title field.',
			'sql' => array(
				"ALTER TABLE `grouped_work` DROP COLUMN `title`",
				"ALTER TABLE `grouped_work` DROP COLUMN `subtitle`",
			),
		),

		'grouped_works_primary_identifiers' => array(
			'title' => 'Grouped Work Primary Identifiers',
			'description' => 'Add primary identifiers table for works.',
			'sql' => array(
				"CREATE TABLE IF NOT EXISTS grouped_work_primary_identifiers (
					id BIGINT(20) NOT NULL AUTO_INCREMENT,
					grouped_work_id BIGINT(20) NOT NULL,
					`type` ENUM('ils', 'external_econtent', 'acs', 'free', 'overdrive' ) NOT NULL,
					identifier VARCHAR(36) NOT NULL,
					PRIMARY KEY (id),
					UNIQUE KEY (`type`,identifier),
					KEY grouped_record_id (grouped_work_id)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8",
			),
		),

		'grouped_works_primary_identifiers_1' => array(
			'title' => 'Grouped Work Primary Identifiers Update 1',
			'description' => 'Add additional types of identifiers.',
			'sql' => array(
				"ALTER TABLE grouped_work_primary_identifiers CHANGE `type` `type` ENUM('ils', 'external', 'drm', 'free', 'overdrive' ) NOT NULL",
			),
		),

		'grouped_works_partial_updates' => array(
			'title' => 'Grouped Work Partial Updates',
			'description' => 'Updates to allow only changed records to be regrouped.',
			'sql' => array(
				"ALTER TABLE grouped_work ADD date_updated INT(11)",
			),
		),

		'grouped_work_engine' => array(
			'title' => 'Grouped Work Engine',
			'description' => 'Change storage engine to InnoDB for grouped work tables',
			'sql' => array(
				'ALTER TABLE `grouped_work` ENGINE = InnoDB',
				'ALTER TABLE `grouped_work_primary_identifiers` ENGINE = InnoDB',
				'ALTER TABLE `ils_marc_checksums` ENGINE = InnoDB',
			)
		),

		'grouped_work_merging' => array(
			'title' => 'Grouped Work Merging',
			'description' => 'Add a new table to allow manual merging of grouped works',
			'sql' => array(
				"CREATE TABLE IF NOT EXISTS merged_grouped_works(
					id BIGINT(20) NOT NULL AUTO_INCREMENT,
					sourceGroupedWorkId CHAR(36) NOT NULL,
					destinationGroupedWorkId CHAR(36) NOT NULL,
					notes VARCHAR(250) NOT NULL DEFAULT '',
					PRIMARY KEY (id),
					UNIQUE KEY (sourceGroupedWorkId,destinationGroupedWorkId)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8",
			)
		),

		'grouped_work_index_date_updated' => array(
			'title' => 'Grouped Work Index Date Update',
			'description' => 'Index date updated to improve performance',
			'sql' => array(
				"ALTER TABLE `grouped_work` ADD INDEX(`date_updated`)",
			)
		),

		'grouped_work_primary_identifiers_hoopla' => array(
			'title' => 'Grouped Work Updates to support Hoopla',
			'description' => 'Allow hoopla as a valid identifier type',
			'sql' => array(
				"ALTER TABLE grouped_work_primary_identifiers CHANGE `type` `type` ENUM('ils', 'external', 'drm', 'free', 'overdrive', 'hoopla' ) NOT NULL",
			),
		),

		'grouped_work_index_cleanup' => array(
			'title' => 'Cleanup Grouped Work Indexes',
			'description' => 'Cleanup Indexes for better performance',
			'continueOnError' => true,
			'sql' => array(
				"DROP INDEX title on grouped_work",
				"DROP INDEX full_title on grouped_work",
			),
		),

		'grouped_work_primary_identifier_types' => array(
			'title' => 'Expand Primary Identifiers Types ',
			'description' => 'Expand Primary Identifiers so they can be any type to make it easier to index different collections.',
			'continueOnError' => true,
			'sql' => array(
				"ALTER TABLE grouped_work_primary_identifiers CHANGE `type` `type` VARCHAR(50) NOT NULL",
			),
		),

		'increase_ilsID_size_for_ils_marc_checksums' => array(
			'title' => 'Expand ilsId Size',
			'description' => 'Increase the column size of the ilsId in the ils_marc_checksums table to accomodate larger Sideload Ids.',
			'continueOnError' => true,
			'sql' => array(
				"ALTER TABLE `ils_marc_checksums` CHANGE COLUMN `ilsId` `ilsId` VARCHAR(50) NOT NULL ;",
			),
		),

		'grouped_work_alternate_titles' => [
			'title' => 'Grouped Work alternate titles',
			'description' => 'Setup alternate titles and authors for grouped works',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS grouped_work_alternate_titles (
    				id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					permanent_id CHAR(36) NOT NULL,
					alternateTitle VARCHAR( 276 ),
					alternateAuthor VARCHAR(50),
					addedBy INT(11),
					dateAdded INT(11),
					INDEX (permanent_id),
					INDEX (alternateTitle, alternateAuthor)
				) ENGINE INNODB'
			],
		],

		'grouped_work_display_info' => [
			'title' => 'Grouped Work Display Information',
			'description' => 'Allow the display title, author, and series information to be set for a grouped work',
			'sql' => [
				'DROP TABLE IF EXISTS grouped_work_display_title_author',
				'CREATE TABLE IF NOT EXISTS grouped_work_display_info (
					id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					permanent_id CHAR(36) NOT NULL UNIQUE,
					title VARCHAR(276),
					author VARCHAR(50),
					seriesName VARCHAR(255),
					seriesDisplayOrder INT DEFAULT 0,
					addedBy INT(11),
					dateAdded INT(11),
					INDEX (permanent_id)
				) ENGINE INNODB'
			],
		],

		'author_authorities' => [
			'title' => 'Setup author authorities',
			'description' => 'Create tables to store author authority information',
			'sql' => [
				'CREATE TABLE author_authority (
					id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					author VARCHAR(512) NOT NULL UNIQUE,
					dateAdded INT(11)
				)',
				'CREATE TABLE author_authority_alternative (
					id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					authorId INT(11),
					alternativeAuthor VARCHAR(512) NOT NULL UNIQUE,
					INDEX (authorId)
				)'
			]
		],

		'author_authorities_normalized_values' => [
			'title' => 'Add Normalized Values to Author Authorities',
			'description' => 'Add a normalized value for author authorities to optimize grouping',
			'sql'=> [
				'ALTER TABLE author_authority ADD COLUMN normalized VARCHAR(512)',
				'ALTER TABLE author_authority_alternative ADD COLUMN normalized VARCHAR(512)',
			]
		]
	);
}
