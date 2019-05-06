package com.turning_leaf_technologies.reindexer;

import com.turning_leaf_technologies.dates.DateUtils;
import com.turning_leaf_technologies.indexing.Scope;
import com.turning_leaf_technologies.strings.StringUtils;
import org.apache.solr.common.SolrInputDocument;

import java.util.Date;
import java.util.HashSet;

class UserListSolr {
	private final UserListIndexer userListIndexer;
	private long id;
	private HashSet<String> relatedRecordIds = new HashSet<>();
	private String author;
	private String title;
	private HashSet<String> contents = new HashSet<>(); //A list of the titles and authors for the list
	private String description;
	private long numTitles = 0;
	private long created;
	private long owningLibrary;
	private String owningLocation;
	private boolean ownerHasListPublisherRole = false;

	UserListSolr(UserListIndexer userListIndexer) {
		this.userListIndexer = userListIndexer;
	}

	SolrInputDocument getSolrDocument() {
		SolrInputDocument doc = new SolrInputDocument();
		doc.addField("id", id);
		doc.addField("recordtype", "list");

		doc.addField("alternate_ids", relatedRecordIds);

		doc.addField("title", title);
		doc.addField("title_display", title);
		
		doc.addField("title_sort", StringUtils.makeValueSortable(title));

		doc.addField("author", author);
		doc.addField("author_display", author);

		doc.addField("table_of_contents", contents);
		doc.addField("description", description);
		doc.addField("keywords", description);

		//TODO: Should we count number of views to determine popularity?
		doc.addField("popularity", Long.toString(numTitles));
		doc.addField("num_titles", numTitles);

		Date dateAdded = new Date(created * 1000);
		doc.addField("days_since_added", DateUtils.getDaysSinceAddedForDate(dateAdded));

		//Do things based on scoping
		for (Scope scope: userListIndexer.getScopes()) {
			boolean okToInclude;
			if (scope.isLibraryScope()) {
				okToInclude = (scope.getPublicListsToInclude() == 2) || //All public lists
						((scope.getPublicListsToInclude() == 1) && (scope.getLibraryId() == owningLibrary)) || //All lists for the current library
						((scope.getPublicListsToInclude() == 3) && ownerHasListPublisherRole && (scope.getLibraryId() == owningLibrary)) || //All lists for list publishers at the current library
						((scope.getPublicListsToInclude() == 4) && ownerHasListPublisherRole) //All lists for list publishers
						;
			} else {
				okToInclude = (scope.getPublicListsToInclude() == 3) || //All public lists
						((scope.getPublicListsToInclude() == 1) && (scope.getLibraryId() == owningLibrary)) || //All lists for the current library
						((scope.getPublicListsToInclude() == 2) && scope.getScopeName().equals(owningLocation)) || //All lists for the current location
						((scope.getPublicListsToInclude() == 4) && ownerHasListPublisherRole && (scope.getLibraryId() == owningLibrary)) || //All lists for list publishers at the current library
						((scope.getPublicListsToInclude() == 5) && ownerHasListPublisherRole && scope.getScopeName().equals(owningLocation)) || //All lists for list publishers the current location
						((scope.getPublicListsToInclude() == 6) && ownerHasListPublisherRole) //All lists for list publishers
						;
			}
			if (okToInclude) {
				doc.addField("local_time_since_added_" + scope.getScopeName(), DateUtils.getTimeSinceAddedForDate(dateAdded));
				doc.addField("local_days_since_added_" + scope.getScopeName(), DateUtils.getDaysSinceAddedForDate(dateAdded));
				doc.addField("format_" + scope.getScopeName(), "list");
				doc.addField("format_category_" + scope.getScopeName(), "list");
				doc.addField("scope_has_related_records", scope.getScopeName());
			}
		}

		return doc;
	}

	void setTitle(String title) {
		this.title = title;
	}

	void setDescription(String description) {
		this.description = description;
	}

	void setAuthor(String author) {
		this.author = author;
	}

	void addListTitle(String groupedWorkId, Object title, Object author) {
		relatedRecordIds.add("grouped_work:" + groupedWorkId);
		contents.add(title + " - " + author);
		numTitles++;
	}

	void setCreated(long created) {
		this.created = created;
	}

	void setId(long id) {
		this.id = id;
	}

	void setOwningLocation(String owningLocation) {
		this.owningLocation = owningLocation;
	}

	void setOwningLibrary(long owningLibrary) {
		this.owningLibrary = owningLibrary;
	}

	void setOwnerHasListPublisherRole(boolean ownerHasListPublisherRole){
		this.ownerHasListPublisherRole = ownerHasListPublisherRole;
	}

	long getNumTitles(){
		return numTitles;
	}
}
