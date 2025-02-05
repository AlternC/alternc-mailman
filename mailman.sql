

CREATE TABLE IF NOT EXISTS `mailman` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `list` varchar(128) NOT NULL DEFAULT '',
  `domain` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '',
  `owner` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `mailman_action` enum('OK','CREATE','DELETE','PASSWORD','GETURL','SETURL','DELETING', 'REGENERATE', 'REGENERATE-2', 'MIGRATE') NOT NULL DEFAULT 'OK',
  `mailman_result` varchar(255) NOT NULL DEFAULT '',
  `mailman_version` decimal(6,3) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mailman_action` (`mailman_action`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Mailman mailing lists';

INSERT IGNORE INTO `variable` (name, value, comment) VALUES ('mailman_url', 0,
' This is the domaine name that will be use to construct mailman\'s interface links. Set this to 0 or a "false" string to ignore and keep the default behavior (hosted domain in the URL).');
-- '

CREATE TABLE IF NOT EXISTS alternc_status (name VARCHAR(48) NOT NULL DEFAULT '',value LONGTEXT NOT NULL,PRIMARY KEY (name),KEY name (name) ) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT IGNORE INTO alternc_status SET name='alternc-mailman_version',value='2.2.sql';

