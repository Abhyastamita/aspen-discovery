package org.aspendiscovery.palace_project;

import com.turning_leaf_technologies.config.ConfigUtil;
import com.turning_leaf_technologies.file.JarUtil;
import com.turning_leaf_technologies.grouping.RecordGroupingProcessor;
import com.turning_leaf_technologies.grouping.RemoveRecordFromWorkResult;
import com.turning_leaf_technologies.indexing.IndexingUtils;
import com.turning_leaf_technologies.logging.LoggingUtil;

import com.turning_leaf_technologies.net.NetworkUtils;
import com.turning_leaf_technologies.net.WebServiceResponse;
import com.turning_leaf_technologies.reindexer.GroupedWorkIndexer;
import com.turning_leaf_technologies.strings.AspenStringUtils;
import com.turning_leaf_technologies.util.SystemUtils;
import org.apache.logging.log4j.Logger;
import org.ini4j.Ini;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.sql.*;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.HashMap;
import java.util.zip.CRC32;


public class PalaceProjectExportMain {
	private static Logger logger;
	private static String serverName;

	private static Ini configIni;

	private static Long startTimeForLogging;
	private static PalaceProjectExportLogEntry logEntry;
	private static String palaceProjectBaseUrl;

	private static Connection aspenConn;
	private static PreparedStatement addPalaceProjectTitleToDbStmt;
	private static PreparedStatement updatePalaceProjectTitleInDbStmt;
	private static PreparedStatement deletePalaceProjectTitleFromDbStmt;

	//Record grouper
	private static GroupedWorkIndexer groupedWorkIndexer;
	private static RecordGroupingProcessor recordGroupingProcessorSingleton = null;

	//Existing records
	private static HashMap<String, PalaceProjectTitle> existingRecords = new HashMap<>();

	//For Checksums
	private static final CRC32 checksumCalculator = new CRC32();

