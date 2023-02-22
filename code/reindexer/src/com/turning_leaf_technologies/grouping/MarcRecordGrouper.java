package com.turning_leaf_technologies.grouping;

import com.turning_leaf_technologies.indexing.IlsExtractLogEntry;
import com.turning_leaf_technologies.indexing.IndexingProfile;
import com.turning_leaf_technologies.indexing.RecordIdentifier;
import com.turning_leaf_technologies.logging.BaseIndexingLogEntry;
import com.turning_leaf_technologies.marc.MarcUtil;
import com.turning_leaf_technologies.reindexer.GroupedWorkIndexer;
import com.turning_leaf_technologies.strings.AspenStringUtils;
import org.apache.logging.log4j.Logger;
import org.marc4j.marc.*;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.*;
import java.util.regex.Pattern;

/**
 * A base class for setting title, author, and format for a MARC record
 * allows us to override certain information (especially format determination)
 * by library.
 */
public class MarcRecordGrouper extends BaseMarcRecordGrouper {
	private final IndexingProfile profile;
	private final String itemTag;
	private final int itemTagInt;
	private final boolean useEContentSubfield;
	private final char eContentDescriptor;
	private PreparedStatement getExistingParentRecordsStmt;
	private PreparedStatement addParentRecordStmt;
	private PreparedStatement deleteParentRecordStmt;
	private PreparedStatement updateChildTitleStmt;

