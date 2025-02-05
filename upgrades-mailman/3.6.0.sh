#!/bin/sh
VERSION=3.6.0

( echo "BEGIN;"
  echo "ALTER TABLE mailman ADD mailman_version decimal(6,3) NOT NULL DEFAULT 0;"
  echo "ALTER TABLE mailman MODIFY mailman_action ENUM( 'OK', 'CREATE', 'DELETE', 'PASSWORD', 'GETURL','SETURL', 'DELETING','REGENERATE','REGENERATE-2','MIGRATE' ) NOT NULL DEFAULT 'OK';"
  echo "UPDATE alternc_status SET value='$VERSION' WHERE name='alternc-mailman_version';"
  echo "COMMIT;"
) | mysql --defaults-file=/etc/alternc/my.cnf


lists=$(echo "select list,domain from mailman;" | mariadb alternc | awk '{print $1 "@" $2}')

for list in $lists
do
    local_part=$(echo $list | cut -f1 -d@)
    domain=$(echo $list | cut -f2 -d@)
    test -d /var/lib/mailman/lists/$local_part && echo "UPDATE mailman SET mailman_version=2 where list='$local_part' and domain='$domain';" | mariadb alternc
    test -d /var/lib/mailman/lists/$local_part-$domain && echo "UPDATE mailman SET mailman_version=2 where list='$local_part' and domain='$domain';" | mariadb alternc
    test -d /var/lib/mailman3/lists/$list@$domain && echo "UPDATE mailman SET mailman_version=3 where list='$local_part' and domain='$domain';" | mariadb alternc
done

exit 0