	public static void main(String[] args){
		boolean extractSingleWork = false;
		String singleWorkId = null;
		if (args.length == 0) {
			serverName = AspenStringUtils.getInputFromCommandLine("Please enter the server name");
			if (serverName.isEmpty()) {
				System.out.println("You must provide the server name as the first argument.");
				System.exit(1);
			}
			String extractSingleWorkResponse = AspenStringUtils.getInputFromCommandLine("Process a single work? (y/N)");
			if (extractSingleWorkResponse.equalsIgnoreCase("y")) {
				extractSingleWork = true;
			}
		} else {
			serverName = args[0];
			if (args.length > 1){
				if (args[1].equalsIgnoreCase("singleWork") || args[1].equalsIgnoreCase("singleRecord")){
					extractSingleWork = true;
					if (args.length > 2) {
						singleWorkId = args[2];
					}
				}
			}
		}
		if (extractSingleWork && singleWorkId == null) {
			singleWorkId = AspenStringUtils.getInputFromCommandLine("Enter the id of the title to extract (will start with urn:)");
		}

		String processName = "palace_project_export";
		logger = LoggingUtil.setupLogging(serverName, processName);

		//Get the checksum of the JAR when it was started, so we can stop if it has changed.
		long myChecksumAtStart = JarUtil.getChecksumForJar(logger, processName, "./" + processName + ".jar");
		long reindexerChecksumAtStart = JarUtil.getChecksumForJar(logger, "reindexer", "../reindexer/reindexer.jar");
		long timeAtStart = new Date().getTime();

		while (true) {
			//Palace Project only needs to run once a day
			Date startTime = new Date();
			startTimeForLogging = startTime.getTime() / 1000;
			logger.info(startTime + ": Starting Palace Project Export");

			// Read the base INI file to get information about the server (current directory/cron/config.ini)
			configIni = ConfigUtil.loadConfigFile("config.ini", serverName, logger);

			//Connect to the Aspen database
			aspenConn = connectToDatabase();

			//Check to see if the jar has changes before processing records, and if so quit
			if (myChecksumAtStart != JarUtil.getChecksumForJar(logger, processName, "./" + processName + ".jar")){
				IndexingUtils.markNightlyIndexNeeded(aspenConn, logger);
				disconnectDatabase(aspenConn);
				break;
			}
			if (reindexerChecksumAtStart != JarUtil.getChecksumForJar(logger, "reindexer", "../reindexer/reindexer.jar")){
				IndexingUtils.markNightlyIndexNeeded(aspenConn, logger);
				disconnectDatabase(aspenConn);
				break;
			}

			//Start a log entry
			createDbLogEntry(startTime, aspenConn);
			logEntry.addNote("Starting extract");
			logEntry.saveResults();

			//Do work here
			boolean updatesRun;
			if (singleWorkId == null) {
				updatesRun = exportPalaceProjectData();
			} else {
				//exportSinglePalaceProjectTitle(singleWorkId);
				System.out.println("Palace Project does not currently support extracting individual records.");
				updatesRun = true;
			}

			processRecordsToReload(logEntry);

			if (recordGroupingProcessorSingleton != null) {
				recordGroupingProcessorSingleton.close();
				recordGroupingProcessorSingleton = null;
			}

			if (groupedWorkIndexer != null) {
				groupedWorkIndexer.finishIndexingFromExtract(logEntry);
				groupedWorkIndexer.close();
				groupedWorkIndexer = null;
				existingRecords = null;
			}

			if (logEntry.hasErrors()) {
				logger.error("There were errors during the export!");
			}

			logger.info("Finished exporting data " + new Date());
			long endTime = new Date().getTime();
			long elapsedTime = endTime - startTime.getTime();
			logger.info("Elapsed Minutes " + (elapsedTime / 60000));

			//Mark that indexing has finished
			logEntry.setFinished();

			if (!updatesRun) {
				//delete the log entry
				try {
					PreparedStatement deleteLogEntryStmt = aspenConn.prepareStatement("DELETE from palace_project_export_log WHERE id = " + logEntry.getLogEntryId());
					deleteLogEntryStmt.executeUpdate();
				} catch (SQLException e) {
					logger.error("Could not delete log export ", e);
				}

			}

			if (extractSingleWork) {
				disconnectDatabase(aspenConn);
				break;
			}

			//Check to see if the jar has changes, and if so quit
			if (myChecksumAtStart != JarUtil.getChecksumForJar(logger, processName, "./" + processName + ".jar")){
				IndexingUtils.markNightlyIndexNeeded(aspenConn, logger);
				disconnectDatabase(aspenConn);
				break;
			}
			if (reindexerChecksumAtStart != JarUtil.getChecksumForJar(logger, "reindexer", "../reindexer/reindexer.jar")){
				IndexingUtils.markNightlyIndexNeeded(aspenConn, logger);
				disconnectDatabase(aspenConn);
				break;
			}
			//Check to see if it's between midnight and 1 am and the jar has been running more than 15 hours.  If so, restart just to clean up memory.
			GregorianCalendar nowAsCalendar = new GregorianCalendar();
			Date now = new Date();
			nowAsCalendar.setTime(now);
			if (nowAsCalendar.get(Calendar.HOUR_OF_DAY) <=1 && (now.getTime() - timeAtStart) > 15 * 60 * 60 * 1000 ){
				logger.info("Ending because we have been running for more than 15 hours and it's between midnight and one AM");
				disconnectDatabase(aspenConn);
				break;
			}
			//Check memory to see if we should close
			if (SystemUtils.hasLowMemory(configIni, logger)){
				logger.info("Ending because we have low memory available");
				disconnectDatabase(aspenConn);
				break;
			}

			disconnectDatabase(aspenConn);

			//Check to see if nightly indexing is running and if so, wait until it is done.
			if (IndexingUtils.isNightlyIndexRunning(configIni, serverName, logger)) {
				//Quit and we will restart after if finishes
				System.exit(0);
			}else {
				//Pause before running the next export (longer if we didn't get any actual changes)
				try {
					System.gc();
					Thread.sleep(1000 * 60 * 15);
				} catch (InterruptedException e) {
					logger.info("Thread was interrupted");
				}
			}
		}

		System.exit(0);
	}

