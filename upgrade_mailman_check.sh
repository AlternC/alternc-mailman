#!/bin/bash -e

# this script will look for upgrade scripts in
# /usr/share/alternc/install/upgrades-mailman/ and execute them based on the
# extension
#
# an upgrade file is considered only if its basename is a version
# number greater than the $oldvers argument

# remove version from filename by stripping the extension
strip_ext() {
    echo $1 | sed 's/\.[^.]*$//'
}

# find the version from a filename by stripping everything but the extension
get_ext() {
    echo $1 | sed 's/^.*\.\([^.]*\)$/\1/'
}


# Reading the current version in the DB.
# If the DB exist but the alternc_status table doesn't, we will initialize it below
# In that case we search where we upgrade from in /var/lib/alternc/backups/lastversion from debian.postinstall script
oldvers="`mysql --defaults-file=/etc/alternc/my.cnf --skip-column-names -e "SELECT value FROM alternc_status WHERE name='alternc-mailman_version'"||true`"
if [ -z "$oldvers" ]
then
    # no version number, we check from /var/lib/alternc
    if [ -f "/var/lib/alternc/backups/alternc-mailman-lastversion" ]
    then
	oldvers="`cat /var/lib/alternc/backups/alternc-mailman-lastversion`"
    else
	echo "found no version to upgrade from... it may be a fresh install..."
	exit 1
    fi
fi

if [ "$oldvers" = '<unknown>' ]
then
	# this is not an upgrade
	exit 0
fi

# Thanks to that, we handle alternc older than 3.1.0~b.php
mysql --defaults-file=/etc/alternc/my.cnf -e "CREATE TABLE IF NOT EXISTS alternc_status (name VARCHAR(48) NOT NULL DEFAULT '',value LONGTEXT NOT NULL,PRIMARY KEY (name),KEY name (name) ) ENGINE=MyISAM DEFAULT CHARSET=latin1;"
mysql --defaults-file=/etc/alternc/my.cnf -e "INSERT IGNORE INTO alternc_status SET name='alternc-mailman_version',value='$oldvers';"


. /etc/alternc/local.sh

# the upgrade script we are considering
extensions="*.sql *.sh *.php"
cd /usr/share/alternc/install/upgrades-mailman
for file in $( ls $extensions | sort -n ) ; do
	if [ -r $file ]; then
                # the version in the filename
		upvers=`strip_ext $file`
                # the extension
		ext=`get_ext $file`
		if dpkg --compare-versions $upvers gt $oldvers; then
		  echo "Running upgrade script $file"
                  # run the proper program to interpret the upgrade script
		  case "$ext" in
		      sql)
			  ( echo "BEGIN;"
			      cat $file
			      echo "COMMIT;"
			  ) | mysql --defaults-file=/etc/alternc/my.cnf
			  ;;
		      php)
		  	  php -q $file
			  ;;
		      sh)
		  	  bash $file
			  ;;
                      *)
			  echo "skipping $file, not recognized !"
			  ;;
		  esac
		  mysql --defaults-file=/etc/alternc/my.cnf --skip-column-names -e "UPDATE alternc_status SET value='$upvers' WHERE name='alternc-mailman_version';"
		fi
	fi
done
