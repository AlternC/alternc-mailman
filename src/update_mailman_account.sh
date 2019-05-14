#!/bin/bash

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

# the account mailman to CREATE
mysql_query "SELECT id, uid, username, password, email FROM mailman_account WHERE mailman_action='CREATE';"|while read id uid username password email ; do

/usr/share/mailman3-web/django_create_user.py -u $username -e $email -p $password

mysql_query "UPDATE mailman_account SET password='', mailman_action='OK' WHERE id='$id';"

done

# the account to DELETE

mysql_query "SELECT id, uid, username, email FROM mailman_account WHERE mailman_action='DELETE';"|while read id uid username email ; do
	mysql_query "SELECT count(*) as cnt FROM mailman_account WHERE mailman_action='OK' and email='$email';"|while read cnt ; do		
		if [[ $cnt == 0  ]]; then
			/usr/share/mailman3-web/django_remove_user.py -e $email;
		fi
		mysql_query "DELETE FROM mailman_account WHERE id='$id';"
	done
	
done

