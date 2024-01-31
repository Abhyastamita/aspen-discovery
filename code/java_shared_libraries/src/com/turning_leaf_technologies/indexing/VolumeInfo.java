package com.turning_leaf_technologies.indexing;

import java.util.ArrayList;

public class VolumeInfo {
	public String bibNumber;
	public String volume;
	public String volumeIdentifier;
	public int displayOrder;
	public ArrayList<String> relatedItems = new ArrayList<>();

	public String getRelatedItemsAsString() {
		return String.join("|", relatedItems);
	}
}
