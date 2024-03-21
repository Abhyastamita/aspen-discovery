#!/bin/bash

for USER in mysql www-data solr;do
        sudo usermod -a -G $LINUX_GROUP_ID $USER
done
if [ ! -d /mnt/_usr_local_aspen-discovery_sites_${SITE_sitename} ] && [ $COMPOSE_Apache == "on" ] ; then
	#First execution

	if [ ! -z "$COMPOSE_RootPwd" ] ; then
		#Assign permissions to $DBUSER over $ASPEN_DBName
		mysql -u$COMPOSE_DBRoot -p$COMPOSE_RootPwd -h$ASPEN_DBHost -P$ASPEN_DBPort -e "create user '$ASPEN_DBUser'@'%' identified by '$ASPEN_DBPwd'; grant all on $ASPEN_DBName.* to '$ASPEN_DBUser'@'%'; flush privileges;"
	fi

  #Prepare createsite template
  cd /usr/local/aspen-discovery/install

  crudini --set createSiteTemplateVars.ini  Site sitename \$SITE_sitename
  crudini --set createSiteTemplateVars.ini  Site operatingSystem \$SITE_operatingSystem
  crudini --set createSiteTemplateVars.ini  Site library \$SITE_library
  crudini --set createSiteTemplateVars.ini  Site title \$SITE_title
  crudini --set createSiteTemplateVars.ini  Site url \$SITE_url
  crudini --set createSiteTemplateVars.ini  Site siteOnWindows \$SITE_siteOnWindows
  crudini --set createSiteTemplateVars.ini  Site solrHost \$SITE_solrHost
  crudini --set createSiteTemplateVars.ini  Site solrPort \$SITE_solrPort
  crudini --set createSiteTemplateVars.ini  Site ils \$SITE_ils
  crudini --set createSiteTemplateVars.ini  Site timezone \$SITE_timezone

  crudini --set createSiteTemplateVars.ini  Aspen DBHost \$ASPEN_DBHost
  crudini --set createSiteTemplateVars.ini  Aspen DBPort \$ASPEN_DBPort
  crudini --set createSiteTemplateVars.ini  Aspen DBName \$ASPEN_DBName
  crudini --set createSiteTemplateVars.ini  Aspen DBUser \$ASPEN_DBUser
  crudini --set createSiteTemplateVars.ini  Aspen DBPwd \$ASPEN_DBPwd
  crudini --set createSiteTemplateVars.ini  Aspen aspenAdminPwd \$ASPEN_aspenAdminPwd

  crudini --set createSiteTemplateVars.ini  ILS ilsDriver \$ILS_ilsDriver
  crudini --set createSiteTemplateVars.ini  ILS ilsUrl \$ILS_ilsUrl
  crudini --set createSiteTemplateVars.ini  ILS staffUrl \$ILS_staffUrl

  crudini --set createSiteTemplateVars.ini  Koha DBHost \$KOHA_DBHost
  crudini --set createSiteTemplateVars.ini  Koha DBName \$KOHA_DBName
  crudini --set createSiteTemplateVars.ini  Koha DBUser \$KOHA_DBUser
  crudini --set createSiteTemplateVars.ini  Koha DBPwd \$KOHA_DBPwd
  crudini --set createSiteTemplateVars.ini  Koha DBPort \$KOHA_DBPort
  crudini --set createSiteTemplateVars.ini  Koha DBTimezone \$KOHA_Timezone
  crudini --set createSiteTemplateVars.ini  Koha ClientId \$KOHA_ClientId
  crudini --set createSiteTemplateVars.ini  Koha ClientSecret \$KOHA_ClientSecret
  envsubst < createSiteTemplateVars.ini > createSiteTemplate.ini

	#Create new site
	php createSite.php createSiteTemplate.ini

	#Delete apache's default site
	unlink /etc/apache2/sites-enabled/000-default.conf
	unlink /etc/apache2/sites-enabled/httpd-$SITE_sitename.conf
	cp /etc/apache2/sites-available/httpd-$SITE_sitename.conf  /etc/apache2/sites-enabled/httpd-$SITE_sitename.conf

	#Change the priority (for Aspen sign in purposes)
	mysql -u$ASPEN_DBUser -p$ASPEN_DBPwd -h$ASPEN_DBHost -P$ASPEN_DBPort $ASPEN_DBName -e "update account_profiles set weight=0 where name='admin'; update account_profiles set weight=1 where name='ils';"

	#Copy data within a persistent volume
	for i in ${COMPOSE_Dirs[@]}; do
		dir=$(echo $i | sed 's/\//_/g'); 
		rsync -al $i/ /mnt/$dir; 
	done

fi

#Create symbolic links of persistent volumes
for i in ${COMPOSE_Dirs[@]}; do
	dir=$(echo $i | sed 's/\//_/g'); 
	mv $i $i-back; 
	ln -s /mnt/$dir $i; 
done

#Wait for mysql responses
while ! nc -z $ASPEN_DBHost $ASPEN_DBPort; do sleep 3; done

#Turn on apache
if [ $COMPOSE_Apache == "on" ]; then
	mkdir -p /var/log/aspen-discovery/$SITE_sitename
	service apache2 start 
fi

#Turn on Cron
if [ $COMPOSE_Cron == "on" ]; then
	service cron start 
	php /usr/local/aspen-discovery/code/web/cron/checkBackgroundProcesses.php $SITE_sitename &
fi

#Assign correct owners to directories within Aspen
chown -R www-data:aspen_apache /usr/local/aspen-discovery/code/web
chown -R www-data:aspen /data/aspen-discovery/$SITE_sitename/

#Assign permissions to Aspen 
chmod -R 777 /usr/local/aspen-discovery/code/web
chmod -R 777 /data/aspen-discovery/$SITE_sitename/

#Infinite loop
/bin/bash -c "trap : TERM INT; sleep infinity & wait"

