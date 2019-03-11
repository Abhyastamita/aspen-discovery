package com.turning_leaf_technologies.sierra;

import com.turning_leaf_technologies.strings.StringUtils;
import org.apache.logging.log4j.Logger;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;

class SierraExportFieldMapping {
    private String bcode3DestinationField;
    private char bcode3DestinationSubfield;
    private String callNumberExportFieldTag;
    private char callNumberPrestampExportSubfield;
    private char callNumberExportSubfield;
    private char callNumberCutterExportSubfield;
    private char callNumberPoststampExportSubfield;
    private String volumeExportFieldTag;
    private String urlExportFieldTag;
    private String eContentExportFieldTag;

    String getBcode3DestinationField() {
        return bcode3DestinationField;
    }

    private void setBcode3DestinationField(String bcode3DestinationField) {
        this.bcode3DestinationField = bcode3DestinationField;
    }

    char getBcode3DestinationSubfield() {
        return bcode3DestinationSubfield;
    }

    private void setBcode3DestinationSubfield(char bcode3DestinationSubfield) {
        this.bcode3DestinationSubfield = bcode3DestinationSubfield;
    }

    String getCallNumberExportFieldTag() {
        return callNumberExportFieldTag;
    }

    private void setCallNumberExportFieldTag(String callNumberExportFieldTag) {
        this.callNumberExportFieldTag = callNumberExportFieldTag;
    }

    char getCallNumberPrestampExportSubfield() {
        return callNumberPrestampExportSubfield;
    }

    private void setCallNumberPrestampExportSubfield(char callNumberPrestampExportSubfield) {
        this.callNumberPrestampExportSubfield = callNumberPrestampExportSubfield;
    }

    char getCallNumberExportSubfield() {
        return callNumberExportSubfield;
    }

    private void setCallNumberExportSubfield(char callNumberExportSubfield) {
        this.callNumberExportSubfield = callNumberExportSubfield;
    }

    char getCallNumberCutterExportSubfield() {
        return callNumberCutterExportSubfield;
    }

    private void setCallNumberCutterExportSubfield(char callNumberCutterExportSubfield) {
        this.callNumberCutterExportSubfield = callNumberCutterExportSubfield;
    }

    char getCallNumberPoststampExportSubfield() {
        return callNumberPoststampExportSubfield;
    }

    private void setCallNumberPoststampExportSubfield(char callNumberPoststampExportSubfield) {
        this.callNumberPoststampExportSubfield = callNumberPoststampExportSubfield;
    }

    String getVolumeExportFieldTag() {
        return volumeExportFieldTag;
    }

    private void setVolumeExportFieldTag(String volumeExportFieldTag) {
        this.volumeExportFieldTag = volumeExportFieldTag;
    }

    String getUrlExportFieldTag() {
        return urlExportFieldTag;
    }

    private void setUrlExportFieldTag(String urlExportFieldTag) {
        this.urlExportFieldTag = urlExportFieldTag;
    }

    String getEContentExportFieldTag() {
        return eContentExportFieldTag;
    }

    private void setEContentExportFieldTag(String eContentExportFieldTag) {
        this.eContentExportFieldTag = eContentExportFieldTag;
    }

    static SierraExportFieldMapping loadSierraFieldMappings(Connection dbConn, long profileId, Logger logger) {
        //Get the Indexing Profile from the database
        SierraExportFieldMapping sierraFieldMapping = new SierraExportFieldMapping();
        try {
            PreparedStatement getSierraFieldMappingsStmt = dbConn.prepareStatement("SELECT * FROM sierra_export_field_mapping where indexingProfileId =" + profileId);
            ResultSet getSierraFieldMappingsRS = getSierraFieldMappingsStmt.executeQuery();
            if (getSierraFieldMappingsRS.next()){
                sierraFieldMapping.setBcode3DestinationField(getSierraFieldMappingsRS.getString("bcode3DestinationField"));
                sierraFieldMapping.setBcode3DestinationSubfield(StringUtils.convertStringToChar(getSierraFieldMappingsRS.getString("bcode3DestinationSubfield")));
                sierraFieldMapping.setCallNumberExportFieldTag(getSierraFieldMappingsRS.getString("callNumberExportFieldTag"));
                sierraFieldMapping.setCallNumberPrestampExportSubfield(StringUtils.convertStringToChar(getSierraFieldMappingsRS.getString("callNumberPrestampExportSubfield")));
                sierraFieldMapping.setCallNumberExportSubfield(StringUtils.convertStringToChar(getSierraFieldMappingsRS.getString("callNumberExportSubfield")));
                sierraFieldMapping.setCallNumberCutterExportSubfield(StringUtils.convertStringToChar(getSierraFieldMappingsRS.getString("callNumberCutterExportSubfield")));
                sierraFieldMapping.setCallNumberPoststampExportSubfield(StringUtils.convertStringToChar(getSierraFieldMappingsRS.getString("callNumberPoststampExportSubfield")));
                sierraFieldMapping.setVolumeExportFieldTag(getSierraFieldMappingsRS.getString("volumeExportFieldTag"));
                sierraFieldMapping.setUrlExportFieldTag(getSierraFieldMappingsRS.getString("urlExportFieldTag"));
                sierraFieldMapping.setEContentExportFieldTag(getSierraFieldMappingsRS.getString("eContentExportFieldTag"));

                getSierraFieldMappingsRS.close();
            }
            getSierraFieldMappingsStmt.close();

        }catch (Exception e){
            logger.error("Error reading index profile for CarlX", e);
        }
        return sierraFieldMapping;
    }
}
