/*
SQLyog Enterprise - MySQL GUI v8.02 RC
MySQL - 5.0.70 : Database - Orbit_AdServer_Lite
*********************************************************************
*/
/*Table structure for table `ad_types` */

DROP TABLE IF EXISTS `ad_types`;

CREATE TABLE `ad_types` (
  `id_ad_type` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id_ad_type`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `additional_field_restrictions` */

DROP TABLE IF EXISTS `additional_field_restrictions`;

CREATE TABLE `additional_field_restrictions` (
  `id_additional_field` int(10) unsigned NOT NULL,
  `value` varchar(200) NOT NULL,
  `value_order` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`id_additional_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `additional_fields` */

DROP TABLE IF EXISTS `additional_fields`;

CREATE TABLE `additional_fields` (
  `id_additional_field` int(10) unsigned NOT NULL auto_increment,
  `name` char(50) NOT NULL,
  `type` enum('int','string','datetime','bool') NOT NULL,
  `default_value` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `field_order` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`id_additional_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `admins` */

DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `id_entity_admin` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_entity_admin`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `ads` */

DROP TABLE IF EXISTS `ads`;

CREATE TABLE `ads` (
  `id_ad` int(10) unsigned NOT NULL auto_increment,
  `id_group` int(10) unsigned NOT NULL,
  `id_ad_type` int(10) unsigned NOT NULL,
  `status` enum('active','paused','blocked','deleted') NOT NULL default 'active',
  `bid` decimal(16,4) unsigned default NULL,
  `title` char(50) NOT NULL,
  `description` char(100) NOT NULL,
  `description2` char(100) NOT NULL,
  `display_url` char(50) NOT NULL,
  `click_url` char(100) NOT NULL,
  `impression_start` datetime NOT NULL,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `protocol` enum('http','https') NOT NULL default 'http',
  PRIMARY KEY  (`id_ad`),
  KEY `id_group` (`id_group`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

/*Table structure for table `ads_additional_fields` */

DROP TABLE IF EXISTS `ads_additional_fields`;

CREATE TABLE `ads_additional_fields` (
  `id_ad` int(10) unsigned NOT NULL,
  `id_additional_field` int(10) unsigned NOT NULL,
  `value` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_ad`,`id_additional_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `advertisers` */

DROP TABLE IF EXISTS `advertisers`;

CREATE TABLE `advertisers` (
  `id_entity_advertiser` int(10) unsigned NOT NULL,
  `current_ballance` decimal(16,4) NOT NULL default '0.0000',
  `id_category` int(10) unsigned default NULL,
  `description` text,
  `longitude` double default NULL,
  `latitude` double default NULL,
  PRIMARY KEY  (`id_entity_advertiser`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `browsers` */

DROP TABLE IF EXISTS `browsers`;

CREATE TABLE `browsers` (
  `name` varchar(20) NOT NULL default '',
  `title` varchar(50) default NULL,
  `position` int(10) unsigned NOT NULL,
  `regexp` varchar(255) NOT NULL default '',
  `banned` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `campaign_clicks` */

DROP TABLE IF EXISTS `campaign_clicks`;

CREATE TABLE `campaign_clicks` (
  `id_campaign` int(10) unsigned NOT NULL,
  `ip_address` int(10) unsigned NOT NULL,
  `datetime` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_campaign`,`ip_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `campaign_countries` */

DROP TABLE IF EXISTS `campaign_countries`;

CREATE TABLE `campaign_countries` (
  `id_campaign` int(10) unsigned NOT NULL,
  `country` char(2) NOT NULL,
  PRIMARY KEY  (`id_campaign`,`country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `campaign_types` */

DROP TABLE IF EXISTS `campaign_types`;

CREATE TABLE `campaign_types` (
  `campaign_type` varchar(16) NOT NULL,
  `campaign_name` varchar(32) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`campaign_type`),
  UNIQUE KEY `campaign_type` (`campaign_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `campaigns` */

DROP TABLE IF EXISTS `campaigns`;

CREATE TABLE `campaigns` (
  `id_campaign` int(10) unsigned NOT NULL auto_increment,
  `id_entity_advertiser` int(10) unsigned NOT NULL,
  `id_campaign_type` enum('cpm_flatrate','cpc') NOT NULL default 'cpm_flatrate',
  `name` char(30) NOT NULL,
  `status` enum('active','paused','finished','deleted') NOT NULL default 'active',
  `frequency_coup` int(11) default NULL,
  `frequency_coup_term` enum('none','day','week','month','year') default NULL,
  `frequency_coup_current` int(11) default NULL,
  `budget` decimal(16,4) default NULL,
  `budget_term` enum('none','day','week','month','year') default NULL,
  `budget_current` decimal(16,4) default NULL,
  `id_schedule` int(10) unsigned default NULL,
  `id_targeting_group` int(10) unsigned default NULL,
  `start_date_time` datetime default NULL,
  `end_date_time` datetime default NULL,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `id_program_type` int(10) unsigned NOT NULL,
  `targeting_type` enum('basic','advanced') NOT NULL default 'basic',
  PRIMARY KEY  (`id_campaign`),
  KEY `advertiser` (`id_entity_advertiser`),
  KEY `status` (`status`),
  KEY `id_campaign_type` (`id_campaign_type`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id_category` int(10) unsigned NOT NULL auto_increment,
  `id_category_parent` int(10) unsigned NOT NULL,
  `name` char(50) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id_category`),
  KEY `parent` (`id_category_parent`)
) ENGINE=MyISAM AUTO_INCREMENT=522 DEFAULT CHARSET=utf8;

/*Table structure for table `channel_categories` */

DROP TABLE IF EXISTS `channel_categories`;

CREATE TABLE `channel_categories` (
  `id_channel` int(10) unsigned NOT NULL,
  `id_category` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_channel`,`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `channel_codes` */

DROP TABLE IF EXISTS `channel_codes`;

CREATE TABLE `channel_codes` (
  `id_channel` int(10) unsigned NOT NULL,
  `id_code` int(10) unsigned NOT NULL,
  KEY `channel` (`id_channel`),
  KEY `code` (`id_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `channel_program_types` */

DROP TABLE IF EXISTS `channel_program_types`;

CREATE TABLE `channel_program_types` (
  `id_channel` int(10) unsigned NOT NULL,
  `program_type` enum('Flat_Rate','CPM') NOT NULL,
  `cost_text` decimal(16,4) NOT NULL,
  `cost_image` decimal(16,4) NOT NULL,
  `volume` int(10) unsigned NOT NULL,
  `title` varchar(32) NOT NULL,
  `id_program` int(10) unsigned NOT NULL auto_increment,
  `avg_cost_text` decimal(16,4) NOT NULL,
  `avg_cost_image` decimal(16,4) NOT NULL,
  PRIMARY KEY  (`id_program`),
  KEY `id_channel` (`id_channel`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

/*Table structure for table `channels` */

DROP TABLE IF EXISTS `channels`;

CREATE TABLE `channels` (
  `id_channel` int(10) unsigned NOT NULL auto_increment,
  `id_dimension` int(10) unsigned NOT NULL,
  `id_targeting_group` int(10) unsigned default NULL,
  `name` char(50) NOT NULL,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `status` enum('active','blocked','deleted','paused') NOT NULL default 'active',
  `description` text NOT NULL,
  `ad_type` set('text','image') NOT NULL,
  `channel_type` enum('contextual','keywords') NOT NULL,
  `ad_settings` enum('tag','blank','blank_color') NOT NULL,
  `id_parent_site` int(10) unsigned NOT NULL,
  `blank_color` char(6) NOT NULL default 'FFFFFF',
  `ad_sources` set('advertisers','xml_feeds') NOT NULL default 'advertisers,xml_feeds',
  PRIMARY KEY  (`id_channel`),
  KEY `dimension` (`id_dimension`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;

/*Table structure for table `clicks` */

DROP TABLE IF EXISTS `clicks`;

CREATE TABLE `clicks` (
  `id_click` varchar(50) character set latin1 NOT NULL,
  `type` enum('search','click','empty') NOT NULL default 'empty',
  `datetime` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `country` char(2) character set latin1 NOT NULL,
  `language` char(2) character set latin1 NOT NULL,
  `program_type` varchar(12) character set latin1 NOT NULL,
  `ad_type` varchar(10) character set latin1 NOT NULL,
  `ad_display_type` varchar(10) character set latin1 NOT NULL,
  `id_feed` int(10) unsigned NOT NULL,
  `id_advertiser` int(10) unsigned NOT NULL,
  `id_campaign` int(10) unsigned NOT NULL,
  `id_group` int(10) unsigned NOT NULL,
  `id_ad` int(10) unsigned NOT NULL,
  `id_site` int(10) unsigned NOT NULL,
  `id_channel` int(10) unsigned NOT NULL,
  `id_group_site_channel` int(10) unsigned NOT NULL,
  `position` tinyint(3) unsigned NOT NULL,
  `destination_url` varchar(255) character set latin1 NOT NULL,
  `referer_url` varchar(255) character set latin1 NOT NULL,
  `user_agent` varchar(255) character set latin1 NOT NULL,
  `browser` varchar(20) character set latin1 NOT NULL,
  `status` varchar(10) character set latin1 NOT NULL,
  `spent` decimal(16,4) unsigned NOT NULL,
  `session_id` varchar(32) NOT NULL,
  PRIMARY KEY  (`id_click`,`type`),
  KEY `date` (`date`),
  KEY `session_id` (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `color_schemes` */

DROP TABLE IF EXISTS `color_schemes`;

CREATE TABLE `color_schemes` (
  `id_color_scheme` int(10) unsigned NOT NULL auto_increment,
  `id_entity_publisher` int(10) unsigned NOT NULL,
  `name` char(50) NOT NULL,
  `border_color` char(6) NOT NULL,
  `title_color` char(6) NOT NULL,
  `title_id_font` int(10) unsigned NOT NULL,
  `title_font_size` tinyint(3) unsigned NOT NULL,
  `title_font_style` enum('normal','italic') NOT NULL default 'normal',
  `title_font_weight` enum('normal','bold') NOT NULL default 'normal',
  `background_color` char(6) NOT NULL,
  `text_color` char(6) NOT NULL,
  `text_id_font` int(10) unsigned NOT NULL,
  `text_font_size` tinyint(3) NOT NULL,
  `text_font_style` enum('normal','italic') NOT NULL default 'normal',
  `text_font_weight` enum('normal','bold') NOT NULL default 'normal',
  `url_color` char(6) NOT NULL,
  `url_id_font` int(10) unsigned NOT NULL,
  `url_font_size` tinyint(3) unsigned NOT NULL,
  `url_font_style` enum('normal','italic') NOT NULL default 'normal',
  `url_font_weight` enum('normal','bold') NOT NULL default 'normal',
  PRIMARY KEY  (`id_color_scheme`),
  KEY `publisher` (`id_entity_publisher`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `contact_field_restrictions` */

DROP TABLE IF EXISTS `contact_field_restrictions`;

CREATE TABLE `contact_field_restrictions` (
  `id_contact_field` int(10) unsigned NOT NULL,
  `value` varchar(50) NOT NULL,
  `value_order` tinyint(3) unsigned NOT NULL,
  KEY `id_contact_field` (`id_contact_field`),
  KEY `value_order` (`value_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `contact_fields` */

DROP TABLE IF EXISTS `contact_fields`;

CREATE TABLE `contact_fields` (
  `id_contact_field` int(10) unsigned NOT NULL auto_increment,
  `name` char(20) NOT NULL,
  `title` char(20) NOT NULL,
  `description` text,
  `field_order` tinyint(3) unsigned default NULL,
  `type` enum('int','string','data','bool') default NULL,
  PRIMARY KEY  (`id_contact_field`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

/*Table structure for table `contacts` */

DROP TABLE IF EXISTS `contacts`;

CREATE TABLE `contacts` (
  `id_entity` int(10) unsigned NOT NULL,
  `id_contact_field` int(10) unsigned NOT NULL,
  `value` varchar(100) NOT NULL,
  KEY `id_entity` (`id_entity`),
  KEY `id_field` (`id_contact_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `continents` */

DROP TABLE IF EXISTS `continents`;

CREATE TABLE `continents` (
  `id_continent` int(10) unsigned NOT NULL auto_increment,
  `name` char(20) NOT NULL,
  PRIMARY KEY  (`id_continent`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `countries` */

DROP TABLE IF EXISTS `countries`;

CREATE TABLE `countries` (
  `iso` char(2) NOT NULL,
  `name` varchar(50) NOT NULL,
  `unicode_name` tinyblob,
  `banned` enum('true','false') NOT NULL default 'false',
  `id_continent` int(11) NOT NULL default '1',
  PRIMARY KEY  (`iso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `dashboard_blocks` */

DROP TABLE IF EXISTS `dashboard_blocks`;

CREATE TABLE `dashboard_blocks` (
  `id_block` int(10) unsigned NOT NULL auto_increment,
  `id_role` int(10) unsigned NOT NULL,
  `column` enum('left','right','top') NOT NULL default 'left',
  `position` tinyint(3) unsigned NOT NULL,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id_block`)
) ENGINE=MyISAM AUTO_INCREMENT=112 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `dimensions` */

DROP TABLE IF EXISTS `dimensions`;

CREATE TABLE `dimensions` (
  `id_dimension` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `max_ad_slots` tinyint(1) unsigned NOT NULL,
  `orientation` enum('horisontal','vertical','square') NOT NULL,
  `width` smallint(5) unsigned NOT NULL,
  `height` smallint(5) unsigned NOT NULL,
  `title_size` tinyint(3) unsigned NOT NULL default '35',
  `text_size` tinyint(3) unsigned NOT NULL default '75',
  `url_size` tinyint(3) unsigned NOT NULL default '35',
  `rows_count` tinyint(1) NOT NULL default '0',
  `columns_count` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id_dimension`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;

/*Table structure for table `entities` */

DROP TABLE IF EXISTS `entities`;

CREATE TABLE `entities` (
  `id_entity` int(10) unsigned NOT NULL auto_increment,
  `name` char(50) NOT NULL,
  `e_mail` char(100) NOT NULL,
  `password` char(32) NOT NULL,
  `creation_date` date NOT NULL,
  `ballance` decimal(16,4) NOT NULL default '0.0000',
  `password_recovery` char(32) default NULL,
  `bonus` decimal(16,4) NOT NULL,
  PRIMARY KEY  (`id_entity`),
  UNIQUE KEY `e_mail` (`e_mail`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

/*Table structure for table `entity_roles` */

DROP TABLE IF EXISTS `entity_roles`;

CREATE TABLE `entity_roles` (
  `id_entity` int(10) unsigned NOT NULL,
  `id_role` int(10) unsigned NOT NULL,
  `status` enum('activation','active','blocked','deleted') NOT NULL default 'active',
  PRIMARY KEY  (`id_entity`,`id_role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `feed_clicks` */

DROP TABLE IF EXISTS `feed_clicks`;

CREATE TABLE `feed_clicks` (
  `id_feed` int(10) unsigned NOT NULL,
  `ip_address` int(10) unsigned NOT NULL,
  `datetime` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_feed`,`ip_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `feeds` */

DROP TABLE IF EXISTS `feeds`;

CREATE TABLE `feeds` (
  `id_feed` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `class` varchar(100) NOT NULL,
  `affiliate_id_1` varchar(100) default NULL,
  `affiliate_id_2` varchar(100) default NULL,
  `affiliate_id_3` varchar(100) default NULL,
  `status` enum('active','paused') default 'active',
  `commission` decimal(5,2) unsigned NOT NULL default '50.00',
  `url` varchar(255) NOT NULL,
  `publisher_commission` decimal(5,2) unsigned NOT NULL default '50.00',
  `capping` int(2) unsigned NOT NULL default '24',
  `timeout` int(11) default '2800',
  PRIMARY KEY  (`id_feed`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `feeds_to_request` */

DROP TABLE IF EXISTS `feeds_to_request`;

CREATE TABLE `feeds_to_request` (
  `name` varchar(50) NOT NULL default '0',
  PRIMARY KEY  (`name`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `field_formats` */

DROP TABLE IF EXISTS `field_formats`;

CREATE TABLE `field_formats` (
  `id_field_format` tinyint(3) unsigned NOT NULL default '0',
  `locale` varchar(10) NOT NULL,
  `sprintf` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `field_types` */

DROP TABLE IF EXISTS `field_types`;

CREATE TABLE `field_types` (
  `id_field_type` tinyint(3) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id_field_type`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Table structure for table `fonts` */

DROP TABLE IF EXISTS `fonts`;

CREATE TABLE `fonts` (
  `id_font` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id_font`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `group_channels` */

DROP TABLE IF EXISTS `group_channels`;

CREATE TABLE `group_channels` (
  `id_group` int(10) unsigned NOT NULL,
  `id_channel` int(10) unsigned NOT NULL,
  `id_program` int(11) NOT NULL,
  UNIQUE KEY `id` (`id_channel`,`id_group`),
  KEY `subprogram` (`id_program`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `group_site_channels` */

DROP TABLE IF EXISTS `group_site_channels`;

CREATE TABLE `group_site_channels` (
  `id_group` int(10) unsigned NOT NULL,
  `id_site_channel` int(10) unsigned NOT NULL,
  `id_program` int(10) unsigned NOT NULL,
  `cost_text` decimal(16,4) unsigned NOT NULL,
  `cost_image` decimal(16,4) unsigned NOT NULL,
  `volume` int(10) unsigned NOT NULL,
  `avg_cost_text` decimal(16,4) unsigned NOT NULL,
  `avg_cost_image` decimal(16,4) unsigned NOT NULL,
  `ad_type` set('text','image') NOT NULL,
  `start_date_time` datetime default NULL,
  `end_date_time` datetime default NULL,
  `impressions` int(10) unsigned default NULL,
  `status` enum('active','completed','paused','trouble','unpaid','deleted') NOT NULL default 'unpaid',
  `clicks` int(10) unsigned NOT NULL,
  `current_impressions` int(10) unsigned NOT NULL default '0',
  `id_group_site_channel` int(10) unsigned NOT NULL auto_increment,
  `spent` decimal(16,4) unsigned NOT NULL,
  `pay_escrow` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`id_group_site_channel`),
  KEY `idp` (`id_program`,`ad_type`),
  KEY `index` (`id_group`,`id_site_channel`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=91 DEFAULT CHARSET=utf8;

/*Table structure for table `group_sites` */

DROP TABLE IF EXISTS `group_sites`;

CREATE TABLE `group_sites` (
  `id_group_site` int(10) unsigned NOT NULL auto_increment,
  `id_group` int(10) unsigned NOT NULL,
  `id_site` int(10) unsigned NOT NULL,
  `impressions` int(10) unsigned NOT NULL,
  `clicks` int(10) unsigned NOT NULL,
  `spent` decimal(16,4) unsigned NOT NULL,
  `status` enum('active','deleted','paused','trouble','unpaid') NOT NULL default 'active',
  PRIMARY KEY  (`id_group_site`),
  KEY `id_group` (`id_group`),
  KEY `id_site` (`id_site`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `groups` */

DROP TABLE IF EXISTS `groups`;

CREATE TABLE `groups` (
  `id_group` int(10) unsigned NOT NULL auto_increment,
  `id_campaign` int(10) unsigned NOT NULL,
  `name` char(50) NOT NULL,
  `status` enum('active','paused','blocked','deleted') NOT NULL default 'active',
  `frequency_coup` int(11) default NULL,
  `frequency_coup_term` enum('none','day','week','month','year') default 'day',
  `frequency_coup_current` int(11) NOT NULL,
  `budget` decimal(16,4) unsigned default NULL,
  `budget_term` enum('none','day','week','month','year') default 'day',
  `budget_current` decimal(16,4) NOT NULL,
  `text_ads` int(10) unsigned NOT NULL default '0',
  `image_ads` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_group`),
  KEY `campaign` (`id_campaign`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

/*Table structure for table `images` */

DROP TABLE IF EXISTS `images`;

CREATE TABLE `images` (
  `id_ad` int(10) unsigned default NULL,
  `id_link` int(10) unsigned default NULL,
  `id_product` int(10) unsigned default NULL,
  `id_dimension` int(10) unsigned NOT NULL,
  `filename` varchar(200) NOT NULL,
  `is_flash` enum('false','true') default 'false',
  `bgcolor` varchar(7) default NULL,
  KEY `dimension` (`id_dimension`),
  KEY `id` (`id_ad`,`id_link`,`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `import_logs` */

DROP TABLE IF EXISTS `import_logs`;

CREATE TABLE `import_logs` (
  `id_log` int(10) unsigned NOT NULL auto_increment,
  `server` tinyint(3) unsigned NOT NULL,
  `file` varchar(1024) NOT NULL,
  `filehash` char(32) NOT NULL,
  `datetime` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_log`),
  KEY `server` (`server`,`filehash`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Table structure for table `import_sessions` */

DROP TABLE IF EXISTS `import_sessions`;

CREATE TABLE `import_sessions` (
  `server` int(10) unsigned NOT NULL,
  `session_id` char(32) NOT NULL,
  `datetime` int(10) NOT NULL,
  PRIMARY KEY  (`server`,`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `installed_plugins` */

DROP TABLE IF EXISTS `installed_plugins`;

CREATE TABLE `installed_plugins` (
  `name` varchar(64) NOT NULL,
  `solution` enum('admarket','adserver','search') default NULL,
  `status` enum('installed','deleted','broken') NOT NULL default 'installed',
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `depending` varchar(255) default NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `languages` */

DROP TABLE IF EXISTS `languages`;

CREATE TABLE `languages` (
  `iso` char(3) NOT NULL,
  `name` char(20) NOT NULL,
  `unicode_name` tinyblob,
  `banned` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`iso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `layouts` */

DROP TABLE IF EXISTS `layouts`;

CREATE TABLE `layouts` (
  `id_layout` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(200) NOT NULL,
  `file` varchar(100) NOT NULL,
  `preview` varchar(100) NOT NULL,
  `status` enum('active','paused') NOT NULL default 'active',
  `columns_count` tinyint(3) unsigned default '0',
  PRIMARY KEY  (`id_layout`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `locale_settings` */

DROP TABLE IF EXISTS `locale_settings`;

CREATE TABLE `locale_settings` (
  `locale` char(10) NOT NULL,
  `date_format` varchar(50) NOT NULL,
  `date_input` varchar(50) NOT NULL,
  `time_format` varchar(50) NOT NULL,
  `time_input` varchar(50) NOT NULL,
  `money_format` varchar(50) NOT NULL,
  `number_format` varchar(50) NOT NULL,
  `week_start` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`locale`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `locales` */

DROP TABLE IF EXISTS `locales`;

CREATE TABLE `locales` (
  `locale` char(6) NOT NULL,
  `lang` varchar(50) default NULL,
  `country` varchar(50) default NULL,
  `flag` varchar(10) default NULL,
  `is_default` enum('false','true') default 'false',
  `template` varchar(255) default '<%FLAG%>',
  `status` enum('enabled','disabled') default 'disabled',
  `ldirection` enum('ltr','rtl') default 'ltr',
  `date_format` varchar(50) default 'mm.dd.yy',
  `date_input` varchar(50) default '%m.%d.%Y',
  `time_format` varchar(50) default 'H:i',
  `time_input` varchar(50) default '%H:%i',
  PRIMARY KEY  (`locale`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `menu_items` */

DROP TABLE IF EXISTS `menu_items`;

CREATE TABLE `menu_items` (
  `id_menu_item` int(10) unsigned NOT NULL auto_increment,
  `id_role` int(10) unsigned NOT NULL,
  `parent_item_id` int(10) unsigned default NULL,
  `position` mediumint(4) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `controller` varchar(200) NOT NULL,
  `visible` enum('true','false') NOT NULL default 'true',
  PRIMARY KEY  (`id_menu_item`),
  UNIQUE KEY `position` (`id_role`,`parent_item_id`,`position`)
) ENGINE=MyISAM AUTO_INCREMENT=519 DEFAULT CHARSET=utf8;

/*Table structure for table `money_flows` */

DROP TABLE IF EXISTS `money_flows`;

CREATE TABLE `money_flows` (
  `id_flow` int(10) unsigned NOT NULL auto_increment,
  `id_entity_receipt` int(10) unsigned NOT NULL,
  `id_entity_expense` int(10) unsigned NOT NULL,
  `flow_date` datetime NOT NULL,
  `value` decimal(16,4) NOT NULL,
  `flow_program` enum('deposit','withdraw','move','click','program','deduction','check','return','denied','transaction','chargeback') NOT NULL,
  `id_flow_parent` int(10) unsigned NOT NULL,
  `id_ads` int(10) unsigned default NULL,
  `id_feeds` int(10) unsigned default NULL,
  `is_processed` enum('false','true') NOT NULL,
  `balance_receipt` decimal(16,4) NOT NULL,
  `balance_expense` decimal(16,4) NOT NULL default '0.0000',
  `escrow_receipt` decimal(16,4) NOT NULL,
  `escrow_expense` decimal(16,4) NOT NULL,
  PRIMARY KEY  (`id_flow`),
  KEY `parent` (`id_flow_parent`),
  KEY `receipt` (`id_entity_receipt`),
  KEY `expense` (`id_entity_expense`),
  KEY `flow_date` (`flow_date`),
  KEY `processed` (`is_processed`)
) ENGINE=MyISAM AUTO_INCREMENT=86 DEFAULT CHARSET=utf8;

/*Table structure for table `news_categories` */

DROP TABLE IF EXISTS `news_categories`;

CREATE TABLE `news_categories` (
  `id_news_category` int(10) unsigned NOT NULL auto_increment,
  `id_news_category_parent` int(10) unsigned NOT NULL,
  `name` char(50) NOT NULL,
  PRIMARY KEY  (`id_news_category`),
  KEY `parent` (`id_news_category_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `orbitscripts_admin_news` */

DROP TABLE IF EXISTS `orbitscripts_admin_news`;

CREATE TABLE `orbitscripts_admin_news` (
  `id` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `title` text NOT NULL,
  `link` varchar(256) NOT NULL default 'http://orbitscripts.com',
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `orbitscripts_payment_gateways_news` */

DROP TABLE IF EXISTS `orbitscripts_payment_gateways_news`;

CREATE TABLE `orbitscripts_payment_gateways_news` (
  `id` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `payment_gateway_id` int(3) unsigned default '0',
  `version` varchar(16) default '0',
  `title` tinytext,
  `description` tinytext,
  `link` varchar(256) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `orbitscripts_plugins_news` */

DROP TABLE IF EXISTS `orbitscripts_plugins_news`;

CREATE TABLE `orbitscripts_plugins_news` (
  `id` int(3) unsigned NOT NULL,
  `plugin_id` tinyint(3) unsigned NOT NULL default '0',
  `version` varchar(16) NOT NULL,
  `title` tinytext NOT NULL,
  `link` varchar(256) NOT NULL,
  `description` tinytext NOT NULL,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `payment_gateways` */

DROP TABLE IF EXISTS `payment_gateways`;

CREATE TABLE `payment_gateways` (
  `id_entity` int(10) unsigned NOT NULL,
  `account_id_1` varchar(50) NOT NULL,
  `account_id_1_title` varchar(32) NOT NULL,
  `account_id_2` varchar(50) default NULL,
  `account_id_2_title` varchar(32) default NULL,
  `use_account_id_2` tinyint(1) unsigned NOT NULL default '0',
  `status` enum('enabled','disabled') default NULL,
  `description` text,
  `mode` set('deposit','withdraw') NOT NULL,
  `withdraw_comm` decimal(5,2) NOT NULL default '0.00',
  `fund_comm` decimal(5,2) NOT NULL default '0.00',
  `version` varchar(32) NOT NULL,
  `id_gateway` varchar(20) NOT NULL,
  `validation_rules_1` varchar(100) default NULL,
  `validation_rules_2` varchar(100) default NULL,
  `max_1` tinyint(3) unsigned default NULL,
  `max_2` tinyint(3) unsigned default NULL,
  `possibility_mode` enum('deposit','withdraw','all') default 'all',
  `minimal_payment` decimal(8,4) default '1.0000',
  `account_id_3` text,
  `account_id_3_title` varchar(32) default NULL,
  `use_account_id_3` tinyint(1) unsigned NOT NULL default '0',
  `validation_rules_3` varchar(100) default NULL,
  `max_3` tinyint(3) unsigned default NULL,
  `use_textarea` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id_entity`),
  KEY `status` (`status`),
  KEY `mode` (`mode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `payment_gateways_to_request` */

DROP TABLE IF EXISTS `payment_gateways_to_request`;

CREATE TABLE `payment_gateways_to_request` (
  `title` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `payment_transactions` */

DROP TABLE IF EXISTS `payment_transactions`;

CREATE TABLE `payment_transactions` (
  `id_flow` int(10) unsigned NOT NULL COMMENT 'SmartPPC transaction ID',
  `transaction_id` varchar(32) NOT NULL default '' COMMENT 'Payment Gateway transaction ID',
  PRIMARY KEY  (`id_flow`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `paypal_payment_transactions` */

DROP TABLE IF EXISTS `paypal_payment_transactions`;

CREATE TABLE `paypal_payment_transactions` (
  `id_transaction` int(10) unsigned NOT NULL auto_increment,
  `id_payment_request` int(10) unsigned default NULL,
  `e_mail` varchar(100) NOT NULL,
  `time` datetime NOT NULL,
  `amount` varchar(20) NOT NULL,
  `money` varchar(20) default NULL,
  PRIMARY KEY  (`id_transaction`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `plugins` */

DROP TABLE IF EXISTS `plugins`;

CREATE TABLE `plugins` (
  `name` varchar(100) NOT NULL,
  `config_file` varchar(100) NOT NULL default '',
  `status` enum('enabled','disabled') NOT NULL default 'enabled',
  `loading_order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `program_purchases` */

DROP TABLE IF EXISTS `program_purchases`;

CREATE TABLE `program_purchases` (
  `id_purchase` int(10) unsigned NOT NULL auto_increment,
  `id_group_site_channel` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `volume` int(10) unsigned NOT NULL,
  `spent` decimal(16,4) unsigned NOT NULL,
  PRIMARY KEY  (`id_purchase`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `program_types` */

DROP TABLE IF EXISTS `program_types`;

CREATE TABLE `program_types` (
  `id_program_type` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY  (`id_program_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `report_entity_columns` */

DROP TABLE IF EXISTS `report_entity_columns`;

CREATE TABLE `report_entity_columns` (
  `id_entity` int(10) unsigned NOT NULL,
  `id_report_type` int(10) unsigned NOT NULL,
  `visible_columns` set('c0','c1','c2','c3','c4','c5','c6','c7','c8','c9','c10','c11','c12','c13','c14','c15','c16','c17','c18','c19','c20','c21','c22','c23','c24') NOT NULL,
  PRIMARY KEY  (`id_entity`,`id_report_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `report_groups` */

DROP TABLE IF EXISTS `report_groups`;

CREATE TABLE `report_groups` (
  `id_report_group` int(10) unsigned NOT NULL,
  `id_role` tinyint(3) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `controller` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id_report_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `report_type_fields` */

DROP TABLE IF EXISTS `report_type_fields`;

CREATE TABLE `report_type_fields` (
  `id_report_type` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL default '',
  `title` varchar(50) NOT NULL default '',
  `column_order` tinyint(3) unsigned NOT NULL,
  `description` text NOT NULL,
  `id_field_type` tinyint(3) NOT NULL,
  `direction` enum('asc','desc') NOT NULL default 'asc',
  `is_total` enum('true','false') NOT NULL default 'false',
  `is_unchanged` enum('true','false') NOT NULL default 'false',
  UNIQUE KEY `id` (`id_report_type`,`column_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `report_types` */

DROP TABLE IF EXISTS `report_types`;

CREATE TABLE `report_types` (
  `id_report_type` int(10) unsigned NOT NULL auto_increment,
  `id_role` int(11) NOT NULL,
  `report_group` int(10) NOT NULL,
  `report_order` tinyint(3) unsigned NOT NULL,
  `title` char(50) NOT NULL,
  `description` text,
  `sort_column` tinyint(3) unsigned NOT NULL default '0',
  `sort_direction` enum('asc','desc') NOT NULL default 'asc',
  PRIMARY KEY  (`id_report_type`),
  UNIQUE KEY `role` (`id_role`,`report_group`,`report_order`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

/*Table structure for table `requested_reports` */

DROP TABLE IF EXISTS `requested_reports`;

CREATE TABLE `requested_reports` (
  `id_requested_report` int(10) unsigned NOT NULL auto_increment,
  `id_entity` int(10) unsigned NOT NULL,
  `custom_title` varchar(100) NOT NULL,
  `request_date` datetime NOT NULL,
  `id_report_type` int(10) unsigned NOT NULL,
  `visible_columns` set('c0','c1','c2','c3','c4','c5','c6','c7','c8','c9','c10','c11','c12','c13','c14','c15','c16','c17','c18','c19','c20','c21','c22','c23','c24') NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `extra_params` longblob,
  PRIMARY KEY  (`id_requested_report`),
  KEY `entity` (`id_entity`)
) ENGINE=MyISAM AUTO_INCREMENT=58 DEFAULT CHARSET=utf8;

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id_role` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `is_used` enum('true','false') NOT NULL default 'true',
  `is_recipient` enum('true','false') NOT NULL default 'true',
  `class` varchar(100) NOT NULL default 'Sppc_Entity_Role',
  PRIMARY KEY  (`id_role`),
  KEY `is_used` (`is_used`),
  KEY `is_recipient` (`is_recipient`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Table structure for table `schedule_timetables` */

DROP TABLE IF EXISTS `schedule_timetables`;

CREATE TABLE `schedule_timetables` (
  `id_schedule` int(10) unsigned NOT NULL,
  `id_timetable` int(11) NOT NULL,
  `weekday` tinyint(1) unsigned NOT NULL,
  KEY `id_schedule` (`id_schedule`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `schedules` */

DROP TABLE IF EXISTS `schedules`;

CREATE TABLE `schedules` (
  `id_schedule` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id_schedule`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `sessions` */

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `session_id` varchar(40) NOT NULL default '0',
  `ip_address` varchar(16) NOT NULL default '0',
  `user_agent` varchar(50) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL default '0',
  `user_data` text,
  PRIMARY KEY  (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `setting_field_restrictions` */

DROP TABLE IF EXISTS `setting_field_restrictions`;

CREATE TABLE `setting_field_restrictions` (
  `name` char(32) NOT NULL,
  `value` varchar(50) NOT NULL,
  `value_order` tinyint(3) unsigned NOT NULL,
  KEY `id_contact_field` (`name`),
  KEY `value_order` (`value_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `setting_fields` */

DROP TABLE IF EXISTS `setting_fields`;

CREATE TABLE `setting_fields` (
  `name` char(32) NOT NULL default '0',
  `type` enum('int','string','datetime','bool') NOT NULL,
  `title` char(50) default NULL,
  `description` text,
  `setting_order` tinyint(3) unsigned default NULL,
  `default_value` varchar(200) default NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `settings` */

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `id_entity` int(10) unsigned NOT NULL,
  `name` char(64) NOT NULL,
  `value` varchar(200) NOT NULL,
  `expired` datetime default NULL,
  PRIMARY KEY  (`id_entity`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `settings_big` */

DROP TABLE IF EXISTS `settings_big`;

CREATE TABLE `settings_big` (
  `id_entity` int(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` text,
  `expired` datetime default NULL,
  PRIMARY KEY  (`id_entity`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `show_contents` */

DROP TABLE IF EXISTS `show_contents`;

CREATE TABLE `show_contents` (
  `id` char(32) NOT NULL default '',
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `content` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `site_categories` */

DROP TABLE IF EXISTS `site_categories`;

CREATE TABLE `site_categories` (
  `id_site` int(10) unsigned NOT NULL,
  `id_category` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_site`,`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `site_channels` */

DROP TABLE IF EXISTS `site_channels`;

CREATE TABLE `site_channels` (
  `id_site` int(10) unsigned NOT NULL,
  `id_channel` int(10) unsigned NOT NULL,
  `status` enum('active','blocked','deleted','paused') NOT NULL default 'active',
  `id_site_channel` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id_site_channel`),
  UNIQUE KEY `site_channel` (`id_site`,`id_channel`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;

/*Table structure for table `site_color_schemes` */

DROP TABLE IF EXISTS `site_color_schemes`;

CREATE TABLE `site_color_schemes` (
  `id_color_scheme` int(10) unsigned NOT NULL auto_increment,
  `name` char(50) NOT NULL,
  `path_to_css` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_color_scheme`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `site_layout_channels` */

DROP TABLE IF EXISTS `site_layout_channels`;

CREATE TABLE `site_layout_channels` (
  `id_site_layout_channel` int(10) unsigned NOT NULL auto_increment,
  `id_site_layout` int(10) unsigned NOT NULL,
  `id_site_layout_zone` int(10) unsigned NOT NULL,
  `id_channel` int(10) unsigned NOT NULL,
  `x` smallint(5) unsigned NOT NULL default '0',
  `y` smallint(5) unsigned NOT NULL default '0',
  `width` smallint(5) unsigned NOT NULL default '0',
  `height` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_site_layout_channel`),
  KEY `index_site_layout_channels_id_channel` (`id_channel`),
  KEY `index_site_layout_channels_id_site_layout` (`id_site_layout`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

/*Table structure for table `site_layout_zones` */

DROP TABLE IF EXISTS `site_layout_zones`;

CREATE TABLE `site_layout_zones` (
  `id_site_layout_zone` int(10) unsigned NOT NULL auto_increment,
  `id_site_layout` int(10) unsigned NOT NULL,
  `colspan` tinyint(3) unsigned NOT NULL,
  `rowspan` tinyint(3) unsigned NOT NULL,
  `order` tinyint(3) unsigned NOT NULL default '0',
  `id_json_zone` varchar(32) NOT NULL default '0',
  PRIMARY KEY  (`id_site_layout_zone`),
  KEY `site_layout_zones_id_site_layout` (`id_site_layout`)
) ENGINE=MyISAM AUTO_INCREMENT=88 DEFAULT CHARSET=utf8;

/*Table structure for table `site_layouts` */

DROP TABLE IF EXISTS `site_layouts`;

CREATE TABLE `site_layouts` (
  `id_site_layout` int(10) unsigned NOT NULL auto_increment,
  `id_site` int(10) unsigned default NULL,
  `width` smallint(5) unsigned NOT NULL default '0',
  `height` smallint(5) unsigned NOT NULL default '0',
  `size_w1` smallint(5) unsigned NOT NULL default '0',
  `size_w2` smallint(5) unsigned NOT NULL default '0',
  `size_w3` smallint(5) unsigned NOT NULL default '0',
  `size_h1` smallint(5) unsigned NOT NULL default '0',
  `size_h2` smallint(5) unsigned NOT NULL default '0',
  `size_h3` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_site_layout`),
  UNIQUE KEY `idx_site_layouts_id_site` (`id_site`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

/*Table structure for table `sites` */

DROP TABLE IF EXISTS `sites`;

CREATE TABLE `sites` (
  `id_site` int(10) unsigned NOT NULL auto_increment,
  `id_entity_publisher` int(10) unsigned NOT NULL,
  `url` varchar(150) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `status` enum('unapproved','pending','denied','active','blocked','deleted','paused') NOT NULL default 'active',
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `ownership_confirmation_code` char(32) default NULL,
  PRIMARY KEY  (`id_site`),
  KEY `publisher` (`id_entity_publisher`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

/*Table structure for table `stat_ads` */

DROP TABLE IF EXISTS `stat_ads`;

CREATE TABLE `stat_ads` (
  `id_ad` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_ad`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `stat_ads_packet` */

DROP TABLE IF EXISTS `stat_ads_packet`;

CREATE TABLE `stat_ads_packet` (
  `id_ad` int(10) unsigned NOT NULL,
  `id_group_site_channel` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_ad`,`id_group_site_channel`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `stat_advertiser_channels` */

DROP TABLE IF EXISTS `stat_advertiser_channels`;

CREATE TABLE `stat_advertiser_channels` (
  `id_entity_advertiser` int(11) unsigned NOT NULL,
  `id_group` int(10) unsigned NOT NULL,
  `id_site` int(10) unsigned NOT NULL,
  `id_channel` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_channel`,`stat_date`,`id_site`,`id_entity_advertiser`,`id_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `stat_advertisers` */

DROP TABLE IF EXISTS `stat_advertisers`;

CREATE TABLE `stat_advertisers` (
  `id_entity_advertiser` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  `impressions` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_entity_advertiser`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `stat_campaigns` */

DROP TABLE IF EXISTS `stat_campaigns`;

CREATE TABLE `stat_campaigns` (
  `id_campaign` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_campaign`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `stat_channels` */

DROP TABLE IF EXISTS `stat_channels`;

CREATE TABLE `stat_channels` (
  `id_channel` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `alternative_impressions` int(10) unsigned NOT NULL default '0',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_channel`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `stat_feeds` */

DROP TABLE IF EXISTS `stat_feeds`;

CREATE TABLE `stat_feeds` (
  `id_feed` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_feed`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `stat_groups` */

DROP TABLE IF EXISTS `stat_groups`;

CREATE TABLE `stat_groups` (
  `id_group` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_group`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `stat_sites` */

DROP TABLE IF EXISTS `stat_sites`;

CREATE TABLE `stat_sites` (
  `id_site` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `alternative_impressions` int(10) unsigned NOT NULL default '0',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_site`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `stat_sites_channels` */

DROP TABLE IF EXISTS `stat_sites_channels`;

CREATE TABLE `stat_sites_channels` (
  `id_channel` int(10) unsigned NOT NULL,
  `id_site` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `alternative_impressions` int(10) unsigned NOT NULL default '0',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_channel`,`id_site`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `tags` */

DROP TABLE IF EXISTS `tags`;

CREATE TABLE `tags` (
  `id_tag` int(11) unsigned NOT NULL auto_increment,
  `code` text NOT NULL,
  PRIMARY KEY  (`id_tag`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;

/*Table structure for table `targeting_group_values` */

DROP TABLE IF EXISTS `targeting_group_values`;

CREATE TABLE `targeting_group_values` (
  `id_targeting_group_value` int(10) unsigned NOT NULL auto_increment,
  `id_targeting_group` int(10) unsigned NOT NULL,
  `group` enum('browsers','ips','languages','operating_systems','user_agents','countries','urls','referers','variables') NOT NULL,
  `name` varchar(50) default NULL,
  `value` varchar(255) NOT NULL,
  `compare` enum('equals','not_equals','contain','not_contain','regexp','not_regexp','less_than','more_than') default 'equals',
  PRIMARY KEY  (`id_targeting_group_value`),
  KEY `value` (`value`),
  KEY `search` (`id_targeting_group`)
) ENGINE=MyISAM AUTO_INCREMENT=2483 DEFAULT CHARSET=utf8;

/*Table structure for table `targeting_groups` */

DROP TABLE IF EXISTS `targeting_groups`;

CREATE TABLE `targeting_groups` (
  `id_targeting_group` int(10) unsigned NOT NULL auto_increment,
  `id_entity` int(10) unsigned NOT NULL,
  `id_role` int(10) unsigned NOT NULL,
  `title` varchar(50) NOT NULL default '',
  `status` enum('active','paused','deleted','temp') NOT NULL default 'active',
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_targeting_group`),
  KEY `status` (`status`),
  KEY `dateindex` (`creation_date`)
) ENGINE=MyISAM AUTO_INCREMENT=299 DEFAULT CHARSET=utf8;

/*Table structure for table `timetables` */

DROP TABLE IF EXISTS `timetables`;

CREATE TABLE `timetables` (
  `id_timetable` int(11) NOT NULL,
  `h_00` tinyint(1) unsigned NOT NULL default '0',
  `h_01` tinyint(1) unsigned NOT NULL default '0',
  `h_02` tinyint(1) unsigned NOT NULL default '0',
  `h_03` tinyint(1) unsigned NOT NULL default '0',
  `h_04` tinyint(1) unsigned NOT NULL default '0',
  `h_05` tinyint(1) unsigned NOT NULL default '0',
  `h_06` tinyint(1) unsigned NOT NULL default '0',
  `h_07` tinyint(1) unsigned NOT NULL default '0',
  `h_08` tinyint(1) unsigned NOT NULL default '0',
  `h_09` tinyint(1) unsigned NOT NULL default '0',
  `h_10` tinyint(1) unsigned NOT NULL default '0',
  `h_11` tinyint(1) unsigned NOT NULL default '0',
  `h_12` tinyint(1) unsigned NOT NULL default '0',
  `h_13` tinyint(1) unsigned NOT NULL default '0',
  `h_14` tinyint(1) unsigned NOT NULL default '0',
  `h_15` tinyint(1) unsigned NOT NULL default '0',
  `h_16` tinyint(1) unsigned NOT NULL default '0',
  `h_17` tinyint(1) unsigned NOT NULL default '0',
  `h_18` tinyint(1) unsigned NOT NULL default '0',
  `h_19` tinyint(1) unsigned NOT NULL default '0',
  `h_20` tinyint(1) unsigned NOT NULL default '0',
  `h_21` tinyint(1) unsigned NOT NULL default '0',
  `h_22` tinyint(1) unsigned NOT NULL default '0',
  `h_23` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_timetable`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `timezones` */

DROP TABLE IF EXISTS `timezones`;

CREATE TABLE `timezones` (
  `id_timezone` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `position` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_timezone`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `wizard_steps` */

DROP TABLE IF EXISTS `wizard_steps`;

CREATE TABLE `wizard_steps` (
  `id_wizard_step` varchar(150) NOT NULL default '',
  `id_wizard` varchar(30) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `review_title` varchar(255) default NULL,
  `controller` varchar(255) NOT NULL default '',
  `review_controller` varchar(255) default NULL,
  `step` tinyint(3) unsigned NOT NULL,
  `confirmation_button_title` varchar(70) default NULL,
  `review_next_step` varchar(255) default NULL,
  `review_previous_step` varchar(255) default NULL,
  PRIMARY KEY  (`id_wizard_step`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `wizards` */

DROP TABLE IF EXISTS `wizards`;

CREATE TABLE `wizards` (
  `id_wizard` varchar(30) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`id_wizard`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `zips` */

DROP TABLE IF EXISTS `zips`;

CREATE TABLE `zips` (
  `id_zip` int(10) unsigned NOT NULL auto_increment,
  `id_city` int(10) unsigned NOT NULL,
  `zip` char(20) NOT NULL,
  PRIMARY KEY  (`id_zip`),
  KEY `city` (`id_city`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;