	private static boolean exportPalaceProjectData() {
		boolean updatesRun = false;
		try{
			PreparedStatement getSettingsStmt = aspenConn.prepareStatement("SELECT * from palace_project_settings");

			ResultSet getSettingsRS = getSettingsStmt.executeQuery();
			int numSettings = 0;
			while (getSettingsRS.next()) {
				long settingsId = getSettingsRS.getLong("id");
				numSettings++;

				long lastUpdateOfChangedRecords = getSettingsRS.getLong("lastUpdateOfChangedRecords");
				long lastUpdateOfAllRecords = getSettingsRS.getLong("lastUpdateOfAllRecords");
				long lastUpdate = Math.max(lastUpdateOfChangedRecords, lastUpdateOfAllRecords);

				//Check to see if we should run.  For Palace Project we only need to check for updates once an hour.
				Date now = new Date();
				if ((now.getTime()  / 1000 - lastUpdate) < 60 * 60) {
					//Don't update since it hasn't been an hour
					//logger.warn("Not running since it hasn't been an hour.");
					continue;
				}

				logEntry.addNote("Starting update from Palace Project");
				logEntry.saveResults();

				palaceProjectBaseUrl = getSettingsRS.getString("apiUrl");
				String palaceProjectLibraryId = getSettingsRS.getString("libraryId");
				boolean doFullReload = getSettingsRS.getBoolean("runFullUpdate");

				//Get a list of all existing records in the database
				loadExistingTitles();

				String url = palaceProjectBaseUrl + "/" + palaceProjectLibraryId + "/crawlable";
				HashMap<String, String> headers = new HashMap<>();
				headers.put("Accept", "application/opds+json");
				headers.put("User-Agent", "Aspen Discovery");

				WebServiceResponse response = NetworkUtils.getURL(url, logger, headers);
				if (!response.isSuccess()) {
					logEntry.incErrors("Could not get titles from " + url + " " + response.getMessage());
				} else {
					JSONObject responseJSON = new JSONObject(response.getMessage());

					//Loop through facets to get a list of all collections for palace project
					HashMap<String, String> validCollections = new HashMap<>();
					if (responseJSON.has("facets")) {
						JSONArray facetList = responseJSON.getJSONArray("facets");
						for (int i = 0; i < facetList.length(); i++) {
							JSONObject curFacet = facetList.getJSONObject(i);
							if (curFacet.has("metadata")) {
								JSONObject facetMetadata = curFacet.getJSONObject("metadata");
								if (facetMetadata.getString("title").equals("Collection Name")) {
									JSONArray links = curFacet.getJSONArray("links");
									for (int j = 0; j < links.length(); j++) {
										JSONObject link = links.getJSONObject(j);
										String linkTitle = link.getString("title");
										if (linkTitle.equals("All") || linkTitle.contains("OverDrive") || linkTitle.contains("Axis 360") || linkTitle.contains("Boundless") || linkTitle.contains("Bibliotheca")) {
											continue;
										}
										validCollections.put(linkTitle, link.getString("href"));
									}
								}
							}
						}
					}

					for (String collectionName : validCollections.keySet()) {
						String collectionUrl = validCollections.get(collectionName);
						while (collectionUrl != null) {
							WebServiceResponse responseForCollection = NetworkUtils.getURL(collectionUrl, logger, headers);
							if (!response.isSuccess()) {
								logEntry.incErrors("Could not get titles from " + collectionUrl + " " + responseForCollection.getMessage());
							} else {
								JSONObject collectionResponseJSON = new JSONObject(responseForCollection.getMessage());
								if (collectionResponseJSON.has("publications")) {
									JSONArray responseTitles = collectionResponseJSON.getJSONArray("publications");
									if (responseTitles != null && !responseTitles.isEmpty()) {
										updateTitlesInDB(collectionName, responseTitles, doFullReload);
										logEntry.saveResults();
									}
								}
								collectionUrl = null;
								//Get the next URL
								if (collectionResponseJSON.has("links")) {
									JSONArray links = collectionResponseJSON.getJSONArray("links");
									for (int i = 0; i < links.length(); i++) {
										JSONObject curLink = links.getJSONObject(i);
										if (curLink.getString("rel").equals("next")) {
											collectionUrl = curLink.getString("href");
											break;
										}
									}
								}
							}
						}
					}
				}

				//remove any remaining titles that we did not find
				if (!logEntry.hasErrors()) {
					int numDeleted = 0;
					for (PalaceProjectTitle existingTitle : existingRecords.values()) {
						if (!existingTitle.isFoundInExport()) {
							deletePalaceProjectTitleFromDbStmt.setLong(1, existingTitle.getId());
							deletePalaceProjectTitleFromDbStmt.executeUpdate();
							//TODO: This needs to also account for the collection
							RemoveRecordFromWorkResult result = getRecordGroupingProcessor().removeRecordFromGroupedWork("palace_project", existingTitle.getPalaceProjectId());
							if (result.reindexWork) {
								getGroupedWorkIndexer().processGroupedWork(result.permanentId);
							} else if (result.deleteWork) {
								//Delete the work from solr and the database
								getGroupedWorkIndexer().deleteRecord(result.permanentId);
							}
							numDeleted++;
							logEntry.incDeleted();
						}
					}
					if (numDeleted > 0) {
						logEntry.saveResults();
						logger.warn("Deleted " + numDeleted + " old titles");
					}
				}

				updatesRun = true;

				//Set the extract time
				PreparedStatement updateSettingsStmt = null;
				if (doFullReload){
					if (!logEntry.hasErrors()) {
						updateSettingsStmt = aspenConn.prepareStatement("UPDATE palace_project_settings set lastUpdateOfAllRecords = ? where id = ?");
					} else {
						//force another full update
						PreparedStatement reactiveFullUpdateStmt = aspenConn.prepareStatement("UPDATE palace_project_settings set runFullUpdate = 1 where id = ?");
						reactiveFullUpdateStmt.setLong(1, settingsId);
						reactiveFullUpdateStmt.executeUpdate();
					}
				}else{
					updateSettingsStmt = aspenConn.prepareStatement("UPDATE palace_project_settings set lastUpdateOfChangedRecords = ? where id = ?");
				}
				if (updateSettingsStmt != null) {
					updateSettingsStmt.setLong(1, startTimeForLogging);
					updateSettingsStmt.setLong(2, settingsId);
					updateSettingsStmt.executeUpdate();
				}
			}
			if (numSettings == 0){
				logger.error("Unable to find settings for Palace Project, please add settings to the database");
			}
		}catch (Exception e){
			logEntry.incErrors("Error exporting Palace Project data", e);
		}
		return updatesRun;
	}

