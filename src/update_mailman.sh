#!/bin/bash

# This script look in the database wich mailman list should be CREATED / DELETED / PASSWORDED

# Source some configuration file
for CONFIG_FILE in \
      /etc/alternc/local.sh \
      /usr/lib/alternc/functions.sh
  do
    if [ ! -r "$CONFIG_FILE" ]; then
        echo "Can't access $CONFIG_FILE."
        exit 1
    fi
    . "$CONFIG_FILE"
done

LOCK_FILE="/var/run/alternc/update_mailman"

#FIXME: this var should be define by local.sh
ALTERNC_MAIL_LOC="/var/alternc/mail"

# Somes check before start operations
if [ `id -u` -ne 0 ]; then
    log_error "must be launched as root"
elif [ -f "$LOCK_FILE" ]; then
    process=$(ps f -p `cat "$LOCK_FILE"|tail -1`|tail -1|awk '{print $NF;}')
    if [ "$(basename $process)" = "$(basename "$0")" ] ; then
	log_error "last cron unfinished or stale lock file ($LOCK_FILE)."
    else
	rm "$LOCK_FILE"
    fi
fi

# If there is ionice, add it to the command line
ionice=""
ionice > /dev/null && ionice="ionice -c 3 "

# We lock the application
echo $$ > "$LOCK_FILE"

# List the lists to CREATE
mysql_query "SELECT id, name, domain, owner, password FROM mailman WHERE mailman_action='CREATE';"|while read id name domain owner password ; do
    if [ -d "/var/lib/mailman/lists/$name" ]
    then
	mysql_query "UPDATE mailman SET password='', mailman_result='This list already exist', mailman_action='OK' WHERE id='$id';"
    else
	# Create the list : 
	su - list -c "/usr/lib/mailman/bin/newlist -q \"$name\" \"$owner\" \"$password\""
	if [ "$?" -eq "0" ]
	then
	    mysql_query "UPDATE mailman SET password='', mailman_result='', mailman_action='OK' WHERE id='$id';"
	else
	    mysql_query "UPDATE mailman SET password='', mailman_result='A fatal error happened when creating the list', mailman_action='OK' WHERE id='$id';"
	fi
    fi
done

# List the lists to DELETE
mysql_query "SELECT id, list, domain FROM mailman WHERE mailman_action='DELETE';"|while read id list domain ; do
    if [ ! -d "/var/lib/mailman/lists/$list" ]
    then
	mysql_query "UPDATE mailman SET mailman_result='This list does not exist', mailman_action='OK' WHERE id='$id';"
    else
	# Delete the list : 
	mysql_query "UPDATE mailman SET mailman_action='DELETING' WHERE id='$id';"
	su - list -c "/usr/lib/mailman/bin/rmlist \"$list\""
	if [ "$?" -eq "0" ]
	then
	    # Now delete the archives too ...
	    su - list -c "/usr/lib/mailman/bin/rmlist -a \"$list\""
	    mysql_query "DELETE FROM mailman WHERE id='$id';"
	else
	    mysql_query "UPDATE mailman SET mailman_result='A fatal error happened when deleting the list', mailman_action='OK' WHERE id='$id';"
	fi
    fi
done


# List the lists to PASSWORD
mysql_query "SELECT id, list, domain, password FROM mailman WHERE mailman_action='PASSWORD';"|while read id list domain password ; do
    if [ ! -d "/var/lib/mailman/lists/$list" ]
    then
	mysql_query "UPDATE mailman SET mailman_result='This list does not exist', mailman_action='OK', password='' WHERE id='$id';"
    else
	# Password the list : 
	su - list -c "/usr/lib/mailman/bin/change_pw -l \"$list\" -p \"$password\""
	if [ "$?" -eq "0" ]
	then
	    mysql_query "UPDATE mailman SET password='', mailman_result='', mailman_action='OK' WHERE id='$id';"
	else
	    mysql_query "UPDATE mailman SET password='', mailman_result='A fatal error happened when changing the list password', mailman_action='OK' WHERE id='$id';"
	fi
    fi
done


# List the lists to GETURL
mysql_query "SELECT id, list, domain FROM mailman WHERE mailman_action='GETURL';"|while read id list domain ; do
    if [ ! -d "/var/lib/mailman/lists/$list" ]
    then
	mysql_query "UPDATE mailman SET mailman_result='This list does not exist', mailman_action='OK' WHERE id='$id';"
    else
	# Get the list's URL : 
	URL=`su -p - list -c "/usr/lib/mailman/bin/withlist -q -l -r get_url_alternc \"$list\" " 2>/dev/null `
	if [ "$?" -eq "0" ]
	then
	    mysql_query "UPDATE mailman SET mailman_result='', mailman_action='OK', url='$URL' WHERE id='$id';"
	else
	    mysql_query "UPDATE mailman SET mailman_result='A fatal error happened when getting the list url', mailman_action='OK' WHERE id='$id';"
	fi
    fi
done


# List the lists to SETURL
mysql_query "SELECT id, list, domain, url FROM mailman WHERE mailman_action='SETURL';"|while read id list domain url ; do
    if [ ! -d "/var/lib/mailman/lists/$list" ]
    then
	mysql_query "UPDATE mailman SET mailman_result='This list does not exist', mailman_action='OK' WHERE id='$id';"
    else
	# SetURL the list : 
	su - list -c "/usr/lib/mailman/bin/withlist -q -l -r set_url_alternc \"$list\" \"$url\""
	if [ "$?" -eq "0" ]
	then
	    mysql_query "UPDATE mailman SET mailman_result='', mailman_action='OK' WHERE id='$id';"
	else
	    mysql_query "UPDATE mailman SET mailman_result='A fatal error happened when changing the list password', mailman_action='OK' WHERE id='$id';"
	fi
    fi
done


# Delete the lock
rm -f "$LOCK_FILE"
