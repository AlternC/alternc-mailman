# Every minute, do mailman actions
* * * * *	root	/usr/lib/alternc/update_mailman.sh
* * * * *	root	/usr/lib/alternc/update_mailman_account.sh

# Every minute, synchronise email of mailman3 account
* * * * *	root	/usr/lib/alternc/sync_mailman3_email_account.php

# Every 2 minute, remove list in db mailman if note exist in mailman3 db
2 * * * *	root	/usr/lib/alternc/check_db_mman3.php