	private static void updateTitlesInDB(String collectionName, JSONArray responseTitles, boolean doFullReload) {
		logEntry.incNumProducts(responseTitles.length());
		for (int i = 0; i < responseTitles.length(); i++){
			try {
				JSONObject curTitle = responseTitles.getJSONObject(i);
				JSONObject curTitleMetadata = curTitle.getJSONObject("metadata");

				String rawResponse = curTitle.toString();
				checksumCalculator.reset();
				checksumCalculator.update(rawResponse.getBytes());
				long rawChecksum = checksumCalculator.getValue();

				String palaceProjectId = curTitleMetadata.getString("identifier");
				String title = curTitleMetadata.getString("title");

				PalaceProjectTitle existingTitle = existingRecords.get(palaceProjectId + collectionName);
				boolean recordUpdated = false;
				if (existingTitle != null) {
					//Record exists
					if ((existingTitle.getChecksum() != rawChecksum) || (existingTitle.getRawResponseLength() != rawResponse.length())){
						recordUpdated = true;
						logEntry.incUpdated();
					}
					existingTitle.setFoundInExport(true);
				}else{
					recordUpdated = true;
					logEntry.incAdded();
				}

				if (title.length() > 750) {
					title = title.substring(0, 750);
				}

				if (existingTitle == null){
					addPalaceProjectTitleToDbStmt.setString(1, palaceProjectId);
					addPalaceProjectTitleToDbStmt.setString(2, title);
					addPalaceProjectTitleToDbStmt.setString(3, collectionName);
					addPalaceProjectTitleToDbStmt.setLong(4, rawChecksum);
					addPalaceProjectTitleToDbStmt.setString(5, rawResponse);
					addPalaceProjectTitleToDbStmt.setLong(6, startTimeForLogging);
					try {
						addPalaceProjectTitleToDbStmt.executeUpdate();

						ResultSet generatedKeys = addPalaceProjectTitleToDbStmt.getGeneratedKeys();
						long palaceProjectAspenId = -1;
						if (generatedKeys.next()){
							palaceProjectAspenId = generatedKeys.getLong(1);
						}else{
							logEntry.incErrors("Could not add " + palaceProjectId + " to the database, did not get the Aspen ID back");
						}

						String groupedWorkId =  getRecordGroupingProcessor().groupPalaceProjectRecord(curTitle, palaceProjectAspenId);
						indexRecord(groupedWorkId);
					}catch (DataTruncation e) {
						logEntry.addNote("Record " + palaceProjectId + " " + title + " contained invalid data " + e);
					}catch (SQLException e){
						logEntry.incErrors("Error adding Palace Project title to database record " + palaceProjectId + " " + title + " " + collectionName, e);
					}
				}else if (recordUpdated || doFullReload){
					updatePalaceProjectTitleInDbStmt.setString(1, title);
					updatePalaceProjectTitleInDbStmt.setString(2, collectionName);
					updatePalaceProjectTitleInDbStmt.setLong(3, rawChecksum);
					updatePalaceProjectTitleInDbStmt.setString(4, rawResponse);
					updatePalaceProjectTitleInDbStmt.setLong(5, existingTitle.getId());
					try {
						updatePalaceProjectTitleInDbStmt.executeUpdate();

						String groupedWorkId =  getRecordGroupingProcessor().groupPalaceProjectRecord(curTitle, existingTitle.getId());
						indexRecord(groupedWorkId);
					}catch (DataTruncation e) {
						logEntry.addNote("Record " + palaceProjectId + " " + title + " contained invalid data " + e);
					}catch (SQLException e){
						logEntry.incErrors("Error updating Palace Project data in database for record " + palaceProjectId + " " + title, e);
					}
				}
			}catch (Exception e){
				logEntry.incErrors("Error updating palace project data", e);
			}
		}
		getGroupedWorkIndexer().commitChanges();
	}

