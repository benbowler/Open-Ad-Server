DELETE FROM `#__modules` WHERE module='mod_orbitscripts_ads';
DROP TABLE IF EXISTS `#__orbitscripts_ads_params`;

CREATE TABLE `#__orbitscripts_ads_params` (
  `name` varchar(25) NOT NULL,
  `value` text,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__orbitscripts_ads_params` (`name`,`value`) 
VALUES ('channels',''), ('palettes','');