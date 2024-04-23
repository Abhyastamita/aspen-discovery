package org.aspen_discovery.grouping;

import org.aspen_discovery.reindexer.GroupedWorkIndexer;
import com.turning_leaf_technologies.indexing.RecordIdentifier;
import com.turning_leaf_technologies.indexing.SideLoadSettings;
import com.turning_leaf_technologies.logging.BaseIndexingLogEntry;
import org.apache.logging.log4j.Logger;

import java.sql.Connection;

public class SideLoadedRecordGrouper extends BaseMarcRecordGrouper {
	private final SideLoadSettings settings;
	/**
	 * Creates a record grouping processor that saves results to the database.
	 *
	 * @param dbConnection   - The Connection to the database
	 * @param settings        - The profile that we are grouping records for
	 * @param logger         - A logger to store debug and error messages to.
	 */
	public SideLoadedRecordGrouper(String serverName, Connection dbConnection, SideLoadSettings settings, BaseIndexingLogEntry logEntry, Logger logger) {
		super(serverName, settings, dbConnection, logEntry, logger);
		this.settings = settings;
	}

	public String processMarcRecord(org.marc4j.marc.Record marcRecord, boolean primaryDataChanged, String originalGroupedWorkId, GroupedWorkIndexer indexer) {
		RecordIdentifier primaryIdentifier = getPrimaryIdentifierFromMarcRecord(marcRecord, settings);

		if (primaryIdentifier != null){
			//Get data for the grouped record
			GroupedWork workForTitle = setupBasicWorkForIlsRecord(marcRecord);

			addGroupedWorkToDatabase(primaryIdentifier, workForTitle, primaryDataChanged, originalGroupedWorkId);
			return workForTitle.getPermanentId();
		}else{
			//The record is suppressed
			return null;
		}
	}
}