	@SuppressWarnings("unused")
	private static void exportSinglePalaceProjectTitle(String singleWorkId) {
		try{
			logEntry.addNote("Doing extract of single work " + singleWorkId);
			logEntry.saveResults();

			PreparedStatement getSettingsStmt = aspenConn.prepareStatement("SELECT * from palace_project_settings");
			ResultSet getSettingsRS = getSettingsStmt.executeQuery();
			int numSettings = 0;
			while (getSettingsRS.next()) {
				numSettings++;
				palaceProjectBaseUrl = getSettingsRS.getString("apiUrl");
				String palaceProjectLibraryId = getSettingsRS.getString("libraryId");

				String url = palaceProjectBaseUrl + "/" + palaceProjectLibraryId + "/crawlable";
				HashMap<String, String> headers = new HashMap<>();
				headers.put("Accept", "application/opds+json");
				headers.put("User-Agent", "Aspen Discovery");
				WebServiceResponse response = NetworkUtils.getURL(url, logger, headers);
				if (!response.isSuccess()){
					logEntry.incErrors("Could not get titles from " + url + " " + response.getMessage());
				}else {
					JSONObject responseJSON = new JSONObject(response.getMessage());
					if (responseJSON.has("publications")) {
						JSONArray responseTitles = responseJSON.getJSONArray("publications");
						if (responseTitles != null && !responseTitles.isEmpty()) {
							//updateTitlesInDB(responseTitles, false);
							logEntry.saveResults();
						}
					}
				}
			}
			if (numSettings == 0){
				logger.error("Unable to find settings for Palace Project, please add settings to the database");
			}
		}catch (Exception e){
			logEntry.incErrors("Error exporting Palace Project data", e);
		}
	}

