
CREATE TABLE IF NOT EXISTS `mailman` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(10) unsigned NOT NULL default '0',
  `list` varchar(128) NOT NULL default '',
  `domain` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Mailman mailing lists';

INSERT IGNORE INTO `variable` (name, value, comment) VALUES ('mailman_url', 0,
' This is the domaine name that will be use to construct mailman\'s interface links. Set this to 0 or a "false" string to ignore and keep the default behavior (hosted domain in the URL).');
