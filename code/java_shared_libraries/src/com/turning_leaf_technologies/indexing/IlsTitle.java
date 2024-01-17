package com.turning_leaf_technologies.indexing;

public class IlsTitle {
	private final long checksum;
	private final long dateFirstDetected;
	private final boolean isDeleted;

	public IlsTitle(long checksum, long dateFirstDetected, boolean isDeleted) {
		this.checksum = checksum;
		this.dateFirstDetected = dateFirstDetected;
		this.isDeleted = isDeleted;
	}

	public Long getChecksum() {
		return checksum;
	}

	public Long getDateFirstDetected() {
		return dateFirstDetected;
	}

    public boolean isDeleted() {
        return isDeleted;
    }
}
