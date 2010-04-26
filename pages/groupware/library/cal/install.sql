


-- TO INSTALL THE DATABASE TABLES, SIMPLY RUN THIS ON THE COMMAND LINE:
-- mysql -u root -p thenameofthedatabase < install.sql
-- you can also copy and past this into something like phpmyadmin or whatever.


-- 
-- Table structure for table `cal_accounts`
-- 
CREATE TABLE `cal_accounts` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(50) default NULL,
  `pass` varchar(32) default NULL,
  `first_name` varchar(50) NOT NULL default '',
  `last_name` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user` (`user`),
  KEY `user_2` (`user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- 
-- Table structure for table `cal_eventtypes`
-- 
CREATE TABLE `cal_eventtypes` (
  `id` int(11) NOT NULL auto_increment,
  `typename` varchar(100) NOT NULL default '',
  `typedesc` text NOT NULL default '',
  `typecolor` varchar(6) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- 
-- Dumping data for table `cal_eventtypes`
-- 
INSERT INTO `cal_eventtypes` (`typename`, `typedesc`, `typecolor`) VALUES ('Birthday', 'Someone''s Birthday', 'F1EA74');
INSERT INTO `cal_eventtypes` (`typename`, `typedesc`, `typecolor`) VALUES ('Important', 'Something Important or Critical', 'FFAAAA');
INSERT INTO `cal_eventtypes` (`typename`, `typedesc`, `typecolor`) VALUES ('Boring', 'Boring Everyday Stuff', '999999');
INSERT INTO `cal_eventtypes` (`typename`, `typedesc`, `typecolor`) VALUES ('Holiday', 'A Holiday', 'A4CAE6');


-- 
-- Table structure for table `cal_events`
-- 
CREATE TABLE `cal_events` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) default NULL,
  `user_id` int(11) NOT NULL default '0',
  `mod_id` int(11) default NULL,
  `mod_username` varchar(50) default NULL,
  `mod_stamp` datetime default NULL,
  `stamp` datetime default NULL,
  `duration` datetime default NULL,
  `eventtype` int(4) default NULL,
  `subject` varchar(255) default NULL,
  `description` TEXT NULL DEFAULT NULL,
  `alias` varchar(20) default NULL,
  `private` char(1) NOT NULL default '0',
  `repeat_end` date default NULL,
  `repeat_num` mediumint(9) NOT NULL default '0',
  `repeat_d` smallint(6) NOT NULL default '0',
  `repeat_m` smallint(6) NOT NULL default '0',
  `repeat_y` smallint(6) NOT NULL default '0',
  `repeat_h` smallint(6) NOT NULL default '0',
  `type_id` int(11) NOT NULL default '0',
  `special_id` int(11) NOT NULL default '0',
  `deleted` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `subject` (`subject`,`description`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- 
-- Table structure for table `cal_options`
-- 
CREATE TABLE `cal_options` (
  `opname` varchar(30) NOT NULL default '',
  `opvalue` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`opname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- 
-- Table structure for table `cal_permissions`
-- 
CREATE TABLE `cal_permissions` (
  `user_id` int(11) default NULL,
  `pname` varchar(30) NOT NULL default '',
  `pvalue` char(1) NOT NULL default 'n',
  PRIMARY KEY  (`user_id`,`pname`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


