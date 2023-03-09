#!/bin/sh
# Copies needed solr files to the server specified as a command line argument
# This script is intended to be run as root
if [ -z "$1" ]
  then
    echo "Please provide the server name to update as the first argument."
fi
echo "Updating $1"

cp -r solr7 /data/aspen-discovery/$1
chown -R solr:aspen /data/aspen-discovery/$1/solr7

su -c "/usr/local/aspen-discovery/sites/$1/$1.sh restart" solr
