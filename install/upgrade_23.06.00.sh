#!/bin/sh
if [ -z "$1" ]
  then
    echo "Please provide the server name to update as the first argument."
    exit 1
fi

php /usr/local/aspen-discovery/install/updateCron_23_06.php $1