	private static Connection connectToDatabase(){
		Connection aspenConn = null;
		try{
			String databaseConnectionInfo = ConfigUtil.cleanIniValue(configIni.get("Database", "database_aspen_jdbc"));
			if (databaseConnectionInfo != null) {
				aspenConn = DriverManager.getConnection(databaseConnectionInfo);

				addPalaceProjectTitleToDbStmt = aspenConn.prepareStatement("INSERT INTO palace_project_title (palaceProjectId, title, collectionName, rawChecksum, rawResponse, dateFirstDetected) VALUES (?, ?, ?, ?, COMPRESS(?), ?)", PreparedStatement.RETURN_GENERATED_KEYS);
				updatePalaceProjectTitleInDbStmt = aspenConn.prepareStatement("UPDATE palace_project_title set title = ?, collectionName = ?, rawChecksum = ?, rawResponse = COMPRESS(?) WHERE id = ?");
				deletePalaceProjectTitleFromDbStmt = aspenConn.prepareStatement("DELETE FROM palace_project_title where id = ?");
			}else{
				logger.error("Aspen database connection information was not provided");
				System.exit(1);
			}
		}catch (Exception e){
			logger.error("Error connecting to Aspen database " + e);
			System.exit(1);
		}
		return aspenConn;
	}

	private static void disconnectDatabase(Connection aspenConn) {
		try{
			addPalaceProjectTitleToDbStmt.close();
			updatePalaceProjectTitleInDbStmt.close();
			deletePalaceProjectTitleFromDbStmt.close();

			aspenConn.close();
			//noinspection UnusedAssignment
			aspenConn = null;
		}catch (Exception e){
			logger.error("Error closing database ", e);
			System.exit(1);
		}
	}

	private static void createDbLogEntry(Date startTime, Connection aspenConn) {
		//Remove log entries older than 45 days
		long earliestLogToKeep = (startTime.getTime() / 1000) - (60 * 60 * 24 * 45);
		try {
			int numDeletions = aspenConn.prepareStatement("DELETE from palace_project_export_log WHERE startTime < " + earliestLogToKeep).executeUpdate();
			logger.info("Deleted " + numDeletions + " old log entries");
		} catch (SQLException e) {
			logger.error("Error deleting old log entries", e);
		}

		logEntry = new PalaceProjectExportLogEntry(aspenConn, logger);
	}

	private static void loadExistingTitles() {
		try {
			if (existingRecords == null) existingRecords = new HashMap<>();
			PreparedStatement getAllExistingPalaceProjectTitlesStmt = aspenConn.prepareStatement("SELECT id, palaceProjectId, collectionName, rawChecksum, UNCOMPRESSED_LENGTH(rawResponse) as rawResponseLength from palace_project_title");
			ResultSet allRecordsRS = getAllExistingPalaceProjectTitlesStmt.executeQuery();
			while (allRecordsRS.next()) {
				String palaceProjectId = allRecordsRS.getString("palaceProjectId");
				String collectionName = allRecordsRS.getString("collectionName");
				PalaceProjectTitle newTitle = new PalaceProjectTitle(
						allRecordsRS.getLong("id"),
						palaceProjectId,
						collectionName,
						allRecordsRS.getLong("rawChecksum"),
						allRecordsRS.getLong("rawResponseLength")
				);
				existingRecords.put(palaceProjectId+collectionName, newTitle);
			}
			allRecordsRS.close();
			//noinspection UnusedAssignment
			allRecordsRS = null;
			getAllExistingPalaceProjectTitlesStmt.close();
		} catch (SQLException e) {
			logger.error("Error loading existing titles", e);
			logEntry.addNote("Error loading existing titles" + e);
			System.exit(-1);
		}
	}