	/**
	 * Creates a record grouping processor that saves results to the database.
	 *
	 * @param dbConnection   - The Connection to the database
	 * @param profile        - The profile that we are grouping records for
	 * @param logger         - A logger to store debug and error messages to.
	 */
	public MarcRecordGrouper(String serverName, Connection dbConnection, IndexingProfile profile, BaseIndexingLogEntry logEntry, Logger logger) {
		super(serverName, profile, dbConnection, logEntry, logger);
		this.profile = profile;

		itemTag = profile.getItemTag();
		itemTagInt = profile.getItemTagInt();
		eContentDescriptor = profile.getEContentDescriptor();
		useEContentSubfield = profile.getEContentDescriptor() != ' ';

		super.setupDatabaseStatements(dbConnection);

		super.loadAuthorities(dbConnection);

		loadTranslationMaps(dbConnection);

		try {
			getExistingParentRecordsStmt = dbConnection.prepareStatement("SELECT * FROM record_parents where childRecordId = ?", ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
			addParentRecordStmt = dbConnection.prepareStatement("INSERT INTO record_parents (childRecordId, parentRecordId, childTitle) VALUES (?, ?, ?)");
			deleteParentRecordStmt = dbConnection.prepareStatement("DELETE FROM record_parents WHERE childRecordId = ? AND parentRecordId = ?");
			updateChildTitleStmt = dbConnection.prepareStatement("UPDATE record_parents set childTitle = ? where childRecordId = ? and parentRecordId = ?");
		}catch (SQLException e) {
			logEntry.incErrors("Error loading prepared statements for loading parent records", e);
		}

	}

	private void loadTranslationMaps(Connection dbConnection) {
		try {
			PreparedStatement loadMapsStmt = dbConnection.prepareStatement("SELECT * FROM translation_maps where indexingProfileId = ?", ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
			PreparedStatement loadMapValuesStmt = dbConnection.prepareStatement("SELECT * FROM translation_map_values where translationMapId = ?", ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
			loadMapsStmt.setLong(1, profile.getId());
			ResultSet translationMapsRS = loadMapsStmt.executeQuery();
			while (translationMapsRS.next()){
				HashMap<String, String> translationMap = new HashMap<>();
				String mapName = translationMapsRS.getString("name");
				long translationMapId = translationMapsRS.getLong("id");

				loadMapValuesStmt.setLong(1, translationMapId);
				ResultSet mapValuesRS = loadMapValuesStmt.executeQuery();
				while (mapValuesRS.next()){
					String value = mapValuesRS.getString("value");
					String translation = mapValuesRS.getString("translation");

					translationMap.put(value, translation);
				}
				mapValuesRS.close();
				translationMaps.put(mapName, translationMap);
			}
			translationMapsRS.close();

			PreparedStatement getFormatMapStmt = dbConnection.prepareStatement("SELECT * from format_map_values WHERE indexingProfileId = ?");
			getFormatMapStmt.setLong(1, profile.getId());
			ResultSet formatMapRS = getFormatMapStmt.executeQuery();
			HashMap <String, String> formatMap = new HashMap<>();
			translationMaps.put("format", formatMap);
			HashMap <String, String> formatCategoryMap = new HashMap<>();
			translationMaps.put("formatCategory", formatCategoryMap);
			while (formatMapRS.next()){
				String format = formatMapRS.getString("value");
				formatMap.put(format.toLowerCase(), formatMapRS.getString("format"));
				formatCategoryMap.put(format.toLowerCase(), formatMapRS.getString("formatCategory"));
			}
			formatMapRS.close();
		}catch (Exception e){
			logEntry.incErrors("Error loading translation maps", e);
		}

	}

	private static final Pattern overdrivePattern = Pattern.compile("(?i)^http://.*?lib\\.overdrive\\.com/ContentDetails\\.htm\\?id=[\\da-f]{8}-[\\da-f]{4}-[\\da-f]{4}-[\\da-f]{4}-[\\da-f]{12}$");

	private String getFormatFromItems(Record record, char formatSubfield) {
		List<DataField> itemFields = getDataFields(record, itemTagInt);
		for (DataField itemField : itemFields) {
			if (itemField.getSubfield(formatSubfield) != null) {
				String originalFormat = itemField.getSubfield(formatSubfield).getData().toLowerCase();
				if (translationMaps.get("formatCategory").containsKey(originalFormat)){
					String format = translateValue("formatCategory", originalFormat);
					String formatCategory = categoryMap.get(format.toLowerCase());
					if (formatCategory != null){
						return formatCategory;
					}else{
						logger.warn("Did not find a grouping category for format " + format.toLowerCase());
					}
				}else{
					logger.warn("Did not find a format category for format " + originalFormat);
				}
			}
		}
		return null;
	}

	public String processMarcRecord(Record marcRecord, boolean primaryDataChanged, String originalGroupedWorkId, GroupedWorkIndexer indexer) {
		RecordIdentifier primaryIdentifier = getPrimaryIdentifierFromMarcRecord(marcRecord, profile);

		if (primaryIdentifier != null){
			//Get data for the grouped record
			GroupedWork workForTitle = setupBasicWorkForIlsRecord(marcRecord);

			if (profile.isProcessRecordLinking()){
				//Check to see if we have any 773 fields which identify the
				HashSet<String> parentRecords = getParentRecordIds(marcRecord);
				if (parentRecords.size() > 0){
					String firstParentRecordId = null;
					//Add the parent records to the database
					try {
						getExistingParentRecordsStmt.setString(1, primaryIdentifier.getIdentifier());
						ResultSet existingParentsRS = getExistingParentRecordsStmt.executeQuery();
						HashMap<String, String> existingParentRecords = new HashMap<>();
						while (existingParentsRS.next()){
							existingParentRecords.put(existingParentsRS.getString("parentRecordId"), existingParentsRS.getString("childTitle"));
						}
						DataField titleField = marcRecord.getDataField(245);
						String title;
						if (titleField == null) {
							title = "";
						}else{
							title = titleField.getSubfieldsAsString("abfgnp", " ");
						}

						//Loop through the records to see if they need to be added
						for (String parentRecordId : parentRecords){
							if (firstParentRecordId == null) {
								firstParentRecordId = parentRecordId;
							}
							if (existingParentRecords.containsKey(parentRecordId)){
								try{
									if (!existingParentRecords.get(parentRecordId).equals(title)){
										updateChildTitleStmt.setString(1, AspenStringUtils.trimTo(750, title));
										updateChildTitleStmt.setString(2, primaryIdentifier.getIdentifier());
										updateChildTitleStmt.setString(3, parentRecordId);
										updateChildTitleStmt.executeUpdate();
									}
									existingParentRecords.remove(parentRecordId);
								}catch (Exception e){
									logEntry.incErrors("Error updating parent record for " + primaryIdentifier.getIdentifier() + " in the database", e);
								}
							}else{
								try{
									addParentRecordStmt.setString(1, primaryIdentifier.getIdentifier());
									addParentRecordStmt.setString(2, parentRecordId);
									addParentRecordStmt.setString(3, AspenStringUtils.trimTo(750, title));
									addParentRecordStmt.executeUpdate();
								}catch (Exception e){
									logEntry.incErrors("Error adding parent record for " + primaryIdentifier.getIdentifier() + " in the database", e);
								}
							}
						}
						for (String oldParentRecordId : existingParentRecords.keySet()){
							try{
								deleteParentRecordStmt.setString(1, primaryIdentifier.getIdentifier());
								deleteParentRecordStmt.setString(2,oldParentRecordId);
								deleteParentRecordStmt.executeUpdate();
							}catch (Exception e){
								logEntry.incErrors("Error deleting parent record for " + primaryIdentifier.getIdentifier() + ", " + oldParentRecordId, e);
							}
						}
					}catch (Exception e){
						logEntry.incErrors("Error adding parent records to the database", e);
					}
					//MDN 9/24/22 even if the record has parents, we want to group it so we have information about
					//the record, and it's items in the database.
					//return null;

					//if the record does have a parent, we're going to cheat a bit and use the info for the parent record when grouping
					if (firstParentRecordId != null) {
						Record parentMarcRecord = indexer.loadMarcRecordFromDatabase(profile.getName(), firstParentRecordId, logEntry);
						workForTitle = setupBasicWorkForIlsRecord(parentMarcRecord);
					}
				}
			}

			addGroupedWorkToDatabase(primaryIdentifier, workForTitle, primaryDataChanged, originalGroupedWorkId);
			return workForTitle.getPermanentId();
		}else{
			//The record is suppressed
			return null;
		}
	}

	protected String setGroupingCategoryForWork(Record marcRecord, GroupedWork workForTitle) {
		String groupingFormat;
		if (profile.getFormatSource().equals("item")){
			//get format from item
			groupingFormat = getFormatFromItems(marcRecord, profile.getFormat());
			if (groupingFormat == null || groupingFormat.length() == 0){
				//Do a bib level determination
				String format = getFormatFromBib(marcRecord);
				groupingFormat = categoryMap.get(formatsToFormatCategory.get(format.toLowerCase()));
				workForTitle.setGroupingCategory(groupingFormat);
			}else {
				workForTitle.setGroupingCategory(groupingFormat);
			}
		}else{
			groupingFormat = super.setGroupingCategoryForWork(marcRecord, workForTitle);
		}
		return groupingFormat;
	}

	public RecordIdentifier getPrimaryIdentifierFromMarcRecord(Record marcRecord, IndexingProfile indexingProfile){
		RecordIdentifier identifier = super.getPrimaryIdentifierFromMarcRecord(marcRecord, indexingProfile);

		if (indexingProfile.isDoAutomaticEcontentSuppression()) {
			//Check to see if the record is an overdrive record
			if (useEContentSubfield) {
				boolean allItemsSuppressed = true;

				List<DataField> itemFields = getDataFields(marcRecord, itemTagInt);
				int numItems = itemFields.size();
				for (DataField itemField : itemFields) {
					if (itemField.getSubfield(eContentDescriptor) != null) {
						//Check the protection types and sources
						String eContentData = itemField.getSubfield(eContentDescriptor).getData();
						if (eContentData.indexOf(':') >= 0) {
							String[] eContentFields = eContentData.split(":");
							String sourceType = eContentFields[0].toLowerCase().trim();
							if (!sourceType.equals("overdrive") && !sourceType.equals("hoopla")) {
								allItemsSuppressed = false;
							}
						} else {
							allItemsSuppressed = false;
						}
					} else {
						allItemsSuppressed = false;
					}
				}
				if (numItems == 0) {
					allItemsSuppressed = false;
				}
				if (allItemsSuppressed && identifier != null) {
					//Don't return a primary identifier for this record (we will suppress the bib and just use OverDrive APIs)
					identifier.setSuppressed();
				}
			} else {
				//Check the 856 for an overdrive url
				if (identifier != null) {
					List<DataField> linkFields = getDataFields(marcRecord, 856);
					for (DataField linkField : linkFields) {
						if (linkField.getSubfield('u') != null) {
							//Check the url to see if it is from OverDrive
							//TODO: Suppress other eContent records as well?
							String linkData = linkField.getSubfield('u').getData().trim();
							if (MarcRecordGrouper.overdrivePattern.matcher(linkData).matches()) {
								identifier.setSuppressed();
							}
						}
					}
				}
			}
		}

		if (identifier != null) {
			if (indexingProfile.getSuppressRecordsWithUrlsMatching() != null) {
				Set<String> linkFields = MarcUtil.getFieldList(marcRecord, "856u");
				for (String linkData : linkFields) {
					if (indexingProfile.getSuppressRecordsWithUrlsMatching().matcher(linkData).matches()) {
						identifier.setSuppressed();
					}
				}
			}
		}

		if (identifier != null && identifier.isValid()){
			return identifier;
		}else{
			return null;
		}
	}

	protected String getFormatFromBib(Record record) {
		//Check to see if the title is eContent based on the 989 field
		if (useEContentSubfield) {
			List<DataField> itemFields = getDataFields(record, itemTag);
			for (DataField itemField : itemFields) {
				if (itemField.getSubfield(eContentDescriptor) != null) {
					//The record is some type of eContent.  For this purpose, we don't care what type.
					return "eContent";
				}
			}
		}
		return super.getFormatFromBib(record);
	}

	public void regroupAllRecords(Connection dbConn, IndexingProfile indexingProfile, GroupedWorkIndexer indexer, IlsExtractLogEntry logEntry)  throws SQLException {
		logEntry.addNote("Starting to regroup all records");
		PreparedStatement getAllRecordsToRegroupStmt = dbConn.prepareStatement("SELECT ilsId from ils_records where source = ? and deleted = 0", ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
		PreparedStatement getOriginalPermanentIdForRecordStmt = dbConn.prepareStatement("SELECT permanent_id from grouped_work_primary_identifiers join grouped_work on grouped_work_id = grouped_work.id WHERE type = ? and identifier = ?", ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
		getAllRecordsToRegroupStmt.setString(1, indexingProfile.getName());
		ResultSet allRecordsToRegroupRS = getAllRecordsToRegroupStmt.executeQuery();
		while (allRecordsToRegroupRS.next()) {
			logEntry.incRecordsRegrouped();
			String recordIdentifier = allRecordsToRegroupRS.getString("ilsId");
			String originalGroupedWorkId;
			getOriginalPermanentIdForRecordStmt.setString(1, indexingProfile.getName());
			getOriginalPermanentIdForRecordStmt.setString(2, recordIdentifier);
			ResultSet getOriginalPermanentIdForRecordRS = getOriginalPermanentIdForRecordStmt.executeQuery();
			if (getOriginalPermanentIdForRecordRS.next()){
				originalGroupedWorkId = getOriginalPermanentIdForRecordRS.getString("permanent_id");
			}else{
				originalGroupedWorkId = "false";
			}
			Record marcRecord = indexer.loadMarcRecordFromDatabase(indexingProfile.getName(), recordIdentifier, logEntry);
			if (marcRecord != null) {
				//Pass null to processMarcRecord.  It will do the lookup to see if there is an existing id there.
				String groupedWorkId = processMarcRecord(marcRecord, false, null, indexer);
				if (originalGroupedWorkId == null || !originalGroupedWorkId.equals(groupedWorkId)) {
					logEntry.incChangedAfterGrouping();
					//process records to regroup after every 1000 changes so we keep up with the changes.
					if (logEntry.getNumChangedAfterGrouping() % 1000 == 0){
						indexer.processScheduledWorks(logEntry, false, -1);
					}
				}
			}
		}

		//Finish reindexing anything that just changed
		if (logEntry.getNumChangedAfterGrouping() > 0){
			indexer.processScheduledWorks(logEntry, false, -1);
		}

		indexingProfile.clearRegroupAllRecords(dbConn, logEntry);
		logEntry.addNote("Finished regrouping all records");
		logEntry.saveResults();
	}

	public HashSet<String> getParentRecordIds(Record record) {
		List<DataField> analyticFields = record.getDataFields(773);
		HashSet<String> parentRecords = new HashSet<>();
		for (DataField analyticField : analyticFields){
			Subfield linkingSubfield = analyticField.getSubfield('w');
			if (linkingSubfield != null){
				//Establish a link and suppress this record
				String parentRecordId = linkingSubfield.getData();
				//Remove anything in parentheses
				parentRecordId = parentRecordId.replaceAll("\\(.*?\\)", "").trim();
				parentRecords.add(parentRecordId);
			}
		}
		return parentRecords;
	}
}
