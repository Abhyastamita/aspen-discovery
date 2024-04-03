package com.turning_leaf_technologies.reindexer;

import org.apache.logging.log4j.Logger;
import org.marc4j.marc.DataField;
import org.marc4j.marc.Subfield;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.HashMap;

public class EvergreenRecordProcessor extends IlsRecordProcessor {
	HashMap<String, String> barcodeCreatedByDates = new HashMap<>();

	private PreparedStatement getVolumesForBibStmt;

	EvergreenRecordProcessor(GroupedWorkIndexer indexer, String curType, Connection dbConn, ResultSet indexingProfileRS, Logger logger, boolean fullReindex) {
		super(indexer, curType, dbConn, indexingProfileRS, logger, fullReindex);
		this.suppressRecordsWithNoCollection = false;
		loadSupplementalFiles();
	}

	private void loadSupplementalFiles() {
		File supplementalDirectory = new File(marcPath + "/../supplemental");
		if (supplementalDirectory.exists()){
			try {
				File barcodeActiveDatesFile = new File(marcPath + "/../supplemental/barcode_active_dates.csv");
				if (barcodeActiveDatesFile.exists()){
					BufferedReader barcodeActiveDatesReader = new BufferedReader(new FileReader(marcPath + "/../supplemental/barcode_active_dates.csv"));
					String curValuesStr = barcodeActiveDatesReader.readLine();
					while (curValuesStr != null){
						String[] curValues = curValuesStr.split("\\|");
						String barcode = curValues[0];
						if (curValues.length >= 2){
							String date = curValues[1].trim();
							if (date.length() > 0){
								barcodeCreatedByDates.put(barcode, date);
							}
						}
						curValuesStr = barcodeActiveDatesReader.readLine();
					}
					barcodeActiveDatesReader.close();
				}else{
					indexer.getLogEntry().incErrors("Error barcode_active_dates.csv did not exist within " + marcPath);
				}
			}catch (IOException e){
				indexer.getLogEntry().incErrors("Error reading barcode active dates", e);
			}
		}else{
			indexer.getLogEntry().addNote("Supplemental directory did not exist");
		}
	}

	@Override
	protected boolean isItemAvailable(ItemInfo itemInfo, String displayStatus, String groupedStatus) {
		return itemInfo.getStatusCode().equals("Available") || groupedStatus.equals("On Shelf") || (settings.getTreatLibraryUseOnlyGroupedStatusesAsAvailable() && groupedStatus.equals("Library Use Only"));
	}

	private SimpleDateFormat createdByFormatter = new SimpleDateFormat("yyyy-MM-dd");
	protected void loadDateAdded(String recordIdentifier, DataField itemField, ItemInfo itemInfo) {
		Subfield itemBarcodeSubfield = itemField.getSubfield(settings.getBarcodeSubfield());
		if (itemBarcodeSubfield != null){
			String barcode = itemBarcodeSubfield.getData();
			if (barcodeCreatedByDates.containsKey(barcode)){
				String createdBy = barcodeCreatedByDates.get(barcode);
				if (createdBy.contains(" ")){
					createdBy = createdBy.substring(0, createdBy.indexOf(' '));
				}
				try {
					Date createdByDate = createdByFormatter.parse(createdBy);
					itemInfo.setDateAdded(createdByDate);
				}catch (ParseException e2){
					indexer.getLogEntry().addNote("Error processing date added for record identifier " + recordIdentifier + " profile " + profileType + " " + e2);
				}
			}
		}
	}

	protected boolean isItemHoldableUnscoped(ItemInfo itemInfo){
		//Koha uses subfield 7 to determine if a record is holdable or not.
		Subfield subfieldX = itemInfo.getMarcField().getSubfield('x');
		if (subfieldX != null) {
			if (subfieldX.getData() != null && subfieldX.getData().equalsIgnoreCase("unholdable")) {
				return false;
			}
		}
		return super.isItemHoldableUnscoped(itemInfo);
	}
}