	private static void processRecordsToReload(PalaceProjectExportLogEntry logEntry) {
		try {
			PreparedStatement getRecordsToReloadStmt = aspenConn.prepareStatement("SELECT * from record_identifiers_to_reload WHERE processed = 0 and type='palace_project'", ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
			PreparedStatement markRecordToReloadAsProcessedStmt = aspenConn.prepareStatement("UPDATE record_identifiers_to_reload SET processed = 1 where id = ?");
			PreparedStatement getItemDetailsForRecordStmt = aspenConn.prepareStatement("SELECT UNCOMPRESS(rawResponse) as rawResponse from palace_project_title where id = ?", ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
			PreparedStatement getIdForPalaceProjectIdStmt = aspenConn.prepareStatement("SELECT if from palace_project_title where palaceProjectId = ?", ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
			ResultSet getRecordsToReloadRS = getRecordsToReloadStmt.executeQuery();
			int numRecordsToReloadProcessed = 0;
			while (getRecordsToReloadRS.next()){
				long recordToReloadId = getRecordsToReloadRS.getLong("id");
				String rawPalaceProjectId = getRecordsToReloadRS.getString("identifier");
				long palaceProjectId;
				if (AspenStringUtils.isNumeric(rawPalaceProjectId)) {
					palaceProjectId = Long.parseLong(rawPalaceProjectId);
				}else{
					getIdForPalaceProjectIdStmt.setString(1, rawPalaceProjectId);
					ResultSet getIdForPalaceProjectIdRS = getIdForPalaceProjectIdStmt.executeQuery();
					if (getIdForPalaceProjectIdRS.next()) {
						palaceProjectId = getIdForPalaceProjectIdRS.getLong("id");
						getIdForPalaceProjectIdRS.close();
					}else{
						logEntry.addNote("Could not get details for record to reload " + rawPalaceProjectId + " it has been deleted");
						markRecordToReloadAsProcessedStmt.setLong(1, recordToReloadId);
						markRecordToReloadAsProcessedStmt.executeUpdate();
						numRecordsToReloadProcessed++;
						getIdForPalaceProjectIdRS.close();
						continue;
					}
				}
				//Regroup the record
				getItemDetailsForRecordStmt.setLong(1, palaceProjectId);
				ResultSet getItemDetailsForRecordRS = getItemDetailsForRecordStmt.executeQuery();
				if (getItemDetailsForRecordRS.next()){
					String rawResponse = getItemDetailsForRecordRS.getString("rawResponse");
					try {
						JSONObject itemDetails = new JSONObject(rawResponse);
						String groupedWorkId =  getRecordGroupingProcessor().groupPalaceProjectRecord(itemDetails, palaceProjectId);
						//Reindex the record
						getGroupedWorkIndexer().processGroupedWork(groupedWorkId);

						markRecordToReloadAsProcessedStmt.setLong(1, recordToReloadId);
						markRecordToReloadAsProcessedStmt.executeUpdate();
						numRecordsToReloadProcessed++;
					}catch (JSONException e){
						logEntry.incErrors("Could not parse item details for record to reload " + palaceProjectId, e);
					}
				}else{
					//The record has likely been deleted
					logEntry.addNote("Could not get details for record to reload " + palaceProjectId + " it has been deleted");
					markRecordToReloadAsProcessedStmt.setLong(1, recordToReloadId);
					markRecordToReloadAsProcessedStmt.executeUpdate();
					numRecordsToReloadProcessed++;
				}
				getItemDetailsForRecordRS.close();
			}
			if (numRecordsToReloadProcessed > 0){
				logEntry.addNote("Regrouped " + numRecordsToReloadProcessed + " records marked for reprocessing");
			}
			getRecordsToReloadRS.close();
		}catch (Exception e){
			logEntry.incErrors("Error processing records to reload ", e);
		}
	}

	private static GroupedWorkIndexer getGroupedWorkIndexer() {
		if (groupedWorkIndexer == null) {
			groupedWorkIndexer = new GroupedWorkIndexer(serverName, aspenConn, configIni, false, false, logEntry, logger);
			if (!groupedWorkIndexer.isOkToIndex()) {
				logEntry.incErrors("Indexer could not be initialized properly");
				logEntry.saveResults();
				System.exit(1);
			}
		}
		return groupedWorkIndexer;
	}

	private static void indexRecord(String groupedWorkId) {
		getGroupedWorkIndexer().processGroupedWork(groupedWorkId);
	}

	private static RecordGroupingProcessor getRecordGroupingProcessor(){
		if (recordGroupingProcessorSingleton == null) {
			recordGroupingProcessorSingleton = new RecordGroupingProcessor(aspenConn, serverName, logEntry, logger);
		}
		return recordGroupingProcessorSingleton;
	}
}
