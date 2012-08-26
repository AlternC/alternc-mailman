
-- we added 2 new fields to the mailman table: 
-- password => remember temporarily the new password when mailman_action=PASSWORD
-- owner => remember temporarily the owner email of the list
-- url => will store the url of the list administrative interface
-- mailman_action => tell the /usr/lib/alternc/update_mailman.sh cron (launched as "list") that there is something 
-- to do with the list: 
-- OK = nothing, list working
-- CREATE = please create that new list with password and owner
-- DELETE = please delete that list
-- PASSWORD = please change that list's password
-- DELETING = the cron IS DELETING the list, will be deleted soon.

ALTER TABLE `mailman` 
      ADD `password` VARCHAR( 64 ) NOT NULL DEFAULT '',
      ADD `owner` VARCHAR( 255 ) NOT NULL DEFAULT '',
      ADD `url` VARCHAR( 255 ) NOT NULL DEFAULT '',
      ADD `mailman_action` ENUM( 'OK', 'CREATE', 'DELETE', 'PASSWORD', 'GETURL','SETURL', 'DELETING' ) NOT NULL DEFAULT 'OK',
      ADD `mailman_result` varchar(255) NOT NULL DEFAULT '',
      ADD INDEX ( `mailman_action` ) ;


-- we will fill the url at the first cron run ;) 
UPDATE mailman SET mailman_action='GETURL';

