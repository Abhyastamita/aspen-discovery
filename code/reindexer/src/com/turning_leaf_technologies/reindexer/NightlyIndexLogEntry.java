package com.turning_leaf_technologies.reindexer;

import com.turning_leaf_technologies.logging.BaseIndexingLogEntry;
import com.turning_leaf_technologies.util.SystemUtils;
import org.apache.logging.log4j.Logger;

import java.sql.*;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;

public class NightlyIndexLogEntry implements BaseIndexingLogEntry {
	private Long logEntryId = null;
	private final Date startTime;
	private Date endTime;
	private final ArrayList<String> notes = new ArrayList<>();
	private final Logger logger;
	private int numWorksProcessed;
	private int numErrors;
	private int numInvalidRecords = 0;

	private static PreparedStatement insertLogEntry;
	private static PreparedStatement updateLogEntry;

	public NightlyIndexLogEntry(Connection dbConn, Logger logger){
		this.logger = logger;
		this.startTime = new Date();
		try {
			insertLogEntry = dbConn.prepareStatement("INSERT into reindex_log (startTime) VALUES (?)", PreparedStatement.RETURN_GENERATED_KEYS);
			updateLogEntry = dbConn.prepareStatement("UPDATE reindex_log SET lastUpdate = ?, endTime = ?, notes = ?, numWorksProcessed = ?, numErrors = ?, numInvalidRecords = ? WHERE id = ?", PreparedStatement.RETURN_GENERATED_KEYS);
		} catch (SQLException e) {
			logger.error("Error creating prepared statements to update log", e);
		}
		this.saveResults();
	}

	private final SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
	@Override
	//Synchronized to prevent concurrent modification of the notes ArrayList
	public synchronized void addNote(String note) {
		Date date = new Date();
		this.notes.add(dateFormat.format(date) + " - " + note);
	}

	private String getNotesHtml() {
		StringBuilder notesText = new StringBuilder("<ol class='cronNotes'>");
		for (String curNote : notes){
			String cleanedNote = curNote;
			cleanedNote = cleanedNote.replaceAll("<pre>", "<code>");
			cleanedNote = cleanedNote.replaceAll("</pre>", "</code>");
			//Replace multiple line breaks
			cleanedNote = cleanedNote.replaceAll("(?:<br?>\\s*)+", "<br/>");
			cleanedNote = cleanedNote.replaceAll("<meta.*?>", "");
			cleanedNote = cleanedNote.replaceAll("<title>.*?</title>", "");
			notesText.append("<li>").append(cleanedNote).append("</li>");
		}
		notesText.append("</ol>");
		String returnText = notesText.toString();
		if (returnText.length() > 25000){
			returnText = returnText.substring(0, 25000) + " more data was truncated";
		}
		return returnText;
	}

	@Override
	public void setFinished() {
		this.endTime = new Date();
		this.addNote("Finished Reindex");
		this.saveResults();
	}

	public void incErrors(String note) {
		this.addNote("ERROR: " + note);
		numErrors++;
		this.saveResults();
		logger.error(note);
	}

	public void incErrors(String note, Exception e){
		this.addNote("ERROR: " + note + " " + e.toString());
		numErrors++;
		this.saveResults();
		logger.error(note, e);
	}

	void incNumWorksProcessed(){
		numWorksProcessed++;
		if (numWorksProcessed % 5000 == 0){
			this.saveResults();
		}
	}

	@Override
	public boolean saveResults() {
		try {
			if (logEntryId == null){
				insertLogEntry.setLong(1, startTime.getTime() / 1000);
				insertLogEntry.executeUpdate();
				ResultSet generatedKeys = insertLogEntry.getGeneratedKeys();
				if (generatedKeys.next()){
					logEntryId = generatedKeys.getLong(1);
				}
			}else{
				int curCol = 0;
				updateLogEntry.setLong(++curCol, new Date().getTime() / 1000);
				if (endTime == null){
					updateLogEntry.setNull(++curCol, Types.INTEGER);
				}else{
					updateLogEntry.setLong(++curCol, endTime.getTime() / 1000);
				}
				updateLogEntry.setString(++curCol, getNotesHtml());
				updateLogEntry.setInt(++curCol, numWorksProcessed);
				updateLogEntry.setInt(++curCol, numErrors);
				updateLogEntry.setInt(++curCol, numInvalidRecords);
				updateLogEntry.setLong(++curCol, logEntryId);
				updateLogEntry.executeUpdate();
			}
			SystemUtils.printMemoryStats(logger);
			return true;
		} catch (SQLException e) {
			logger.error("Error saving nightly indexing log", e);
			return false;
		}
	}

	public void incInvalidRecords(String invalidRecordId){
		this.numInvalidRecords++;
		this.addNote("Invalid Record found: " + invalidRecordId);
	}
}
