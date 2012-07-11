-- MySQL dump 10.11
--
-- Host: 192.168.0.7    Database: orbit_adserver_install
-- ------------------------------------------------------
-- Server version	5.0.70

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ad_places_names`
--

DROP TABLE IF EXISTS `ad_places_names`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `ad_places_names` (
  `place` varchar(64) default NULL,
  `name` varchar(64) default NULL,
  `color` char(6) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `ad_places_names`
--

LOCK TABLES `ad_places_names` WRITE;
/*!40000 ALTER TABLE `ad_places_names` DISABLE KEYS */;
INSERT INTO `ad_places_names` VALUES ('sites','Content Network','000000'),('domains','Parked Domains','000000'),('intext','In-Text','000000'),('allsites','Entire Network','000000'),('search','Search',NULL);
/*!40000 ALTER TABLE `ad_places_names` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad_types`
--

DROP TABLE IF EXISTS `ad_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `ad_types` (
  `id_ad_type` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id_ad_type`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `ad_types`
--

LOCK TABLES `ad_types` WRITE;
/*!40000 ALTER TABLE `ad_types` DISABLE KEYS */;
INSERT INTO `ad_types` VALUES (1,'text'),(2,'image'),(3,'richmedia');
/*!40000 ALTER TABLE `ad_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `additional_field_restrictions`
--

DROP TABLE IF EXISTS `additional_field_restrictions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `additional_field_restrictions` (
  `id_additional_field` int(10) unsigned NOT NULL,
  `value` varchar(200) NOT NULL,
  `value_order` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`id_additional_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `additional_field_restrictions`
--

LOCK TABLES `additional_field_restrictions` WRITE;
/*!40000 ALTER TABLE `additional_field_restrictions` DISABLE KEYS */;
/*!40000 ALTER TABLE `additional_field_restrictions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `additional_fields`
--

DROP TABLE IF EXISTS `additional_fields`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `additional_fields` (
  `id_additional_field` int(10) unsigned NOT NULL auto_increment,
  `name` char(50) NOT NULL,
  `type` enum('int','string','datetime','bool') NOT NULL,
  `default_value` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `field_order` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`id_additional_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `additional_fields`
--

LOCK TABLES `additional_fields` WRITE;
/*!40000 ALTER TABLE `additional_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `additional_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_news`
--

DROP TABLE IF EXISTS `admin_news`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `admin_news` (
  `id_news` int(10) unsigned NOT NULL auto_increment,
  `target` int(11) NOT NULL default '0',
  `content` text NOT NULL,
  `creation_date` datetime NOT NULL default '2008-01-01 00:00:00',
  `publication_date` datetime NOT NULL,
  `status` enum('deleted','published','unpublished') NOT NULL default 'unpublished',
  `title` text NOT NULL,
  PRIMARY KEY  (`id_news`),
  KEY `target` (`target`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `admin_news`
--

LOCK TABLES `admin_news` WRITE;
/*!40000 ALTER TABLE `admin_news` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `admins` (
  `id_entity_admin` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_entity_admin`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ads`
--

DROP TABLE IF EXISTS `ads`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `ads`
--

LOCK TABLES `ads` WRITE;
/*!40000 ALTER TABLE `ads` DISABLE KEYS */;
/*!40000 ALTER TABLE `ads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ads_additional_fields`
--

DROP TABLE IF EXISTS `ads_additional_fields`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `ads_additional_fields` (
  `id_ad` int(10) unsigned NOT NULL,
  `id_additional_field` int(10) unsigned NOT NULL,
  `value` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_ad`,`id_additional_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `ads_additional_fields`
--

LOCK TABLES `ads_additional_fields` WRITE;
/*!40000 ALTER TABLE `ads_additional_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `ads_additional_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ads_rich`
--

DROP TABLE IF EXISTS `ads_rich`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `ads_rich` (
  `id_ad` int(10) unsigned NOT NULL,
  `rich_content` text NOT NULL,
  `id_dimension` int(10) NOT NULL,
  PRIMARY KEY  (`id_ad`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `ads_rich`
--

LOCK TABLES `ads_rich` WRITE;
/*!40000 ALTER TABLE `ads_rich` DISABLE KEYS */;
/*!40000 ALTER TABLE `ads_rich` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advertisers`
--

DROP TABLE IF EXISTS `advertisers`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `advertisers` (
  `id_entity_advertiser` int(10) unsigned NOT NULL,
  `current_ballance` decimal(16,4) NOT NULL default '0.0000',
  `id_category` int(10) unsigned default NULL,
  `description` text,
  `longitude` double default NULL,
  `latitude` double default NULL,
  `popup_weight` int(2) NOT NULL default '50',
  PRIMARY KEY  (`id_entity_advertiser`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `advertisers`
--

LOCK TABLES `advertisers` WRITE;
/*!40000 ALTER TABLE `advertisers` DISABLE KEYS */;
/*!40000 ALTER TABLE `advertisers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `affiliate_entity`
--

DROP TABLE IF EXISTS `affiliate_entity`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `affiliate_entity` (
  `id_entity_affiliate` int(10) unsigned NOT NULL,
  `id_entity` int(10) unsigned NOT NULL,
  UNIQUE KEY `entity` (`id_entity`),
  KEY `affiliate` (`id_entity_affiliate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `affiliate_entity`
--

LOCK TABLES `affiliate_entity` WRITE;
/*!40000 ALTER TABLE `affiliate_entity` DISABLE KEYS */;
/*!40000 ALTER TABLE `affiliate_entity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `affiliates`
--

DROP TABLE IF EXISTS `affiliates`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `affiliates` (
  `id_entity_affiliate` int(10) unsigned NOT NULL,
  `commission` decimal(5,2) unsigned default NULL,
  PRIMARY KEY  (`id_entity_affiliate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `affiliates`
--

LOCK TABLES `affiliates` WRITE;
/*!40000 ALTER TABLE `affiliates` DISABLE KEYS */;
/*!40000 ALTER TABLE `affiliates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alert_types`
--

DROP TABLE IF EXISTS `alert_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `alert_types` (
  `id_alert_type` tinyint(3) unsigned NOT NULL auto_increment,
  `caption` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `param_count` tinyint(1) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL,
  `id_role` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_alert_type`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `alert_types`
--

LOCK TABLES `alert_types` WRITE;
/*!40000 ALTER TABLE `alert_types` DISABLE KEYS */;
INSERT INTO `alert_types` VALUES (3,'Unpaid prices!','You have unpaid prices.',0,'unpaid_prices',3),(4,'Troubled prices!','You have troubled prices.',0,'troubled_prices',3),(5,'Unpaid CPC sites!','You have unpaid CPC sites.',0,'unpaid_cpc_sites',3),(6,'Troubled CPC sites!','You have troubled CPC sites.',0,'troubled_cpc_sites',3);
/*!40000 ALTER TABLE `alert_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alerts`
--

DROP TABLE IF EXISTS `alerts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `alerts` (
  `id_alert` tinyint(3) unsigned NOT NULL auto_increment,
  `id_entity` int(10) unsigned NOT NULL,
  `id_alert_type` int(10) unsigned NOT NULL,
  `parameter_a` varchar(50) default NULL,
  `parameter_b` varchar(50) default NULL,
  `parameter_c` varchar(50) default NULL,
  `creation_date` datetime NOT NULL,
  PRIMARY KEY  (`id_alert`),
  KEY `id_entity` (`id_entity`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `alerts`
--

LOCK TABLES `alerts` WRITE;
/*!40000 ALTER TABLE `alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `box_categories`
--

DROP TABLE IF EXISTS `box_categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `box_categories` (
  `id_category` int(10) unsigned NOT NULL auto_increment,
  `id_category_parent` int(10) unsigned NOT NULL default '0',
  `name` char(50) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id_category`),
  KEY `parent` (`id_category_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `box_categories`
--

LOCK TABLES `box_categories` WRITE;
/*!40000 ALTER TABLE `box_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `box_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `boxes`
--

DROP TABLE IF EXISTS `boxes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `boxes` (
  `id_box` int(10) unsigned NOT NULL auto_increment,
  `id_category` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `preview` varchar(100) NOT NULL,
  `status` enum('active','paused') NOT NULL default 'active',
  `options` text,
  `class` varchar(255) NOT NULL default 'Sppc_Box',
  `tab_box_class` varchar(255) NOT NULL default 'Sppc_Member_Tab_Box',
  `js_class` varchar(255) NOT NULL default 'Sppc.Landing.AbstractBox',
  `js_file` varchar(255) default NULL,
  `controller` varchar(255) NOT NULL,
  `admin_controller` varchar(255) NOT NULL,
  `guid` char(36) NOT NULL,
  PRIMARY KEY  (`id_box`),
  UNIQUE KEY `name` (`name`),
  KEY `status` (`status`),
  KEY `id_category` (`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `boxes`
--

LOCK TABLES `boxes` WRITE;
/*!40000 ALTER TABLE `boxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `boxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `browsers`
--

DROP TABLE IF EXISTS `browsers`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `browsers` (
  `name` varchar(20) NOT NULL default '',
  `title` varchar(50) default NULL,
  `position` int(10) unsigned NOT NULL,
  `regexp` varchar(255) NOT NULL default '',
  `banned` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `browsers`
--

LOCK TABLES `browsers` WRITE;
/*!40000 ALTER TABLE `browsers` DISABLE KEYS */;
INSERT INTO `browsers` VALUES ('msie','Internet Explorer',1,'MSIE|Internet Explorer','false'),('firefox','FireFox',2,'FireFox','false'),('opera','Opera',6,'Opera','false'),('shiira','Shiira',8,'Shiira','false'),('chimera','Chimera',9,'Chimera','false'),('phoenix','Phoenix',10,'Phoenix','false'),('firebird','Firebird',11,'Firebird','false'),('camino','Camino',12,'Camino','false'),('netscape','Netscape',7,'Netscape','false'),('omniweb','OmniWeb',13,'OmniWeb','false'),('mozilla','Mozilla',5,'.+Mozilla','false'),('safari','Safari',4,'Safari','false'),('konqueror','Konqueror',14,'Konqueror','false'),('icab','iCab',15,'iCab','false'),('hotjava','HotJava',16,'HotJava','false'),('chrome','Google Chrome',3,'Chrome','false'),('unknown','Unknown',100,'Unknown','false');
/*!40000 ALTER TABLE `browsers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_plugin`
--

DROP TABLE IF EXISTS `cache_plugin`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `cache_plugin` (
  `id_plugin` int(10) unsigned default NULL,
  `live_period` enum('hour','day','week','month') default NULL,
  `status` enum('enabled','disabled') default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `cache_plugin`
--

LOCK TABLES `cache_plugin` WRITE;
/*!40000 ALTER TABLE `cache_plugin` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_plugin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_clicks`
--

DROP TABLE IF EXISTS `campaign_clicks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `campaign_clicks` (
  `id_campaign` int(10) unsigned NOT NULL,
  `ip_address` int(10) unsigned NOT NULL,
  `datetime` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_campaign`,`ip_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `campaign_clicks`
--

LOCK TABLES `campaign_clicks` WRITE;
/*!40000 ALTER TABLE `campaign_clicks` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_clicks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_countries`
--

DROP TABLE IF EXISTS `campaign_countries`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `campaign_countries` (
  `id_campaign` int(10) unsigned NOT NULL,
  `country` char(2) NOT NULL,
  PRIMARY KEY  (`id_campaign`,`country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `campaign_countries`
--

LOCK TABLES `campaign_countries` WRITE;
/*!40000 ALTER TABLE `campaign_countries` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_languages`
--

DROP TABLE IF EXISTS `campaign_languages`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `campaign_languages` (
  `id_campaign` int(10) unsigned NOT NULL,
  `language` char(3) NOT NULL,
  PRIMARY KEY  (`id_campaign`,`language`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `campaign_languages`
--

LOCK TABLES `campaign_languages` WRITE;
/*!40000 ALTER TABLE `campaign_languages` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_types`
--

DROP TABLE IF EXISTS `campaign_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `campaign_types` (
  `campaign_type` varchar(16) NOT NULL,
  `campaign_name` varchar(32) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`campaign_type`),
  UNIQUE KEY `campaign_type` (`campaign_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `campaign_types`
--

LOCK TABLES `campaign_types` WRITE;
/*!40000 ALTER TABLE `campaign_types` DISABLE KEYS */;
INSERT INTO `campaign_types` VALUES ('cpm_flatrate','CPM / Flat Rate','CPM / Flat Rate Campaign Description'),('cpc','CPC','CPC Campaign Description');
/*!40000 ALTER TABLE `campaign_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  `capping` int(2) unsigned NOT NULL default '24',
  PRIMARY KEY  (`id_campaign`),
  KEY `advertiser` (`id_entity_advertiser`),
  KEY `status` (`status`),
  KEY `id_campaign_type` (`id_campaign_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `campaigns`
--

LOCK TABLES `campaigns` WRITE;
/*!40000 ALTER TABLE `campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `captcha`
--

DROP TABLE IF EXISTS `captcha`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `captcha` (
  `captcha_id` bigint(13) unsigned NOT NULL auto_increment,
  `captcha_time` int(10) unsigned NOT NULL,
  `ip_address` varchar(16) NOT NULL default '0',
  `word` varchar(20) NOT NULL,
  PRIMARY KEY  (`captcha_id`),
  KEY `word` (`word`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `captcha`
--

LOCK TABLES `captcha` WRITE;
/*!40000 ALTER TABLE `captcha` DISABLE KEYS */;
/*!40000 ALTER TABLE `captcha` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `categories` (
  `id_category` int(10) unsigned NOT NULL auto_increment,
  `id_category_parent` int(10) unsigned NOT NULL,
  `name` char(50) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id_category`),
  KEY `parent` (`id_category_parent`)
) ENGINE=MyISAM AUTO_INCREMENT=521 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,0,'Categories','General category'),(375,1,'News & Current Events','News & Current Events'),(387,1,'Photo & Video','Photo & Video'),(477,1,'Sports','Sports'),(508,1,'Travel','Travel');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category_keywords`
--

DROP TABLE IF EXISTS `category_keywords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `category_keywords` (
  `id_category` int(10) unsigned NOT NULL,
  `id_keyword` char(32) NOT NULL,
  PRIMARY KEY  (`id_category`,`id_keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `category_keywords`
--

LOCK TABLES `category_keywords` WRITE;
/*!40000 ALTER TABLE `category_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `category_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `channel_categories`
--

DROP TABLE IF EXISTS `channel_categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `channel_categories` (
  `id_channel` int(10) unsigned NOT NULL,
  `id_category` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_channel`,`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `channel_categories`
--

LOCK TABLES `channel_categories` WRITE;
/*!40000 ALTER TABLE `channel_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `channel_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `channel_codes`
--

DROP TABLE IF EXISTS `channel_codes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `channel_codes` (
  `id_channel` int(10) unsigned NOT NULL,
  `id_code` int(10) unsigned NOT NULL,
  KEY `channel` (`id_channel`),
  KEY `code` (`id_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `channel_codes`
--

LOCK TABLES `channel_codes` WRITE;
/*!40000 ALTER TABLE `channel_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `channel_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `channel_feeds`
--

DROP TABLE IF EXISTS `channel_feeds`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `channel_feeds` (
  `id_channel` int(10) unsigned NOT NULL,
  `id_feed` int(10) unsigned NOT NULL,
  `status` enum('active','paused') NOT NULL default 'active',
  `affiliate_id_1` varchar(100) default NULL,
  `affiliate_id_2` varchar(100) default NULL,
  `affiliate_id_3` varchar(100) default NULL,
  `commission` decimal(5,2) unsigned default '50.00',
  PRIMARY KEY  (`id_channel`,`id_feed`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `channel_feeds`
--

LOCK TABLES `channel_feeds` WRITE;
/*!40000 ALTER TABLE `channel_feeds` DISABLE KEYS */;
/*!40000 ALTER TABLE `channel_feeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `channel_keywords`
--

DROP TABLE IF EXISTS `channel_keywords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `channel_keywords` (
  `id_channel` int(10) unsigned NOT NULL default '0',
  `id_keyword` char(32) NOT NULL default '0',
  PRIMARY KEY  (`id_channel`,`id_keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `channel_keywords`
--

LOCK TABLES `channel_keywords` WRITE;
/*!40000 ALTER TABLE `channel_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `channel_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `channel_program_types`
--

DROP TABLE IF EXISTS `channel_program_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  `cost_richmedia` decimal(16,4) NOT NULL,
  `avg_cost_richmedia` decimal(16,4) NOT NULL,
  PRIMARY KEY  (`id_program`),
  KEY `id_channel` (`id_channel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `channel_program_types`
--

LOCK TABLES `channel_program_types` WRITE;
/*!40000 ALTER TABLE `channel_program_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `channel_program_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `channel_tags`
--

DROP TABLE IF EXISTS `channel_tags`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `channel_tags` (
  `id_channel` int(10) unsigned NOT NULL,
  `id_tag` int(11) unsigned NOT NULL,
  `id_targeting_group` int(10) default NULL,
  `status` enum('active','suspended','deleted') NOT NULL default 'active',
  `priority` int(10) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id_channel`,`id_tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `channel_tags`
--

LOCK TABLES `channel_tags` WRITE;
/*!40000 ALTER TABLE `channel_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `channel_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `channels`
--

DROP TABLE IF EXISTS `channels`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `channels` (
  `id_channel` int(10) unsigned NOT NULL auto_increment,
  `id_dimension` int(10) unsigned NOT NULL,
  `id_targeting_group` int(10) unsigned default NULL,
  `name` char(50) NOT NULL,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `status` enum('active','blocked','deleted','paused') NOT NULL default 'active',
  `description` text NOT NULL,
  `ad_type` set('text','image','richmedia') NOT NULL,
  `channel_type` enum('contextual','keywords') NOT NULL,
  `ad_settings` enum('tag','blank','blank_color') NOT NULL,
  `id_parent_site` int(10) unsigned NOT NULL,
  `use_cpc` enum('true','false') NOT NULL default 'true',
  `cpm_cpc_ratio` varchar(10) NOT NULL default '100/100',
  `blank_color` char(6) NOT NULL default 'FFFFFF',
  `ad_sources` set('advertisers','xml_feeds','tags') NOT NULL default 'advertisers,xml_feeds',
  `ad_sources_mix_rule` enum('advertisers_first','xml_feeds_first','mix_by_bid') NOT NULL default 'advertisers_first',
  `tags_priority` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_channel`),
  KEY `dimension` (`id_dimension`),
  KEY `use_cpc` (`use_cpc`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `channels`
--

LOCK TABLES `channels` WRITE;
/*!40000 ALTER TABLE `channels` DISABLE KEYS */;
/*!40000 ALTER TABLE `channels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checks`
--

DROP TABLE IF EXISTS `checks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `checks` (
  `id_check` int(11) NOT NULL auto_increment,
  `id_payment_request` int(11) NOT NULL,
  `id_flow` int(11) NOT NULL,
  `value` decimal(16,4) NOT NULL,
  `status` enum('send','cash','expired','return','resend') NOT NULL default 'send',
  `send_date` date NOT NULL,
  `expiration_date` date NOT NULL,
  `transaction_id` char(20) NOT NULL,
  PRIMARY KEY  (`id_check`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `checks`
--

LOCK TABLES `checks` WRITE;
/*!40000 ALTER TABLE `checks` DISABLE KEYS */;
/*!40000 ALTER TABLE `checks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `cities` (
  `id_city` int(10) unsigned NOT NULL auto_increment,
  `id_country` int(10) unsigned NOT NULL,
  `id_region` int(10) unsigned NOT NULL,
  `longitude` double NOT NULL,
  `latitude` double NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id_city`),
  KEY `country` (`id_country`),
  KEY `region` (`id_region`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `cities`
--

LOCK TABLES `cities` WRITE;
/*!40000 ALTER TABLE `cities` DISABLE KEYS */;
/*!40000 ALTER TABLE `cities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clicks`
--

DROP TABLE IF EXISTS `clicks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `clicks` (
  `id_click` varchar(50) character set latin1 NOT NULL,
  `type` enum('search','click','organic','conversion','empty','flatrate','cpm','popup') NOT NULL default 'empty',
  `datetime` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `server` tinyint(3) unsigned NOT NULL,
  `ip_address` varchar(15) character set latin1 NOT NULL,
  `ip_proxy` varchar(15) character set latin1 NOT NULL,
  `country` char(2) character set latin1 NOT NULL,
  `language` char(2) character set latin1 NOT NULL,
  `search_type` varchar(15) character set latin1 NOT NULL,
  `program_type` varchar(12) character set latin1 NOT NULL,
  `ad_type` varchar(10) character set latin1 NOT NULL,
  `ad_display_type` varchar(10) character set latin1 NOT NULL,
  `id_feed` int(10) unsigned NOT NULL,
  `id_advertiser` int(10) unsigned NOT NULL,
  `id_campaign` int(10) unsigned NOT NULL,
  `id_group` int(10) unsigned NOT NULL,
  `id_ad` int(10) unsigned NOT NULL,
  `id_publisher` int(10) unsigned NOT NULL,
  `id_site` int(10) unsigned NOT NULL,
  `id_channel` int(10) unsigned NOT NULL,
  `id_domain` int(10) unsigned NOT NULL,
  `id_group_site` int(10) unsigned NOT NULL,
  `id_group_site_channel` int(10) unsigned NOT NULL,
  `position` tinyint(3) unsigned NOT NULL,
  `keyword` varchar(255) character set latin1 NOT NULL,
  `id_keyword` char(32) character set latin1 NOT NULL,
  `destination_url` varchar(255) character set latin1 NOT NULL,
  `referer_url` varchar(255) character set latin1 NOT NULL,
  `user_agent` varchar(255) character set latin1 NOT NULL,
  `browser` varchar(20) character set latin1 NOT NULL,
  `status` varchar(10) character set latin1 NOT NULL,
  `fraud_cause` varchar(20) character set latin1 NOT NULL,
  `spent` decimal(16,4) unsigned NOT NULL,
  `revenue_admin` decimal(16,4) unsigned NOT NULL,
  `revenue_publisher` decimal(16,4) unsigned NOT NULL,
  `id_search_type` int(10) NOT NULL,
  `id_conversion` int(10) unsigned default NULL,
  `conversion_value` decimal(16,4) unsigned default NULL,
  `id_category` int(10) unsigned NOT NULL,
  `session_id` varchar(32) NOT NULL,
  PRIMARY KEY  (`id_click`,`type`),
  KEY `date` (`date`),
  KEY `server` (`server`),
  KEY `session_id` (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `clicks`
--

LOCK TABLES `clicks` WRITE;
/*!40000 ALTER TABLE `clicks` DISABLE KEYS */;
/*!40000 ALTER TABLE `clicks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clicks_server_1`
--

DROP TABLE IF EXISTS `clicks_server_1`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `clicks_server_1` (
  `id_click` varchar(50) character set latin1 NOT NULL,
  `type` enum('search','click','organic','conversion','empty','flatrate','cpm','popup') NOT NULL default 'empty',
  `datetime` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `server` tinyint(3) unsigned NOT NULL,
  `ip_address` varchar(15) character set latin1 NOT NULL,
  `ip_proxy` varchar(15) character set latin1 NOT NULL,
  `country` char(2) character set latin1 NOT NULL,
  `language` char(2) character set latin1 NOT NULL,
  `search_type` varchar(15) character set latin1 NOT NULL,
  `program_type` varchar(12) character set latin1 NOT NULL,
  `ad_type` varchar(10) character set latin1 NOT NULL,
  `ad_display_type` varchar(10) character set latin1 NOT NULL,
  `id_feed` int(10) unsigned NOT NULL,
  `id_advertiser` int(10) unsigned NOT NULL,
  `id_campaign` int(10) unsigned NOT NULL,
  `id_group` int(10) unsigned NOT NULL,
  `id_ad` int(10) unsigned NOT NULL,
  `id_publisher` int(10) unsigned NOT NULL,
  `id_site` int(10) unsigned NOT NULL,
  `id_channel` int(10) unsigned NOT NULL,
  `id_domain` int(10) unsigned NOT NULL,
  `id_group_site` int(10) unsigned NOT NULL,
  `id_group_site_channel` int(10) unsigned NOT NULL,
  `position` tinyint(3) unsigned NOT NULL,
  `keyword` varchar(255) character set latin1 NOT NULL,
  `id_keyword` char(32) character set latin1 NOT NULL,
  `destination_url` varchar(255) character set latin1 NOT NULL,
  `referer_url` varchar(255) character set latin1 NOT NULL,
  `user_agent` varchar(255) character set latin1 NOT NULL,
  `browser` varchar(20) character set latin1 NOT NULL,
  `status` varchar(10) character set latin1 NOT NULL,
  `fraud_cause` varchar(20) character set latin1 NOT NULL,
  `spent` decimal(16,4) unsigned NOT NULL,
  `revenue_admin` decimal(16,4) unsigned NOT NULL,
  `revenue_publisher` decimal(16,4) unsigned NOT NULL,
  `id_search_type` int(10) NOT NULL,
  `id_conversion` int(10) unsigned default NULL,
  `conversion_value` decimal(16,4) unsigned default NULL,
  `id_category` int(10) unsigned NOT NULL,
  `session_id` varchar(32) NOT NULL,
  PRIMARY KEY  (`id_click`,`type`),
  KEY `date` (`date`),
  KEY `server` (`server`),
  KEY `session_id` (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `clicks_server_1`
--

LOCK TABLES `clicks_server_1` WRITE;
/*!40000 ALTER TABLE `clicks_server_1` DISABLE KEYS */;
/*!40000 ALTER TABLE `clicks_server_1` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `codes`
--

DROP TABLE IF EXISTS `codes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `codes` (
  `id_code` int(10) unsigned NOT NULL auto_increment,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `description` varchar(50) default NULL,
  `id_color_scheme` int(10) unsigned NOT NULL,
  `id_entity_publisher` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `codes`
--

LOCK TABLES `codes` WRITE;
/*!40000 ALTER TABLE `codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `color_schemes`
--

DROP TABLE IF EXISTS `color_schemes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `color_schemes`
--

LOCK TABLES `color_schemes` WRITE;
/*!40000 ALTER TABLE `color_schemes` DISABLE KEYS */;
INSERT INTO `color_schemes` VALUES (1,1,'Default','AAAAAA','0000FF',2,12,'','','FFFFFF','000000',2,12,'','','008000',2,12,'','');
/*!40000 ALTER TABLE `color_schemes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `companies` (
  `id_company` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY  (`id_company`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_field_restrictions`
--

DROP TABLE IF EXISTS `contact_field_restrictions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contact_field_restrictions` (
  `id_contact_field` int(10) unsigned NOT NULL,
  `value` varchar(50) NOT NULL,
  `value_order` tinyint(3) unsigned NOT NULL,
  KEY `id_contact_field` (`id_contact_field`),
  KEY `value_order` (`value_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `contact_field_restrictions`
--

LOCK TABLES `contact_field_restrictions` WRITE;
/*!40000 ALTER TABLE `contact_field_restrictions` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_field_restrictions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_fields`
--

DROP TABLE IF EXISTS `contact_fields`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contact_fields` (
  `id_contact_field` int(10) unsigned NOT NULL auto_increment,
  `name` char(20) NOT NULL,
  `title` char(20) NOT NULL,
  `description` text,
  `field_order` tinyint(3) unsigned default NULL,
  `type` enum('int','string','data','bool') default NULL,
  PRIMARY KEY  (`id_contact_field`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `contact_fields`
--

LOCK TABLES `contact_fields` WRITE;
/*!40000 ALTER TABLE `contact_fields` DISABLE KEYS */;
INSERT INTO `contact_fields` VALUES (7,'country','Country',NULL,0,'string'),(8,'timezone','TimeZone',NULL,1,'int'),(9,'city','City',NULL,2,'string'),(10,'address','Address',NULL,3,'string'),(11,'zip_postal','ZIP/Postal Code',NULL,4,'string'),(12,'phone','Phone #',NULL,5,'string'),(13,'sex','Sex',NULL,6,'string'),(14,'bithday','Bithday',NULL,7,'string'),(15,'state','State',NULL,8,'string'),(16,'photo','Photo',NULL,9,'string');
/*!40000 ALTER TABLE `contact_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contacts` (
  `id_entity` int(10) unsigned NOT NULL,
  `id_contact_field` int(10) unsigned NOT NULL,
  `value` varchar(100) NOT NULL,
  KEY `id_entity` (`id_entity`),
  KEY `id_field` (`id_contact_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `continents`
--

DROP TABLE IF EXISTS `continents`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `continents` (
  `id_continent` int(10) unsigned NOT NULL auto_increment,
  `name` char(20) NOT NULL,
  PRIMARY KEY  (`id_continent`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `continents`
--

LOCK TABLES `continents` WRITE;
/*!40000 ALTER TABLE `continents` DISABLE KEYS */;
INSERT INTO `continents` VALUES (1,'North America'),(2,'South America'),(3,'Africa'),(4,'Europe'),(5,'Asia'),(6,'Australia & Oceania'),(7,'Unknown'),(8,'Antarctic');
/*!40000 ALTER TABLE `continents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `countries` (
  `iso` char(2) NOT NULL,
  `name` varchar(50) NOT NULL,
  `unicode_name` tinyblob,
  `banned` enum('true','false') NOT NULL default 'false',
  `id_continent` int(11) NOT NULL default '1',
  PRIMARY KEY  (`iso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` VALUES ('AF','Afghanistan ','افغانستان','false',5),('AX','Aland Islands','','false',4),('AL','Albania ','Shqipëria','false',4),('DZ','Algeria ','الجزائر','false',3),('AS','American Samoa','','false',6),('AD','Andorra','','false',4),('AO','Angola','','false',3),('AI','Anguilla','','false',1),('AQ','Antarctica','','false',8),('AG','Antigua and Barbuda','','false',1),('AR','Argentina','','false',2),('AM','Armenia ','Հայաստան','false',5),('AW','Aruba','','false',1),('AU','Australia','','false',6),('AT','Austria ','Österreich','false',4),('AZ','Azerbaijan ','Azərbaycan','false',5),('BS','Bahamas','','false',1),('BH','Bahrain ','البحرين','false',5),('BD','Bangladesh ','বাংলাদেশ','false',5),('BB','Barbados','','false',1),('BY','Belarus ','Белару́сь','false',4),('BE','Belgium ','België','false',4),('BZ','Belize','','false',1),('BJ','Benin ','Bénin','false',3),('BM','Bermuda','','false',1),('BT','Bhutan ','འབྲུག་ཡུལ','false',5),('BO','Bolivia','','false',2),('BA','Bosnia and Herzegovina ','Bosna i Hercegovina','false',4),('BW','Botswana','','false',3),('BV','Bouvet Island','','false',1),('BR','Brazil ','Brasil','false',2),('IO','British Indian Ocean Territory','','false',5),('BN','Brunei ','Brunei Darussalam','false',5),('BG','Bulgaria ','България','false',4),('BF','Burkina Faso','','false',3),('BI','Burundi ','Uburundi','false',3),('KH','Cambodia ','Kampuchea','false',5),('CM','Cameroon ','Cameroun','false',3),('CA','Canada','','false',1),('CV','Cape Verde ','Cabo Verde','false',3),('KY','Cayman Islands','','false',1),('CF','Central African Republic ','République Centrafricaine','false',3),('TD','Chad ','Tchad','false',3),('CL','Chile','','false',2),('CN','China ','中国','false',5),('CX','Christmas Island','','false',5),('CC','Cocos Islands','','false',5),('CO','Colombia','','false',2),('KM','Comoros ','Comores','false',3),('CG','Congo','','false',3),('CD','Congo, Democratic Republic of the','','false',3),('CK','Cook Islands','','false',6),('CR','Costa Rica','','false',1),('CI','C?te d\'Ivoire','','false',3),('HR','Croatia ','Hrvatska','false',4),('CU','Cuba','','false',1),('CY','Cyprus ','Κυπρος','false',5),('CZ','Czech Republic ','Česko','false',4),('DK','Denmark ','Danmark','false',4),('DJ','Djibouti','','false',3),('DM','Dominica','','false',1),('DO','Dominican Republic','','false',1),('EC','Ecuador','','false',2),('EG','Egypt ','مصر','false',3),('SV','El Salvador','','false',1),('GQ','Equatorial Guinea ','Guinea Ecuatorial','false',3),('ER','Eritrea ','Ertra','false',3),('EE','Estonia ','Eesti','false',4),('ET','Ethiopia ','Ityop\'iya','false',3),('FK','Falkland Islands','','false',2),('FO','Faroe Islands','','false',1),('FJ','Fiji','','false',6),('FI','Finland ','Suomi','false',4),('FR','France','','false',4),('GF','French Guiana','','false',2),('PF','French Polynesia','','false',6),('TF','French Southern Territories','','false',8),('GA','Gabon','','false',3),('GM','Gambia','','false',3),('GE','Georgia ','საქართველო','false',5),('DE','Germany ','Deutschland','false',4),('GH','Ghana','','false',3),('GI','Gibraltar','','false',4),('GR','Greece ','\'Eλλας','false',4),('GL','Greenland','','false',1),('GD','Grenada','','false',1),('GP','Guadeloupe','','false',1),('GU','Guam','','false',6),('GT','Guatemala','','false',1),('GG','Guernsey','','false',4),('GN','Guinea ','Guinée','false',3),('GW','Guinea-Bissau ','Guiné-Bissau','false',3),('GY','Guyana','','false',2),('HT','Haiti ','Haïti','false',1),('HM','Heard Island and McDonald Islands','','false',8),('HN','Honduras','','false',1),('HK','Hong Kong','','false',5),('HU','Hungary ','Magyarország','false',4),('IS','Iceland ','Ísland','false',4),('IN','India','','false',5),('ID','Indonesia','','false',5),('IR','Iran ','ایران','false',5),('IQ','Iraq ','العراق','false',5),('IE','Ireland','','false',4),('IM','Isle of Man','','false',4),('IL','Israel ','ישראל','false',5),('IT','Italy ','Italia','false',4),('JM','Jamaica','','false',1),('JP','Japan ','日本','false',5),('JE','Jersey','','false',4),('JO','Jordan ','الاردن','false',5),('KZ','Kazakhstan ','Қазақстан','false',5),('KE','Kenya','','false',3),('KI','Kiribati','','false',6),('KW','Kuwait ','الكويت','false',5),('KG','Kyrgyzstan ','Кыргызстан','false',5),('LA','Laos ','ນລາວ','false',5),('LV','Latvia ','Latvija','false',4),('LB','Lebanon ','لبنان','false',5),('LS','Lesotho','','false',3),('LR','Liberia','','false',3),('LY','Libya ','ليبيا','false',3),('LI','Liechtenstein','','false',4),('LT','Lithuania ','Lietuva','false',4),('LU','Luxembourg ','Lëtzebuerg','false',4),('MO','Macao','','false',5),('MK','Macedonia ','Македонија','false',4),('MG','Madagascar ','Madagasikara','false',3),('MW','Malawi','','false',3),('MY','Malaysia','','false',5),('MV','Maldives ','ގުޖޭއްރާ ޔާއްރިހޫމްޖ','false',5),('ML','Mali','','false',3),('MT','Malta','','false',4),('MH','Marshall Islands','','false',6),('MQ','Martinique','','false',1),('MR','Mauritania ','موريتانيا','false',3),('MU','Mauritius','','false',3),('YT','Mayotte','','false',3),('MX','Mexico ','México','false',1),('FM','Micronesia','','false',6),('MD','Moldova','','false',4),('MC','Monaco','','false',4),('MN','Mongolia ','Монгол Улс','false',5),('ME','Montenegro ','Црна Гора','false',4),('MS','Montserrat','','false',1),('MA','Morocco ','المغرب','false',3),('MZ','Mozambique ','Moçambique','false',3),('MM','Myanmar ','Burma','false',5),('NA','Namibia','','false',3),('NR','Nauru ','Naoero','false',6),('NP','Nepal ','नेपाल','false',5),('NL','Netherlands ','Nederland','false',4),('AN','Netherlands Antilles','','false',1),('NC','New Caledonia','','false',6),('NZ','New Zealand','','false',6),('NI','Nicaragua','','false',1),('NE','Niger','','false',3),('NG','Nigeria','','false',3),('NU','Niue','','false',6),('NF','Norfolk Island','','false',6),('MP','Northern Mariana Islands','','false',1),('KP','North Korea ','조선','false',5),('NO','Norway ','Norge','false',4),('OM','Oman ','عمان','false',5),('PK','Pakistan ','پاکستان','false',5),('PW','Palau ','Belau','false',6),('PS','Palestinian Territory','','false',5),('PA','Panama ','Panamá','false',1),('PG','Papua New Guinea','','false',6),('PY','Paraguay','','false',2),('PE','Peru ','Perú','false',2),('PH','Philippines ','Pilipinas','false',5),('PN','Pitcairn','','false',6),('PL','Poland ','Polska','false',4),('PT','Portugal','','false',4),('PR','Puerto Rico','','false',1),('QA','Qatar ','قطر','false',5),('RE','Reunion','','false',3),('RO','Romania ','România','false',4),('RU','Russia ','Россия','false',4),('RW','Rwanda','','false',3),('SH','Saint Helena','','false',3),('KN','Saint Kitts and Nevis','','false',1),('LC','Saint Lucia','','false',1),('PM','Saint Pierre and Miquelon','','false',1),('VC','Saint Vincent and the Grenadines','','false',1),('WS','Samoa','','false',6),('SM','San Marino','','false',4),('ST','Sao Tome and Principe ','São Tomé and Príncipe','false',3),('SA','Saudi Arabia ','المملكة العربية السعودية','false',5),('SN','Senegal ','Sénégal','false',3),('RS','Serbia ','Србија','false',4),('CS','Serbia and Montenegro ','Србија и Црна Гора','false',4),('SC','Seychelles','','false',3),('SL','Sierra Leone','','false',3),('SG','Singapore ','Singapura','false',5),('SK','Slovakia ','Slovensko','false',4),('SI','Slovenia ','Slovenija','false',4),('SB','Solomon Islands','','false',6),('SO','Somalia ','Soomaaliya','false',3),('ZA','South Africa','','false',3),('GS','South Georgia and the South Sandwich Islands','','false',8),('KR','South Korea ','한국','false',5),('ES','Spain ','España','false',4),('LK','Sri Lanka','','false',5),('SD','Sudan ','السودان','false',3),('SR','Suriname','','false',2),('SJ','Svalbard and Jan Mayen','','false',4),('SZ','Swaziland','','false',3),('SE','Sweden ','Sverige','false',4),('CH','Switzerland ','Schweiz','false',4),('SY','Syria ','سوريا','false',5),('TW','Taiwan ','台灣','false',5),('TJ','Tajikistan ','Тоҷикистон','false',5),('TZ','Tanzania','','false',3),('TH','Thailand ','ราชอาณาจักรไทย','false',5),('TL','Timor-Leste','','false',5),('TG','Togo','','false',3),('TK','Tokelau','','false',6),('TO','Tonga','','false',6),('TT','Trinidad and Tobago','','false',1),('TN','Tunisia ','تونس','false',3),('TR','Turkey ','Türkiye','false',5),('TM','Turkmenistan ','Türkmenistan','false',5),('TC','Turks and Caicos Islands','','false',1),('TV','Tuvalu','','false',6),('UG','Uganda','','false',3),('UA','Ukraine ','Україна','false',4),('AE','United Arab Emirates ','الإمارات العربيّة المتّحدة','false',5),('GB','United Kingdom','','false',4),('US','United States','','false',1),('UM','United States minor outlying islands','','false',1),('UY','Uruguay','','false',2),('UZ','Uzbekistan ','O\'zbekiston','false',5),('VU','Vanuatu','','false',6),('VA','Vatican City ','Città del Vaticano','false',4),('VE','Venezuela','','false',2),('VN','Vietnam ','Việt Nam','false',5),('VG','Virgin Islands, British','','false',1),('VI','Virgin Islands, U.S.','','false',1),('WF','Wallis and Futuna','','false',6),('EH','Western Sahara ','الصحراء الغربية','false',3),('YE','Yemen ','اليمن','false',5),('ZM','Zambia','','false',3),('ZW','Zimbabwe','','false',3),('UN','Unknown',NULL,'false',7);
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dashboard_blocks`
--

DROP TABLE IF EXISTS `dashboard_blocks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `dashboard_blocks` (
  `id_block` int(10) unsigned NOT NULL auto_increment,
  `id_role` int(10) unsigned NOT NULL,
  `column` enum('left','right','top') NOT NULL default 'left',
  `position` tinyint(3) unsigned NOT NULL,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id_block`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `dashboard_blocks`
--

LOCK TABLES `dashboard_blocks` WRITE;
/*!40000 ALTER TABLE `dashboard_blocks` DISABLE KEYS */;
INSERT INTO `dashboard_blocks` VALUES (1,1,'left',1,'Sppc_Dashboard_Block_TodayEarnings'),(2,1,'left',2,'Sppc_Dashboard_Block_DatePicker'),(3,1,'left',3,'Sppc_Dashboard_Block_Summary_Publisher'),(4,1,'left',4,'Sppc_Dashboard_Block_Chart_Publisher'),(5,1,'left',5,'Sppc_Dashboard_Block_Table_Messages'),(6,1,'right',1,'Sppc_Dashboard_Block_Table_AdminTopSites'),(7,1,'right',2,'Sppc_Dashboard_Block_Table_AdminTopFeeds'),(8,1,'right',3,'Sppc_Dashboard_Block_Table_AdminTopAdvertisers'),(9,1,'right',10,'Sppc_Dashboard_Block_News_Orbitscripts'),(10,1,'top',1,'Sppc_Dashboard_Block_Alerts');
/*!40000 ALTER TABLE `dashboard_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dimensions`
--

DROP TABLE IF EXISTS `dimensions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  `ad_type` set('sites','domains','intext','allsites') NOT NULL default 'sites,allsites',
  `type` enum('standart','custom') NOT NULL default 'standart',
  `rows_count` tinyint(1) NOT NULL default '0',
  `columns_count` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id_dimension`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `dimensions`
--

LOCK TABLES `dimensions` WRITE;
/*!40000 ALTER TABLE `dimensions` DISABLE KEYS */;
INSERT INTO `dimensions` VALUES (1,'728 x 90 Leaderboard',4,'horisontal',728,90,25,70,35,'sites,domains,intext,allsites','standart',1,4),(2,'468 x 60 Banner',2,'horisontal',468,60,25,70,35,'sites,domains,intext,allsites','standart',1,2),(3,'234 x 60 Half Banner',1,'horisontal',234,60,25,70,35,'sites,domains,intext,allsites','standart',1,1),(4,'120 x 600 Skyscraper',4,'vertical',120,600,25,70,35,'sites,domains,allsites','standart',4,1),(5,'160 x 600 Wide Skyscraper',5,'vertical',160,600,25,70,35,'sites,domains,allsites','standart',5,1),(6,'120 x 240 Vertical Banner',2,'vertical',120,240,25,70,35,'sites,domains,intext,allsites','standart',2,1),(12,'180 x 150 Small Rectangle',1,'horisontal',180,150,25,70,35,'sites,domains,intext,allsites','standart',1,1),(8,'250 x 250 Medium Rectangle',3,'square',250,250,25,70,35,'sites,domains,intext,allsites','standart',3,1),(9,'200 x 200 Small Square',2,'square',200,200,25,70,35,'sites,domains,intext,allsites','standart',2,1),(10,'125 x 125 Button',1,'square',125,125,25,70,35,'sites,domains,intext,allsites','standart',1,1),(13,'300 x 250 Medium Rectangle',4,'horisontal',300,250,25,70,35,'sites,domains,intext,allsites','standart',4,1),(14,'336 x 280 Large Rectangle',4,'horisontal',336,280,25,70,35,'sites,domains,intext,allsites','standart',4,1);
/*!40000 ALTER TABLE `dimensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `directories`
--

DROP TABLE IF EXISTS `directories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `directories` (
  `id_directory` int(10) unsigned NOT NULL auto_increment,
  `name` char(50) NOT NULL,
  `id_directory_parent` int(10) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL,
  `bid` decimal(16,4) NOT NULL,
  `meta_title` text,
  `meta_description` text,
  PRIMARY KEY  (`id_directory`),
  UNIQUE KEY `name` (`name`),
  KEY `parent` (`id_directory_parent`),
  KEY `position` (`position`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `directories`
--

LOCK TABLES `directories` WRITE;
/*!40000 ALTER TABLE `directories` DISABLE KEYS */;
/*!40000 ALTER TABLE `directories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `directory_groups`
--

DROP TABLE IF EXISTS `directory_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `directory_groups` (
  `id_directory` int(10) unsigned NOT NULL,
  `id_group` int(10) unsigned NOT NULL,
  UNIQUE KEY `id` (`id_directory`,`id_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `directory_groups`
--

LOCK TABLES `directory_groups` WRITE;
/*!40000 ALTER TABLE `directory_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `directory_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `directory_keywords`
--

DROP TABLE IF EXISTS `directory_keywords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `directory_keywords` (
  `id_directory` int(10) unsigned NOT NULL,
  `id_keyword` char(32) NOT NULL,
  UNIQUE KEY `id` (`id_directory`,`id_keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `directory_keywords`
--

LOCK TABLES `directory_keywords` WRITE;
/*!40000 ALTER TABLE `directory_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `directory_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `directory_meta_keywords`
--

DROP TABLE IF EXISTS `directory_meta_keywords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `directory_meta_keywords` (
  `id_directory` int(10) unsigned NOT NULL,
  `id_meta_keyword` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_directory`,`id_meta_keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `directory_meta_keywords`
--

LOCK TABLES `directory_meta_keywords` WRITE;
/*!40000 ALTER TABLE `directory_meta_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `directory_meta_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domain_feeds`
--

DROP TABLE IF EXISTS `domain_feeds`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `domain_feeds` (
  `id_domain` int(10) unsigned NOT NULL,
  `id_feed` int(10) unsigned NOT NULL,
  `commissions` decimal(16,4) NOT NULL,
  `status` enum('active','passive','blocked') NOT NULL,
  UNIQUE KEY `id` (`id_domain`,`id_feed`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `domain_feeds`
--

LOCK TABLES `domain_feeds` WRITE;
/*!40000 ALTER TABLE `domain_feeds` DISABLE KEYS */;
/*!40000 ALTER TABLE `domain_feeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domain_keywords`
--

DROP TABLE IF EXISTS `domain_keywords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `domain_keywords` (
  `id_domain` int(10) unsigned NOT NULL,
  `id_keyword` char(32) NOT NULL,
  UNIQUE KEY `id` (`id_domain`,`id_keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `domain_keywords`
--

LOCK TABLES `domain_keywords` WRITE;
/*!40000 ALTER TABLE `domain_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `domain_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domain_names`
--

DROP TABLE IF EXISTS `domain_names`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `domain_names` (
  `id_domain_name` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY  (`id_domain_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `domain_names`
--

LOCK TABLES `domain_names` WRITE;
/*!40000 ALTER TABLE `domain_names` DISABLE KEYS */;
/*!40000 ALTER TABLE `domain_names` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domain_template_color_schemes`
--

DROP TABLE IF EXISTS `domain_template_color_schemes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `domain_template_color_schemes` (
  `id_color_scheme` int(10) unsigned NOT NULL auto_increment,
  `id_template` int(10) unsigned NOT NULL,
  `name` char(50) NOT NULL,
  `path_to_css` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_color_scheme`),
  KEY `template` (`id_template`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `domain_template_color_schemes`
--

LOCK TABLES `domain_template_color_schemes` WRITE;
/*!40000 ALTER TABLE `domain_template_color_schemes` DISABLE KEYS */;
/*!40000 ALTER TABLE `domain_template_color_schemes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domain_templates`
--

DROP TABLE IF EXISTS `domain_templates`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `domain_templates` (
  `id_template` int(10) unsigned NOT NULL auto_increment,
  `name` char(50) NOT NULL,
  `path` varchar(200) NOT NULL,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_template`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `domain_templates`
--

LOCK TABLES `domain_templates` WRITE;
/*!40000 ALTER TABLE `domain_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `domain_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domains`
--

DROP TABLE IF EXISTS `domains`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `domains` (
  `id_domain` int(10) unsigned NOT NULL auto_increment,
  `id_domain_name` int(10) unsigned NOT NULL,
  `id_entity_publisher` int(10) unsigned NOT NULL,
  `id_logo_image` int(10) unsigned NOT NULL,
  `id_category` int(10) unsigned NOT NULL,
  `id_color_scheme` int(10) unsigned NOT NULL,
  `id_template` int(10) unsigned NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `status` enum('active','passive','blocked') NOT NULL,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_domain`),
  KEY `ip` (`ip`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `domains`
--

LOCK TABLES `domains` WRITE;
/*!40000 ALTER TABLE `domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entities`
--

DROP TABLE IF EXISTS `entities`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `entities` (
  `id_entity` int(10) unsigned NOT NULL auto_increment,
  `name` char(50) NOT NULL,
  `e_mail` char(100) NOT NULL,
  `password` char(32) NOT NULL,
  `creation_date` date NOT NULL,
  `ballance` decimal(16,4) NOT NULL default '0.0000',
  `password_recovery` char(32) default NULL,
  `email_validation` char(32) default NULL,
  `bonus` decimal(16,4) NOT NULL,
  PRIMARY KEY  (`id_entity`),
  UNIQUE KEY `e_mail` (`e_mail`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `entities`
--

LOCK TABLES `entities` WRITE;
/*!40000 ALTER TABLE `entities` DISABLE KEYS */;
INSERT INTO `entities` VALUES (1,'Administrator','admin@smartppcevo.com','53bbc2682e88bdd6f583761423a8fe0d','2009-04-28','10000.0000',NULL,NULL,'0.0000'),(2,'PayPal','paypal@payment.com','-','2009-04-28','0.0000',NULL,NULL,'0.0000'),(3,'Authorize','credit.card@payment.com','-','2009-04-28','0.0000',NULL,NULL,'0.0000'),(4,'XmlFeed','xml@smartppcevo.com','-','0000-00-00','0.0000',NULL,NULL,'0.0000'),(6,'Guest','guest@orbitadserver.com','-','2009-04-28','0.0000',NULL,NULL,'0.0000');
/*!40000 ALTER TABLE `entities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entity_roles`
--

DROP TABLE IF EXISTS `entity_roles`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `entity_roles` (
  `id_entity` int(10) unsigned NOT NULL,
  `id_role` int(10) unsigned NOT NULL,
  `status` enum('activation','active','blocked','deleted') NOT NULL default 'active',
  PRIMARY KEY  (`id_entity`,`id_role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `entity_roles`
--

LOCK TABLES `entity_roles` WRITE;
/*!40000 ALTER TABLE `entity_roles` DISABLE KEYS */;
INSERT INTO `entity_roles` VALUES (1,1,'active'),(2,9,'active'),(3,9,'active'),(6,2,'active');
/*!40000 ALTER TABLE `entity_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feed_banned_countries`
--

DROP TABLE IF EXISTS `feed_banned_countries`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `feed_banned_countries` (
  `id_feed` int(10) unsigned NOT NULL,
  `id_country` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_feed`),
  KEY `country` (`id_country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `feed_banned_countries`
--

LOCK TABLES `feed_banned_countries` WRITE;
/*!40000 ALTER TABLE `feed_banned_countries` DISABLE KEYS */;
/*!40000 ALTER TABLE `feed_banned_countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feed_banned_domains`
--

DROP TABLE IF EXISTS `feed_banned_domains`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `feed_banned_domains` (
  `id_feed` int(10) unsigned NOT NULL,
  `id_domain_name` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_feed`),
  KEY `domain` (`id_domain_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `feed_banned_domains`
--

LOCK TABLES `feed_banned_domains` WRITE;
/*!40000 ALTER TABLE `feed_banned_domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `feed_banned_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feed_banned_ips`
--

DROP TABLE IF EXISTS `feed_banned_ips`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `feed_banned_ips` (
  `id_feed` int(10) unsigned NOT NULL,
  `ip_mask` char(15) NOT NULL,
  KEY `id_feed` (`id_feed`),
  KEY `mask` (`ip_mask`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `feed_banned_ips`
--

LOCK TABLES `feed_banned_ips` WRITE;
/*!40000 ALTER TABLE `feed_banned_ips` DISABLE KEYS */;
/*!40000 ALTER TABLE `feed_banned_ips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feed_banned_keywords`
--

DROP TABLE IF EXISTS `feed_banned_keywords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `feed_banned_keywords` (
  `id_feed` int(10) unsigned NOT NULL,
  `id_keyword` char(32) NOT NULL,
  PRIMARY KEY  (`id_feed`),
  KEY `keyword` (`id_keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `feed_banned_keywords`
--

LOCK TABLES `feed_banned_keywords` WRITE;
/*!40000 ALTER TABLE `feed_banned_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `feed_banned_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feed_clicks`
--

DROP TABLE IF EXISTS `feed_clicks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `feed_clicks` (
  `id_feed` int(10) unsigned NOT NULL,
  `ip_address` int(10) unsigned NOT NULL,
  `datetime` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_feed`,`ip_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `feed_clicks`
--

LOCK TABLES `feed_clicks` WRITE;
/*!40000 ALTER TABLE `feed_clicks` DISABLE KEYS */;
/*!40000 ALTER TABLE `feed_clicks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feeds`
--

DROP TABLE IF EXISTS `feeds`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `feeds` (
  `id_feed` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `class` varchar(100) NOT NULL,
  `affiliate_id_1` varchar(100) NOT NULL,
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `feeds`
--

LOCK TABLES `feeds` WRITE;
/*!40000 ALTER TABLE `feeds` DISABLE KEYS */;
/*!40000 ALTER TABLE `feeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feeds_to_request`
--

DROP TABLE IF EXISTS `feeds_to_request`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `feeds_to_request` (
  `name` varchar(50) NOT NULL default '0',
  PRIMARY KEY  (`name`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `feeds_to_request`
--

LOCK TABLES `feeds_to_request` WRITE;
/*!40000 ALTER TABLE `feeds_to_request` DISABLE KEYS */;
INSERT INTO `feeds_to_request` VALUES ('7Search.com'),('ABCSearch.com'),('Ah-ha.com(Enchance.com)'),('BlowSearch.com'),('BrainFox.com'),('EPilot.com'),('Findit-Quick.com'),('Findology.com'),('FindWhat.com(Miva)'),('GoClick.com'),('Kanoodle.com'),('LookSmart.com'),('Mamma.com'),('Mirago.com'),('Overture.com'),('RevenuePilot.com'),('RevQuest.com'),('Search123.com'),('SearchFeed.com'),('XmlRevenue.com');
/*!40000 ALTER TABLE `feeds_to_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `field_formats`
--

DROP TABLE IF EXISTS `field_formats`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `field_formats` (
  `id_field_format` tinyint(3) unsigned NOT NULL default '0',
  `locale` varchar(10) NOT NULL,
  `sprintf` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `field_formats`
--

LOCK TABLES `field_formats` WRITE;
/*!40000 ALTER TABLE `field_formats` DISABLE KEYS */;
/*!40000 ALTER TABLE `field_formats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `field_types`
--

DROP TABLE IF EXISTS `field_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `field_types` (
  `id_field_type` tinyint(3) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id_field_type`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `field_types`
--

LOCK TABLES `field_types` WRITE;
/*!40000 ALTER TABLE `field_types` DISABLE KEYS */;
INSERT INTO `field_types` VALUES (1,'string'),(2,'integer'),(3,'date'),(4,'time'),(5,'datetime'),(6,'float'),(7,'procent'),(8,'money'),(9,'bool'),(10,'translate');
/*!40000 ALTER TABLE `field_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fonts`
--

DROP TABLE IF EXISTS `fonts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fonts` (
  `id_font` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id_font`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fonts`
--

LOCK TABLES `fonts` WRITE;
/*!40000 ALTER TABLE `fonts` DISABLE KEYS */;
INSERT INTO `fonts` VALUES (1,'Tahoma'),(2,'Arial'),(3,'Helvetica');
/*!40000 ALTER TABLE `fonts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_action`
--

DROP TABLE IF EXISTS `fraud_action`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_action` (
  `id_action` int(10) unsigned NOT NULL auto_increment,
  `name` char(50) NOT NULL,
  PRIMARY KEY  (`id_action`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_action`
--

LOCK TABLES `fraud_action` WRITE;
/*!40000 ALTER TABLE `fraud_action` DISABLE KEYS */;
INSERT INTO `fraud_action` VALUES (1,'Show Ad & Pay'),(2,'Show Ad & Not To Pay'),(3,'Not To Show Ad'),(4,'Not To Show Ad & Block IP');
/*!40000 ALTER TABLE `fraud_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_allowed`
--

DROP TABLE IF EXISTS `fraud_allowed`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_allowed` (
  `id_fraud_allowed` int(11) unsigned NOT NULL auto_increment,
  `ip_start` int(11) unsigned NOT NULL,
  `ip_finish` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_fraud_allowed`),
  KEY `ip` (`ip_start`,`ip_finish`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_allowed`
--

LOCK TABLES `fraud_allowed` WRITE;
/*!40000 ALTER TABLE `fraud_allowed` DISABLE KEYS */;
/*!40000 ALTER TABLE `fraud_allowed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_clicks`
--

DROP TABLE IF EXISTS `fraud_clicks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_clicks` (
  `id_click` bigint(20) unsigned NOT NULL,
  `id_fraud_type` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_click`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_clicks`
--

LOCK TABLES `fraud_clicks` WRITE;
/*!40000 ALTER TABLE `fraud_clicks` DISABLE KEYS */;
/*!40000 ALTER TABLE `fraud_clicks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_domains`
--

DROP TABLE IF EXISTS `fraud_domains`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_domains` (
  `id_domain_name` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_domain_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_domains`
--

LOCK TABLES `fraud_domains` WRITE;
/*!40000 ALTER TABLE `fraud_domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `fraud_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_firewall`
--

DROP TABLE IF EXISTS `fraud_firewall`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_firewall` (
  `id_fraud_firewall` int(11) unsigned NOT NULL auto_increment,
  `ip_start` int(11) unsigned NOT NULL,
  `ip_finish` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_fraud_firewall`),
  KEY `ip` (`ip_start`,`ip_finish`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_firewall`
--

LOCK TABLES `fraud_firewall` WRITE;
/*!40000 ALTER TABLE `fraud_firewall` DISABLE KEYS */;
/*!40000 ALTER TABLE `fraud_firewall` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_groups`
--

DROP TABLE IF EXISTS `fraud_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_groups` (
  `id_fraud_group` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id_fraud_group`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_groups`
--

LOCK TABLES `fraud_groups` WRITE;
/*!40000 ALTER TABLE `fraud_groups` DISABLE KEYS */;
INSERT INTO `fraud_groups` VALUES (1,'Minimal group');
/*!40000 ALTER TABLE `fraud_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_ips`
--

DROP TABLE IF EXISTS `fraud_ips`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_ips` (
  `ip` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_ips`
--

LOCK TABLES `fraud_ips` WRITE;
/*!40000 ALTER TABLE `fraud_ips` DISABLE KEYS */;
/*!40000 ALTER TABLE `fraud_ips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_keywords`
--

DROP TABLE IF EXISTS `fraud_keywords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_keywords` (
  `id_keyword` char(32) NOT NULL,
  PRIMARY KEY  (`id_keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_keywords`
--

LOCK TABLES `fraud_keywords` WRITE;
/*!40000 ALTER TABLE `fraud_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `fraud_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_link_clicks`
--

DROP TABLE IF EXISTS `fraud_link_clicks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_link_clicks` (
  `id_link_click` bigint(20) unsigned NOT NULL,
  `id_fraud_type` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_link_click`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_link_clicks`
--

LOCK TABLES `fraud_link_clicks` WRITE;
/*!40000 ALTER TABLE `fraud_link_clicks` DISABLE KEYS */;
/*!40000 ALTER TABLE `fraud_link_clicks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_product_clicks`
--

DROP TABLE IF EXISTS `fraud_product_clicks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_product_clicks` (
  `id_product_click` bigint(20) unsigned NOT NULL,
  `id_fraud_type` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_product_click`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_product_clicks`
--

LOCK TABLES `fraud_product_clicks` WRITE;
/*!40000 ALTER TABLE `fraud_product_clicks` DISABLE KEYS */;
/*!40000 ALTER TABLE `fraud_product_clicks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_protections`
--

DROP TABLE IF EXISTS `fraud_protections`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_protections` (
  `id_fraud_protection` int(10) unsigned NOT NULL auto_increment,
  `id_fraud_group` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL default '',
  `status` enum('enabled','disabled') NOT NULL,
  `target` set('search','click') NOT NULL,
  `id_action` int(10) NOT NULL,
  `use_actions` enum('true','false') NOT NULL default 'true',
  `slug` varchar(20) NOT NULL,
  `version` varchar(8) NOT NULL,
  `config_path` varchar(50) NOT NULL,
  PRIMARY KEY  (`id_fraud_protection`),
  KEY `status` (`status`,`target`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_protections`
--

LOCK TABLES `fraud_protections` WRITE;
/*!40000 ALTER TABLE `fraud_protections` DISABLE KEYS */;
INSERT INTO `fraud_protections` VALUES (1,1,'Blocked Countries','disabled','search,click',3,'true','country','1.0','admin/banned_countries'),(2,1,'Search & Click Protection','disabled','click',3,'true','search_click','1.0','admin/search_and_click_protection'),(3,1,'\"Quick Click\" Protection','disabled','click',3,'true','quick_click','1.0','admin/quick_click_protection'),(4,1,'Anti-Proxy','disabled','search,click',3,'true','proxy','1.0','admin/anti_proxy_protection'),(5,1,'\"Quick Search\" Protection','disabled','search',3,'true','quick_search','1.0','admin/quick_search_protection'),(6,1,'Firewall Protection','disabled','search,click',3,'true','firewall','1.0','admin/firewall_protection');
/*!40000 ALTER TABLE `fraud_protections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_proxies`
--

DROP TABLE IF EXISTS `fraud_proxies`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_proxies` (
  `id_fraud_proxy` int(11) unsigned NOT NULL auto_increment,
  `ip_start` int(11) unsigned NOT NULL,
  `ip_finish` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_fraud_proxy`),
  KEY `ip` (`ip_start`,`ip_finish`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_proxies`
--

LOCK TABLES `fraud_proxies` WRITE;
/*!40000 ALTER TABLE `fraud_proxies` DISABLE KEYS */;
/*!40000 ALTER TABLE `fraud_proxies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_quick_search`
--

DROP TABLE IF EXISTS `fraud_quick_search`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_quick_search` (
  `ip` int(11) unsigned NOT NULL,
  `search_date` int(11) unsigned NOT NULL,
  KEY `idx` (`ip`,`search_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_quick_search`
--

LOCK TABLES `fraud_quick_search` WRITE;
/*!40000 ALTER TABLE `fraud_quick_search` DISABLE KEYS */;
/*!40000 ALTER TABLE `fraud_quick_search` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_referer_domains`
--

DROP TABLE IF EXISTS `fraud_referer_domains`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_referer_domains` (
  `id_fraud_referer_domain` int(10) unsigned NOT NULL auto_increment,
  `domain` varchar(200) NOT NULL default '',
  `domainhash` char(32) NOT NULL,
  PRIMARY KEY  (`id_fraud_referer_domain`),
  UNIQUE KEY `domainhash` (`domainhash`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_referer_domains`
--

LOCK TABLES `fraud_referer_domains` WRITE;
/*!40000 ALTER TABLE `fraud_referer_domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `fraud_referer_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_settings`
--

DROP TABLE IF EXISTS `fraud_settings`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_settings` (
  `id_fraud_protection` int(10) unsigned NOT NULL,
  `setting_name` char(30) NOT NULL,
  `setting_value` varchar(200) NOT NULL,
  `id_field_type` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id_fraud_protection`,`setting_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_settings`
--

LOCK TABLES `fraud_settings` WRITE;
/*!40000 ALTER TABLE `fraud_settings` DISABLE KEYS */;
INSERT INTO `fraud_settings` VALUES (2,'SearchClickIpMatch','true',9),(2,'ReferrerNonEmpty','false',9),(3,'MinimumIntervalSearchClick','6',2),(3,'MaximumIntervalSearchClick','60',2),(3,'TimePeriod','1',2),(3,'MaximumSearchNumber','10',2),(4,'block_transparent_clicks','false',9),(4,'allowed_proxy_clicks','false',9),(5,'TimePeriod','10',2),(5,'MaximumSearchNumber','1',2);
/*!40000 ALTER TABLE `fraud_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_types`
--

DROP TABLE IF EXISTS `fraud_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fraud_types` (
  `id_fraud_type` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id_fraud_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `fraud_types`
--

LOCK TABLES `fraud_types` WRITE;
/*!40000 ALTER TABLE `fraud_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `fraud_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `free_listing_rating_detailed`
--

DROP TABLE IF EXISTS `free_listing_rating_detailed`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `free_listing_rating_detailed` (
  `id_free_listing` int(10) unsigned NOT NULL,
  `id_surfer` int(10) unsigned NOT NULL,
  `rating_mark` tinyint(1) unsigned NOT NULL,
  `rating_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_free_listing`,`id_surfer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `free_listing_rating_detailed`
--

LOCK TABLES `free_listing_rating_detailed` WRITE;
/*!40000 ALTER TABLE `free_listing_rating_detailed` DISABLE KEYS */;
/*!40000 ALTER TABLE `free_listing_rating_detailed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `free_listings`
--

DROP TABLE IF EXISTS `free_listings`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `free_listings` (
  `id_free_listing` int(10) unsigned NOT NULL auto_increment,
  `id_surfer` int(10) unsigned NOT NULL,
  `id_directory` int(10) unsigned NOT NULL,
  `creation_date` datetime NOT NULL,
  `title` char(50) NOT NULL,
  `description` text NOT NULL,
  `description_2` text,
  `click_url` varchar(200) NOT NULL,
  `display_url` varchar(200) NOT NULL,
  `status` enum('active','passive','blocked') NOT NULL,
  `rating_sum` int(10) unsigned NOT NULL,
  `rated_count` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_free_listing`),
  KEY `surfer` (`id_surfer`),
  KEY `directory` (`id_directory`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `free_listings`
--

LOCK TABLES `free_listings` WRITE;
/*!40000 ALTER TABLE `free_listings` DISABLE KEYS */;
/*!40000 ALTER TABLE `free_listings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `free_listings_additional_fields`
--

DROP TABLE IF EXISTS `free_listings_additional_fields`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `free_listings_additional_fields` (
  `id_free_listings` int(10) unsigned NOT NULL,
  `id_additional_field` int(10) unsigned NOT NULL,
  `value` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_free_listings`,`id_additional_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `free_listings_additional_fields`
--

LOCK TABLES `free_listings_additional_fields` WRITE;
/*!40000 ALTER TABLE `free_listings_additional_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `free_listings_additional_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_channels`
--

DROP TABLE IF EXISTS `group_channels`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `group_channels` (
  `id_group` int(10) unsigned NOT NULL,
  `id_channel` int(10) unsigned NOT NULL,
  `id_program` int(11) NOT NULL,
  UNIQUE KEY `id` (`id_channel`,`id_group`),
  KEY `subprogram` (`id_program`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `group_channels`
--

LOCK TABLES `group_channels` WRITE;
/*!40000 ALTER TABLE `group_channels` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_channels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_keywords`
--

DROP TABLE IF EXISTS `group_keywords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `group_keywords` (
  `id_group` int(10) unsigned NOT NULL,
  `id_keyword` char(32) NOT NULL,
  `match_type` enum('broad','exact','not') NOT NULL default 'broad',
  `sites_bid` decimal(16,4) default NULL,
  `search_bid` decimal(16,4) default NULL,
  `intext_bid` decimal(16,4) default NULL,
  `parked_domains_bid` decimal(16,4) default NULL,
  `status` enum('active','paused','deleted') default 'active',
  PRIMARY KEY  (`id_group`,`id_keyword`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `group_keywords`
--

LOCK TABLES `group_keywords` WRITE;
/*!40000 ALTER TABLE `group_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_site_channels`
--

DROP TABLE IF EXISTS `group_site_channels`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `group_site_channels` (
  `id_group` int(10) unsigned NOT NULL,
  `id_site_channel` int(10) unsigned NOT NULL,
  `id_program` int(10) unsigned NOT NULL,
  `cost_text` decimal(16,4) unsigned NOT NULL,
  `cost_image` decimal(16,4) unsigned NOT NULL,
  `cost_richmedia` decimal(16,4) unsigned NOT NULL,
  `volume` int(10) unsigned NOT NULL,
  `avg_cost_text` decimal(16,4) unsigned NOT NULL,
  `avg_cost_image` decimal(16,4) unsigned NOT NULL,
  `avg_cost_richmedia` decimal(16,4) NOT NULL,
  `ad_type` set('text','image','richmedia') NOT NULL,
  `start_date_time` datetime default NULL,
  `end_date_time` datetime default NULL,
  `impressions` int(10) unsigned default NULL,
  `status` enum('active','completed','paused','trouble','unpaid','deleted') NOT NULL default 'unpaid',
  `is_autorenew` enum('true','false') default 'false',
  `clicks` int(10) unsigned NOT NULL default '0',
  `current_impressions` int(10) unsigned NOT NULL default '0',
  `id_group_site_channel` int(10) unsigned NOT NULL auto_increment,
  `spent` decimal(16,4) unsigned NOT NULL,
  `pay_escrow` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`id_group_site_channel`),
  KEY `idp` (`id_program`,`ad_type`),
  KEY `index` (`id_group`,`id_site_channel`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `group_site_channels`
--

LOCK TABLES `group_site_channels` WRITE;
/*!40000 ALTER TABLE `group_site_channels` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_site_channels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_sites`
--

DROP TABLE IF EXISTS `group_sites`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `group_sites` (
  `id_group_site` int(10) unsigned NOT NULL auto_increment,
  `id_group` int(10) unsigned NOT NULL,
  `id_site` int(10) unsigned NOT NULL,
  `cpc` decimal(16,4) unsigned default NULL COMMENT 'NULL - mean that default bid is used',
  `cpc_image` decimal(16,4) unsigned default NULL,
  `impressions` int(10) unsigned NOT NULL,
  `clicks` int(10) unsigned NOT NULL,
  `spent` decimal(16,4) unsigned NOT NULL,
  `status` enum('active','deleted','paused','trouble','unpaid') NOT NULL default 'active',
  PRIMARY KEY  (`id_group_site`),
  KEY `id_group` (`id_group`),
  KEY `id_site` (`id_site`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `group_sites`
--

LOCK TABLES `group_sites` WRITE;
/*!40000 ALTER TABLE `group_sites` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  `default_cpc` decimal(16,4) unsigned default NULL,
  `default_cpc_image` decimal(16,4) unsigned default NULL,
  `target` set('sites','searches','domains','allsites','intext') NOT NULL default 'sites',
  `keyword_sites_bid` decimal(16,4) unsigned NOT NULL,
  `keyword_parked_domains_bid` decimal(16,4) unsigned NOT NULL,
  `keyword_search_bid` decimal(16,4) unsigned NOT NULL,
  `keyword_intext_bid` decimal(16,4) unsigned NOT NULL,
  `text_ads` int(10) unsigned NOT NULL default '0',
  `image_ads` int(10) unsigned NOT NULL default '0',
  `richmedia_ads` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_group`),
  KEY `campaign` (`id_campaign`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `images`
--

DROP TABLE IF EXISTS `images`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `images`
--

LOCK TABLES `images` WRITE;
/*!40000 ALTER TABLE `images` DISABLE KEYS */;
/*!40000 ALTER TABLE `images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_logs`
--

DROP TABLE IF EXISTS `import_logs`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `import_logs` (
  `id_log` int(10) unsigned NOT NULL auto_increment,
  `server` tinyint(3) unsigned NOT NULL,
  `file` varchar(1024) NOT NULL,
  `filehash` char(32) NOT NULL,
  `datetime` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_log`),
  KEY `server` (`server`,`filehash`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `import_logs`
--

LOCK TABLES `import_logs` WRITE;
/*!40000 ALTER TABLE `import_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_sessions`
--

DROP TABLE IF EXISTS `import_sessions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `import_sessions` (
  `server` int(10) unsigned NOT NULL,
  `session_id` char(32) NOT NULL,
  `datetime` int(10) NOT NULL,
  PRIMARY KEY  (`server`,`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `import_sessions`
--

LOCK TABLES `import_sessions` WRITE;
/*!40000 ALTER TABLE `import_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `installed_plugins`
--

DROP TABLE IF EXISTS `installed_plugins`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `installed_plugins` (
  `name` varchar(64) NOT NULL,
  `solution` enum('admarket','adserver','search') default NULL,
  `status` enum('installed','deleted','broken') NOT NULL default 'installed',
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `depending` varchar(255) default NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `installed_plugins`
--

LOCK TABLES `installed_plugins` WRITE;
/*!40000 ALTER TABLE `installed_plugins` DISABLE KEYS */;
/*!40000 ALTER TABLE `installed_plugins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_locations`
--

DROP TABLE IF EXISTS `ip_locations`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `ip_locations` (
  `ip_start` int(10) unsigned NOT NULL,
  `ip_end` int(10) unsigned NOT NULL,
  `id_location` int(10) unsigned NOT NULL,
  `accuracy` enum('country','region','city','zip') NOT NULL,
  UNIQUE KEY `ip` (`ip_start`,`ip_end`),
  KEY `id_location` (`id_location`),
  KEY `accuracy` (`accuracy`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `ip_locations`
--

LOCK TABLES `ip_locations` WRITE;
/*!40000 ALTER TABLE `ip_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `ip_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `keywords`
--

DROP TABLE IF EXISTS `keywords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `keywords` (
  `id_keyword` char(32) NOT NULL,
  `keyword` varchar(100) NOT NULL,
  PRIMARY KEY  (`id_keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `keywords`
--

LOCK TABLES `keywords` WRITE;
/*!40000 ALTER TABLE `keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `languages` (
  `iso` char(3) NOT NULL,
  `name` char(20) NOT NULL,
  `unicode_name` tinyblob,
  `banned` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`iso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES ('AA','Afar','Afaraf','false'),('AB','Abkhazian','Аҧсуа','false'),('AE','Avestan','avesta','false'),('AF','Afrikaans','Afrikaans','false'),('AK','Akan','Akan','false'),('AM','Amharic','','false'),('AN','Aragonese','Aragonés','false'),('AR','Arabic','العربية','false'),('AS','Assamese','','false'),('AV','Avaric','авар мацӀ; магӀарул мацӀ','false'),('AY','Aymara','aymar aru','false'),('AZ','Azerbaijani','azərbaycan dili','false'),('BA','Bashkir','башҡорт теле','false'),('BE','Belarusian','Беларуская','false'),('BG','Bulgarian','български език','false'),('BH','Bihari','','false'),('BI','Bislama','Bislama','false'),('BM','Bambara','bamanankan','false'),('BN','Bengali','','false'),('BO','Tibetan','','false'),('BR','Breton','brezhoneg','false'),('BS','Bosnian','bosanski jezik','false'),('CA','Catalan','Català','false'),('CE','Chechen','нохчийн мотт','false'),('CH','Chamorro','Chamoru','false'),('CO','Corsican','corsu; lingua corsa','false'),('CR','Cree','ᓀᐦᐃᔭᐍᐏᐣ','false'),('CS','Czech','česky; čeština','false'),('CU','Church Slavic','ѩзыкъ словѣньскъ','false'),('CV','Chuvash','чӑваш чӗлхи','false'),('CY','Welsh','Cymraeg','false'),('DA','Danish','dansk','false'),('DE','German','Deutsch','false'),('DV','Divehi','','false'),('DZ','Dzongkha','','false'),('EE','Ewe','Ɛʋɛgbɛ','false'),('EL','Greek','Ελληνικά','false'),('EN','English','English','false'),('EO','Esperanto','Esperanto','false'),('ES','Spanish','Español; castellano','false'),('ET','Estonian','eesti; eesti keel','false'),('EU','Basque','euskara; euskera','false'),('FA','Persian','فارسی','false'),('FF','Fulah','Fulfulde','false'),('FI','Finnish','suomi; suomen kieli','false'),('FJ','Fijian','vosa Vakaviti','false'),('FO','Faroese','Føroyskt','false'),('FR','French','Français; langue française','false'),('FY','Western Frisian','Frysk','false'),('GA','Irish','Gaeilge','false'),('GD','Scottish Gaelic','Gàidhlig','false'),('GL','Galician','Galego','false'),('GN','Guaran?','Avañe\'ẽ','false'),('GU','Gujarati','','false'),('GV','Manx','Gaelg; Gailck','false'),('HA','Hausa','هَوُسَ','false'),('HE','Hebrew','עברית','false'),('HI','Hindi','','false'),('HO','Hiri Motu','Hiri Motu','false'),('HR','Croatian','Hrvatski','false'),('HT','Haitian','Kreyòl ayisyen','false'),('HU','Hungarian','Magyar','false'),('HY','Armenian','Հայերեն','false'),('HZ','Herero','Otjiherero','false'),('IA','Interlingua','Interlingua','false'),('ID','Indonesian','Bahasa Indonesia','false'),('IE','Interlingue','Interlingue','false'),('IG','Igbo','Igbo','false'),('II','Sichuan Yi','','false'),('IK','Inupiaq','Iñupiaq; Iñupiatun','false'),('IO','Ido','Ido','false'),('IS','Icelandic','Íslenska','false'),('IT','Italian','Italiano','false'),('IU','Inuktitut','ᐃᓄᒃᑎᑐᑦ','false'),('JA','Japanese','','false'),('JV','Javanese','basa Jawa','false'),('KA','Georgian','ქართული','false'),('KG','Kongo','KiKongo','false'),('KI','Kikuyu','Gĩkũyũ','false'),('KJ','Kwanyama','Kuanyama','false'),('KK','Kazakh','Қазақ тілі','false'),('KL','Kalaallisut','kalaallisut; kalaallit oqaasii','false'),('KM','Khmer','','false'),('KN','Kannada','','false'),('KO','Korean','','false'),('KR','Kanuri','Kanuri','false'),('KS','Kashmiri','','false'),('KU','Kurdish','Kurdî; كوردی‎','false'),('KV','Komi','коми кыв','false'),('KW','Cornish','Kernewek','false'),('KY','Kirghiz','кыргыз тили','false'),('LA','Latin','latine; lingua latina','false'),('LB','Luxembourgish','Lëtzebuergesch','false'),('LG','Ganda','Luganda','false'),('LI','Limburgish','Limburgs','false'),('LN','Lingala','Lingála','false'),('LO','Lao','ພາສາລາວ','false'),('LT','Lithuanian','lietuvių kalba','false'),('LU','Luba-Katanga','','false'),('LV','Latvian','latviešu valoda','false'),('MG','Malagasy','Malagasy fiteny','false'),('MH','Marshallese','Kajin M̧ajeļ','false'),('MI','M?ori','te reo Māori','false'),('MK','Macedonian','македонски јазик','false'),('ML','Malayalam','','false'),('MN','Mongolian','Монгол','false'),('MR','Marathi','','false'),('MS','Malay','bahasa Melayu; بهاس ملايو‎','false'),('MT','Maltese','Malti','false'),('MY','Burmese','','false'),('NA','Nauru','Ekakairũ Naoero','false'),('NB','Norwegian Bokm?l','Norsk bokmål','false'),('ND','North Ndebele','isiNdebele','false'),('NE','Nepali','','false'),('NG','Ndonga','Owambo','false'),('NL','Dutch','Nederlands','false'),('NN','Norwegian Nynorsk','Norsk nynorsk','false'),('NO','Norwegian','Norsk','false'),('NR','South Ndebele','isiNdebele','false'),('NV','Navajo','Diné bizaad; Dinékʼehǰí','false'),('NY','Chichewa','chiCheŵa; chinyanja','false'),('OC','Occitan','Occitan','false'),('OJ','Ojibwa','ᐊᓂᔑᓈᐯᒧᐎᓐ','false'),('OM','Oromo','Afaan Oromoo','false'),('OR','Oriya','','false'),('OS','Ossetian','Ирон æвзаг','false'),('PA','Panjabi','','false'),('PI','P?li','','false'),('PL','Polish','polski','false'),('PS','Pashto','پښتو','false'),('PT','Portuguese','Português','false'),('QU','Quechua','Runa Simi; Kichwa','false'),('RM','Raeto-Romance','rumantsch grischun','false'),('RN','Kirundi','kiRundi','false'),('RO','Romanian','română','false'),('RU','Russian','русский язык','false'),('RW','Kinyarwanda','Ikinyarwanda','false'),('SA','Sanskrit','','false'),('SC','Sardinian','sardu','false'),('SD','Sindhi','','false'),('SE','Northern Sami','Davvisámegiella','false'),('SG','Sango','yângâ tî sängö','false'),('SH','Serbo-Croatian','Srpskohrvatski; Српскохрватски','false'),('SI','Sinhala','','false'),('SK','Slovak','slovenčina','false'),('SL','Slovenian','slovenščina','false'),('SM','Samoan','gagana fa\'a Samoa','false'),('SN','Shona','chiShona','false'),('SO','Somali','Soomaaliga; af Soomaali','false'),('SQ','Albanian','Shqip','false'),('SR','Serbian','српски језик','false'),('SS','Swati','SiSwati','false'),('ST','Southern Sotho','Sesotho','false'),('SU','Sundanese','Basa Sunda','false'),('SV','Swedish','svenska','false'),('SW','Swahili','Kiswahili','false'),('TA','Tamil','','false'),('TE','Telugu','','false'),('TG','Tajik','тоҷикӣ; toğikī; تاجیکی‎','false'),('TH','Thai','ไทย','false'),('TI','Tigrinya','','false'),('TK','Turkmen','Türkmen; Түркмен','false'),('TL','Tagalog','Tagalog','false'),('TN','Tswana','Setswana','false'),('TO','Tonga','faka Tonga','false'),('TR','Turkish','Türkçe','false'),('TS','Tsonga','Xitsonga','false'),('TT','Tatar','татарча; tatarça; تاتارچا‎','false'),('TW','Twi','Twi','false'),('TY','Tahitian','Reo Mā`ohi','false'),('UG','Uighur','Uyƣurqə; ئۇيغۇرچە‎','false'),('UK','Ukrainian','Українська','false'),('UR','Urdu','اردو','false'),('UZ','Uzbek','O\'zbek; Ўзбек; أۇزبېك‎','false'),('VE','Venda','Tshivenḓa','false'),('VI','Vietnamese','Tiếng Việt','false'),('VO','Volap?k','Volapük','false'),('WA','Walloon','Walon','false'),('WO','Wolof','Wollof','false'),('XH','Xhosa','isiXhosa','false'),('YI','Yiddish','ייִדיש','false'),('YO','Yoruba','Yorùbá','false'),('ZA','Zhuang','Saɯ cueŋƅ; Saw cuengh','false'),('ZH','Chinese','','false'),('ZU','Zulu','isiZulu','false'),('UN','Unknown','','false');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `layouts`
--

DROP TABLE IF EXISTS `layouts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `layouts`
--

LOCK TABLES `layouts` WRITE;
/*!40000 ALTER TABLE `layouts` DISABLE KEYS */;
/*!40000 ALTER TABLE `layouts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `link_clicks`
--

DROP TABLE IF EXISTS `link_clicks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `link_clicks` (
  `id_link_click` bigint(20) unsigned NOT NULL auto_increment,
  `id_link` int(10) unsigned NOT NULL,
  `datetime` timestamp NOT NULL default '0000-00-00 00:00:00',
  `price` decimal(16,4) NOT NULL,
  `remote_addr` int(10) unsigned NOT NULL,
  `id_country` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_link_click`),
  KEY `ad` (`id_link`),
  KEY `dateime` (`datetime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `link_clicks`
--

LOCK TABLES `link_clicks` WRITE;
/*!40000 ALTER TABLE `link_clicks` DISABLE KEYS */;
/*!40000 ALTER TABLE `link_clicks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `links` (
  `id_link` int(10) unsigned NOT NULL auto_increment,
  `id_group` int(10) unsigned NOT NULL,
  `status` enum('active','passive','bloked') NOT NULL,
  `id_category` int(10) unsigned NOT NULL,
  `price` decimal(16,4) NOT NULL,
  `content_type` char(50) NOT NULL,
  `title` char(50) NOT NULL,
  `description` char(100) NOT NULL,
  `description_2` char(100) NOT NULL,
  `display_url` char(50) NOT NULL,
  `url` char(100) NOT NULL,
  `impression_start` datetime NOT NULL,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_link`),
  KEY `id_group` (`id_group`),
  KEY `status` (`status`),
  KEY `category` (`id_category`),
  KEY `price` (`price`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `links`
--

LOCK TABLES `links` WRITE;
/*!40000 ALTER TABLE `links` DISABLE KEYS */;
/*!40000 ALTER TABLE `links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `listing_bookmarks`
--

DROP TABLE IF EXISTS `listing_bookmarks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `listing_bookmarks` (
  `id_bookmark` int(10) unsigned NOT NULL auto_increment,
  `id_surfer` int(10) unsigned NOT NULL,
  `url_hash` char(32) NOT NULL,
  `status` enum('1') NOT NULL,
  `creation_date` timestamp NOT NULL default '0000-00-00 00:00:00',
  `edit_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `edited_count` int(10) unsigned NOT NULL,
  `rated_count` int(10) unsigned NOT NULL,
  `rating_sum` int(10) unsigned NOT NULL,
  `title` char(50) NOT NULL,
  `description` text NOT NULL,
  `url` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_bookmark`),
  KEY `surfer` (`id_surfer`),
  KEY `url` (`url_hash`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `listing_bookmarks`
--

LOCK TABLES `listing_bookmarks` WRITE;
/*!40000 ALTER TABLE `listing_bookmarks` DISABLE KEYS */;
/*!40000 ALTER TABLE `listing_bookmarks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `listing_comments`
--

DROP TABLE IF EXISTS `listing_comments`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `listing_comments` (
  `id_comment` int(10) unsigned NOT NULL auto_increment,
  `id_surfer` int(10) unsigned NOT NULL,
  `id_comment_parent` tinyint(3) unsigned NOT NULL,
  `url_hash` char(32) NOT NULL,
  `message` text NOT NULL,
  `status` enum('1') NOT NULL,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `edit_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `edited_count` int(10) unsigned NOT NULL,
  `rated_count` int(10) unsigned NOT NULL,
  `rating_sum` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_comment`),
  KEY `surfer` (`id_surfer`),
  KEY `parent` (`id_comment_parent`),
  KEY `url` (`url_hash`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `listing_comments`
--

LOCK TABLES `listing_comments` WRITE;
/*!40000 ALTER TABLE `listing_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `listing_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `listing_files`
--

DROP TABLE IF EXISTS `listing_files`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `listing_files` (
  `id_file` int(10) unsigned NOT NULL auto_increment,
  `id_surfer` int(10) unsigned NOT NULL,
  `url_hash` char(32) NOT NULL,
  `status` enum('1') NOT NULL,
  `creation_date` timestamp NOT NULL default '0000-00-00 00:00:00',
  `edit_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `edited_count` int(10) unsigned NOT NULL,
  `rated_count` int(10) unsigned NOT NULL,
  `rating_sum` int(10) unsigned NOT NULL,
  `description` text NOT NULL,
  `path` varchar(200) NOT NULL,
  `size` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_file`),
  KEY `surfer` (`id_surfer`),
  KEY `url` (`url_hash`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `listing_files`
--

LOCK TABLES `listing_files` WRITE;
/*!40000 ALTER TABLE `listing_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `listing_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `listing_rating_detailed`
--

DROP TABLE IF EXISTS `listing_rating_detailed`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `listing_rating_detailed` (
  `id_listing` int(10) unsigned NOT NULL default '0',
  `id_surfer` int(10) unsigned NOT NULL,
  `rating_mark` tinyint(1) unsigned NOT NULL,
  `rating_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_listing`,`id_surfer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `listing_rating_detailed`
--

LOCK TABLES `listing_rating_detailed` WRITE;
/*!40000 ALTER TABLE `listing_rating_detailed` DISABLE KEYS */;
/*!40000 ALTER TABLE `listing_rating_detailed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `listings`
--

DROP TABLE IF EXISTS `listings`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `listings` (
  `id_listing` int(10) unsigned NOT NULL auto_increment,
  `id_group` int(10) unsigned NOT NULL,
  `id_keyword` char(32) NOT NULL,
  `bid` decimal(16,4) NOT NULL,
  `status` enum('active','passive','blocked') NOT NULL,
  PRIMARY KEY  (`id_listing`),
  KEY `id_group` (`id_group`),
  KEY `keyword` (`id_keyword`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `listings`
--

LOCK TABLES `listings` WRITE;
/*!40000 ALTER TABLE `listings` DISABLE KEYS */;
/*!40000 ALTER TABLE `listings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `listings_additional_fields`
--

DROP TABLE IF EXISTS `listings_additional_fields`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `listings_additional_fields` (
  `id_listings` int(10) unsigned NOT NULL,
  `id_additional_field` int(10) unsigned NOT NULL,
  `value` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_listings`,`id_additional_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `listings_additional_fields`
--

LOCK TABLES `listings_additional_fields` WRITE;
/*!40000 ALTER TABLE `listings_additional_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `listings_additional_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locale_settings`
--

DROP TABLE IF EXISTS `locale_settings`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `locale_settings`
--

LOCK TABLES `locale_settings` WRITE;
/*!40000 ALTER TABLE `locale_settings` DISABLE KEYS */;
INSERT INTO `locale_settings` VALUES ('en_US','m.d.Y','%m.%d.%Y','h:i A','%I:%M %p','$%','2.,',7),('ru_RU','d.m.Y','%d.%m.%Y','H:i','%R','$%','2, ',1);
/*!40000 ALTER TABLE `locale_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locales`
--

DROP TABLE IF EXISTS `locales`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `locales`
--

LOCK TABLES `locales` WRITE;
/*!40000 ALTER TABLE `locales` DISABLE KEYS */;
INSERT INTO `locales` VALUES ('en_US','English','GB','gb.png','false','<%FLAG%>','enabled','ltr','m.d.Y','%m.%d.%Y','H:i','%H:%i'),('ru_RU','Русский','RU','ru.png','false','<%FLAG%>','enabled','ltr','d-m-Y','%d-%m-%Y','H:i','%H:%i');
/*!40000 ALTER TABLE `locales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logo_images`
--

DROP TABLE IF EXISTS `logo_images`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `logo_images` (
  `id_logo_image` int(10) unsigned NOT NULL auto_increment,
  `id_category` int(10) unsigned NOT NULL,
  `id_entity_publisher` int(10) unsigned NOT NULL,
  `path` varchar(200) NOT NULL,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_logo_image`),
  KEY `publisher` (`id_entity_publisher`),
  KEY `category` (`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `logo_images`
--

LOCK TABLES `logo_images` WRITE;
/*!40000 ALTER TABLE `logo_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `logo_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mails`
--

DROP TABLE IF EXISTS `mails`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `mails` (
  `id_mail` int(10) unsigned NOT NULL auto_increment,
  `id_entity_from` int(10) unsigned NOT NULL,
  `subject` char(100) NOT NULL,
  `message` text,
  `creation_date` datetime NOT NULL,
  `status` enum('queuing','send') default 'queuing',
  `type` enum('text','html') NOT NULL default 'html',
  PRIMARY KEY  (`id_mail`),
  KEY `status` (`status`),
  KEY `id_from` (`id_entity_from`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `mails`
--

LOCK TABLES `mails` WRITE;
/*!40000 ALTER TABLE `mails` DISABLE KEYS */;
/*!40000 ALTER TABLE `mails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mails_target`
--

DROP TABLE IF EXISTS `mails_target`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `mails_target` (
  `id_mail` int(10) unsigned NOT NULL,
  `id_entity_to` int(10) unsigned NOT NULL,
  `reply` enum('false','true') NOT NULL default 'false',
  `readed` enum('false','true') NOT NULL default 'false',
  PRIMARY KEY  (`id_mail`,`id_entity_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `mails_target`
--

LOCK TABLES `mails_target` WRITE;
/*!40000 ALTER TABLE `mails_target` DISABLE KEYS */;
/*!40000 ALTER TABLE `mails_target` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_comment_ratings`
--

DROP TABLE IF EXISTS `member_comment_ratings`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `member_comment_ratings` (
  `id_rating` int(10) unsigned NOT NULL auto_increment,
  `id_member` int(10) unsigned NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `id_comment` int(10) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_rating`),
  KEY `id_surfer` (`id_member`),
  KEY `datetime` (`datetime`),
  KEY `id_comment` (`id_comment`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `member_comment_ratings`
--

LOCK TABLES `member_comment_ratings` WRITE;
/*!40000 ALTER TABLE `member_comment_ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_comment_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_comments`
--

DROP TABLE IF EXISTS `member_comments`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `member_comments` (
  `id_comment` int(10) unsigned NOT NULL auto_increment,
  `id_parent` int(10) unsigned default NULL,
  `id_search_type` int(10) unsigned NOT NULL,
  `id_member` int(10) unsigned NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `id_url` char(32) NOT NULL default '',
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `status` enum('active','paused','deleted') NOT NULL default 'active',
  `url` varchar(200) default NULL,
  `num_ratings` mediumint(8) NOT NULL default '0',
  `sum_ratings` mediumint(8) NOT NULL default '0',
  `subject` varchar(200) NOT NULL,
  `message` text,
  PRIMARY KEY  (`id_comment`),
  KEY `id_parent` (`id_parent`),
  KEY `urlhash` (`id_url`),
  KEY `datetime` (`datetime`),
  KEY `status` (`status`),
  KEY `id_surfer` (`id_member`),
  KEY `id_url` (`id_url`),
  KEY `id_search_type` (`id_search_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `member_comments`
--

LOCK TABLES `member_comments` WRITE;
/*!40000 ALTER TABLE `member_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_favorites`
--

DROP TABLE IF EXISTS `member_favorites`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `member_favorites` (
  `id_search_type` int(10) unsigned NOT NULL,
  `id_member` int(10) unsigned NOT NULL,
  `id_url` char(32) NOT NULL,
  `class` varchar(128) NOT NULL,
  `params` text,
  PRIMARY KEY  (`id_search_type`,`id_member`,`id_url`),
  KEY `id_member` (`id_member`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `member_favorites`
--

LOCK TABLES `member_favorites` WRITE;
/*!40000 ALTER TABLE `member_favorites` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_ratings`
--

DROP TABLE IF EXISTS `member_ratings`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `member_ratings` (
  `id_rating` int(10) unsigned NOT NULL auto_increment,
  `id_search_type` int(10) unsigned NOT NULL,
  `id_member` int(10) unsigned NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `id_url` char(32) NOT NULL default '',
  `rating` tinyint(3) unsigned NOT NULL,
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_rating`),
  KEY `id_surfer` (`id_member`),
  KEY `datetime` (`datetime`),
  KEY `id_url` (`id_url`),
  KEY `id_search_type` (`id_search_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `member_ratings`
--

LOCK TABLES `member_ratings` WRITE;
/*!40000 ALTER TABLE `member_ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_tab_boxes`
--

DROP TABLE IF EXISTS `member_tab_boxes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `member_tab_boxes` (
  `id_tab_box` int(10) unsigned NOT NULL auto_increment,
  `id_box` int(10) unsigned NOT NULL,
  `id_tab` int(10) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `column` tinyint(3) unsigned NOT NULL,
  `position` tinyint(3) unsigned NOT NULL,
  `state` enum('minimized','maximized') NOT NULL default 'maximized',
  `options` text,
  PRIMARY KEY  (`id_tab_box`),
  KEY `id_box` (`id_box`),
  KEY `id_tab` (`id_tab`),
  KEY `column_position` (`column`,`position`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `member_tab_boxes`
--

LOCK TABLES `member_tab_boxes` WRITE;
/*!40000 ALTER TABLE `member_tab_boxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_tab_boxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_tabs`
--

DROP TABLE IF EXISTS `member_tabs`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `member_tabs` (
  `id_tab` int(10) unsigned NOT NULL auto_increment,
  `id_entity` int(10) unsigned NOT NULL,
  `id_layout` int(10) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `position` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id_tab`),
  KEY `id_surfer` (`id_entity`),
  KEY `position` (`position`),
  KEY `id_layout` (`id_layout`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `member_tabs`
--

LOCK TABLES `member_tabs` WRITE;
/*!40000 ALTER TABLE `member_tabs` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_tabs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `members` (
  `id_entity_member` int(10) unsigned NOT NULL auto_increment,
  `photo` varchar(200) NOT NULL,
  `id_country` int(10) unsigned NOT NULL,
  `last_activity` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `birthday` date NOT NULL,
  `id_color_scheme` int(10) unsigned NOT NULL,
  `boxes_set` set('1') NOT NULL,
  `id_theme` int(10) unsigned default NULL,
  `sex` varchar(25) default NULL,
  PRIMARY KEY  (`id_entity_member`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
INSERT INTO `menu_items` VALUES (1,1,NULL,0,'Main','admin/dashboard','true'),(2,1,1,0,'Dashboard','admin/dashboard','true'),(3,1,1,100,'System Settings','admin/system_settings','true'),(4,1,NULL,100,'Reports','admin/reports_center','true'),(5,1,4,0,'Reports Center','admin/reports_center','true'),(6,1,4,100,'Global Reports','admin/global_reports','true'),(11,1,NULL,200,'Ad Serving','admin/manage_advertisers','true'),(14,1,11,600,'Manage Feeds','admin/manage_feeds','true'),(15,1,NULL,1000,'Settings','admin/manage_payment_gateways','true'),(17,1,15,0,'Manage Payment Gateways','admin/manage_payment_gateways','true'),(18,1,15,100,'Manage Categories','admin/manage_categories','true'),(20,1,15,200,'Fraud Protection','admin/fraud_protection','true'),(36,1,NULL,1100,'Tools','admin/news','true'),(37,1,36,0,'News','admin/news','true'),(38,1,36,100,'Mail','admin/mail','true'),(39,8,NULL,0,'Admin','admin/login','true'),(40,8,39,0,'Login','admin/login','true'),(41,8,39,100,'Forgot Password','guest_admin/forgot_password','true'),(84,3,NULL,0,'Main','advertiser/dashboard','true'),(85,3,84,0,'Dashboard','advertiser/dashboard','true'),(86,3,84,100,'Account Settings','advertiser/account_settings','true'),(87,3,NULL,100,'Reports','advertiser/reports_center','true'),(88,3,87,0,'Reports Center','advertiser/reports_center','true'),(89,3,NULL,200,'Ad Serving','advertiser/manage_ads','true'),(90,3,89,0,'Manage Ads','advertiser/manage_ads','true'),(91,3,NULL,300,'Payment','advertiser/add_funds','true'),(92,3,91,0,'Add Funds','advertiser/add_funds','true'),(117,2,116,100,'Site Directory','guest/site_directory','true'),(105,2,104,0,'Login','advertiser/login','true'),(106,2,104,100,'Forgot Password','guest/forgot_password','true'),(97,1,4,200,'Ad Serving Reports','admin/ad_serving_reports','true'),(98,1,11,100,'Manage Advertisers','admin/manage_advertisers','true'),(99,1,NULL,150,'Ad Placing','admin/manage_sites_channels','true'),(100,1,99,0,'Manage Sites/Channels','admin/manage_sites_channels','true'),(101,1,99,100,'Color Palettes','admin/color_palettes','true'),(102,1,99,200,'Targeting Groups','admin/manage_targeting_groups','true'),(103,8,NULL,0,'Admin','admin/login','false'),(107,2,104,200,'Sign Up','advertiser/sign_up','true'),(108,2,NULL,0,'Home','guest/home','true'),(109,2,108,100,'Welcome','guest/home','true'),(110,2,108,200,'Terms & Services','guest/terms','true'),(111,2,108,300,'Privacy Policy','guest/policy','true'),(112,2,NULL,500,'Contact Us','guest/contact_us','true'),(113,2,112,100,'Send Message','guest/contact_us','true'),(116,2,NULL,600,'Guest Site Directory','guest/site_directory','true'),(104,2,NULL,40,'Advertiser','advertiser/login','true'),(118,3,NULL,500,'Advertiser Site Directory','advertiser/site_directory','true'),(119,3,118,0,'Site Directory','advertiser/site_directory','true'),(496,1,11,700,'Default Bids','admin/default_bids','true'),(21,1,15,300,'Templates','admin/templates','true'),(513,1,11,200,'Campaign Capping','admin/manage_campains_capping','true'),(514,1,99,300,'Tags','admin/manage_tags','true'),(517,1,15,400,'Channels dimensions','admin/channel_dimensions','true');
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meta_keywords`
--

DROP TABLE IF EXISTS `meta_keywords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `meta_keywords` (
  `id_meta_keyword` int(10) unsigned NOT NULL auto_increment,
  `meta_keyword` varchar(100) NOT NULL,
  PRIMARY KEY  (`id_meta_keyword`),
  UNIQUE KEY `keyword` (`meta_keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `meta_keywords`
--

LOCK TABLES `meta_keywords` WRITE;
/*!40000 ALTER TABLE `meta_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `meta_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `money_flows`
--

DROP TABLE IF EXISTS `money_flows`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `money_flows` (
  `id_flow` int(10) unsigned NOT NULL auto_increment,
  `id_entity_receipt` int(10) unsigned NOT NULL,
  `id_entity_expense` int(10) unsigned NOT NULL,
  `flow_date` datetime NOT NULL,
  `value` decimal(16,4) NOT NULL,
  `flow_program` enum('deposit','withdraw','move','click','program','deduction','check','return','denied') NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `money_flows`
--

LOCK TABLES `money_flows` WRITE;
/*!40000 ALTER TABLE `money_flows` DISABLE KEYS */;
/*!40000 ALTER TABLE `money_flows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_categories`
--

DROP TABLE IF EXISTS `news_categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `news_categories` (
  `id_news_category` int(10) unsigned NOT NULL auto_increment,
  `id_news_category_parent` int(10) unsigned NOT NULL,
  `name` char(50) NOT NULL,
  PRIMARY KEY  (`id_news_category`),
  KEY `parent` (`id_news_category_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `news_categories`
--

LOCK TABLES `news_categories` WRITE;
/*!40000 ALTER TABLE `news_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_directory_plugin`
--

DROP TABLE IF EXISTS `news_directory_plugin`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `news_directory_plugin` (
  `id_news_category` int(10) unsigned NOT NULL,
  `id_news_feed` int(10) unsigned NOT NULL,
  `id_surfer` int(10) unsigned NOT NULL,
  `title` char(50) NOT NULL,
  `rss_url` varchar(200) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id_news_feed`),
  KEY `surfer` (`id_surfer`),
  KEY `category` (`id_news_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `news_directory_plugin`
--

LOCK TABLES `news_directory_plugin` WRITE;
/*!40000 ALTER TABLE `news_directory_plugin` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_directory_plugin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operating_systems`
--

DROP TABLE IF EXISTS `operating_systems`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `operating_systems` (
  `name` varchar(20) NOT NULL default '',
  `title` varchar(50) default NULL,
  `position` int(10) unsigned NOT NULL,
  `regexp` varchar(255) NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `operating_systems`
--

LOCK TABLES `operating_systems` WRITE;
/*!40000 ALTER TABLE `operating_systems` DISABLE KEYS */;
INSERT INTO `operating_systems` VALUES ('win_7','Windows 7',1,'windows nt 6\\.1'),('win_vista','Windows Vista',2,'windows nt 6\\.0'),('win_xp','Windows XP',3,'windows nt 5\\.1'),('win_2k3','Windows 2003',4,'windows nt 5\\.2'),('win_2k','Windows 2000',5,'windows nt 5\\.0'),('win_4.0','Windows NT 4.0',6,'windows nt 4\\.0|winnt ?4\\.0'),('win_nt','Windows NT',7,'winnt'),('win_98','Windows 98',8,'windows 98|win98'),('win_95','Windows 95',9,'windows 95|win95'),('win','Unknown Windows OS',10,'windows'),('os_x','Mac OS X',11,'os x'),('ppc_mac','Power PC Mac',12,'ppc mac'),('ppc','Macintosh',13,'ppc'),('freebsd','FreeBSD',14,'freebsd'),('linux','Linux',15,'linux'),('sunos','Sun Solaris',16,'sunos'),('beos','BeOS',17,'beos'),('aix','AIX',18,'aix'),('irix','Irix',19,'irix'),('osf','DEC OSF',20,'osf'),('hp-ux','HP-UX',21,'hp-ux'),('netbsd','NetBSD',22,'netbsd'),('bsdi','BSDi',23,'bsdi'),('openbsd','OpenBSD',24,'openbsd'),('unix','Unknown Unix OS',25,'unix'),('unknown','Unknown',100,'unknown');
/*!40000 ALTER TABLE `operating_systems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orbitscripts_admin_news`
--

DROP TABLE IF EXISTS `orbitscripts_admin_news`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `orbitscripts_admin_news` (
  `id` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `title` text NOT NULL,
  `link` varchar(256) NOT NULL default 'http://orbitscripts.com',
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `orbitscripts_admin_news`
--

LOCK TABLES `orbitscripts_admin_news` WRITE;
/*!40000 ALTER TABLE `orbitscripts_admin_news` DISABLE KEYS */;
/*!40000 ALTER TABLE `orbitscripts_admin_news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orbitscripts_payment_gateways_news`
--

DROP TABLE IF EXISTS `orbitscripts_payment_gateways_news`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `orbitscripts_payment_gateways_news`
--

LOCK TABLES `orbitscripts_payment_gateways_news` WRITE;
/*!40000 ALTER TABLE `orbitscripts_payment_gateways_news` DISABLE KEYS */;
INSERT INTO `orbitscripts_payment_gateways_news` VALUES (1,'2009-03-23 21:00:00',25,'1.0.0','Do you want your users to have a bigger choice of payment methods?','Request additional payment gateways integration into the system.','http://orbitscripts.com');
/*!40000 ALTER TABLE `orbitscripts_payment_gateways_news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orbitscripts_plugins_news`
--

DROP TABLE IF EXISTS `orbitscripts_plugins_news`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `orbitscripts_plugins_news`
--

LOCK TABLES `orbitscripts_plugins_news` WRITE;
/*!40000 ALTER TABLE `orbitscripts_plugins_news` DISABLE KEYS */;
/*!40000 ALTER TABLE `orbitscripts_plugins_news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organic_search_rating_detailed`
--

DROP TABLE IF EXISTS `organic_search_rating_detailed`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `organic_search_rating_detailed` (
  `url_hash` char(32) NOT NULL,
  `id_surfer` int(10) unsigned NOT NULL,
  `rating_mark` tinyint(1) unsigned NOT NULL,
  `rating_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`url_hash`,`id_surfer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `organic_search_rating_detailed`
--

LOCK TABLES `organic_search_rating_detailed` WRITE;
/*!40000 ALTER TABLE `organic_search_rating_detailed` DISABLE KEYS */;
/*!40000 ALTER TABLE `organic_search_rating_detailed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_keywords`
--

DROP TABLE IF EXISTS `page_keywords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `page_keywords` (
  `pagehash` char(32) NOT NULL,
  `page` varchar(255) NOT NULL,
  `last_update` int(11) default NULL,
  `need_update` enum('true','false') default 'false',
  `keywords` text,
  PRIMARY KEY  (`pagehash`),
  KEY `last_update` (`last_update`),
  KEY `need_update` (`need_update`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `page_keywords`
--

LOCK TABLES `page_keywords` WRITE;
/*!40000 ALTER TABLE `page_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_templates`
--

DROP TABLE IF EXISTS `page_templates`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `page_templates` (
  `id_page_template` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(50) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `default` varchar(255) default NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY  (`id_page_template`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `page_templates`
--

LOCK TABLES `page_templates` WRITE;
/*!40000 ALTER TABLE `page_templates` DISABLE KEYS */;
INSERT INTO `page_templates` VALUES (2,'Welcome Page','welcome','guest/home/template.html',20),(3,'Terms & Services','terms','guest/home/terms.html',30),(4,'Privacy Policy','policy','guest/home/policy.html',40),(5,'Welcome Page, Advertiser Information','adv_info','guest/home/<%LOCALE%>/advertiser_info.html',21),(7,'Contact Us','contact','guest/contact_us/form.html',50),(8,'Header','header','common/parent/header.html',5);
/*!40000 ALTER TABLE `page_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parked_domain_color_shemes`
--

DROP TABLE IF EXISTS `parked_domain_color_shemes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `parked_domain_color_shemes` (
  `id_parked_domain_color_sheme` int(11) unsigned NOT NULL auto_increment,
  `id_parked_domain_template` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `file` varchar(255) NOT NULL,
  PRIMARY KEY  (`id_parked_domain_color_sheme`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `parked_domain_color_shemes`
--

LOCK TABLES `parked_domain_color_shemes` WRITE;
/*!40000 ALTER TABLE `parked_domain_color_shemes` DISABLE KEYS */;
/*!40000 ALTER TABLE `parked_domain_color_shemes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parked_domain_templates`
--

DROP TABLE IF EXISTS `parked_domain_templates`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `parked_domain_templates` (
  `id_parked_domain_template` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(150) NOT NULL,
  `template_type` enum('one_page','two_page') NOT NULL default 'one_page',
  `number_results` tinyint(4) default NULL,
  `folder` varchar(255) NOT NULL,
  PRIMARY KEY  (`id_parked_domain_template`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `parked_domain_templates`
--

LOCK TABLES `parked_domain_templates` WRITE;
/*!40000 ALTER TABLE `parked_domain_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `parked_domain_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parked_domains`
--

DROP TABLE IF EXISTS `parked_domains`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `parked_domains` (
  `id_parked_domain` int(11) unsigned NOT NULL auto_increment,
  `id_entity` int(10) unsigned NOT NULL,
  `domain` varchar(255) NOT NULL,
  `id_category` int(10) unsigned NOT NULL,
  `id_parked_domain_template` int(11) NOT NULL,
  `id_parked_domain_color_sheme` int(11) NOT NULL,
  `parking_type` enum('point_to_server','ns') NOT NULL default 'point_to_server',
  `price` decimal(16,4) default NULL,
  `minimal_bid` decimal(16,4) default NULL,
  `title` text NOT NULL,
  `keywords` text NOT NULL,
  `description` text NOT NULL,
  `status` enum('not_confirmed','active','suspended') NOT NULL default 'not_confirmed',
  PRIMARY KEY  (`id_parked_domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `parked_domains`
--

LOCK TABLES `parked_domains` WRITE;
/*!40000 ALTER TABLE `parked_domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `parked_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partner_advertisers`
--

DROP TABLE IF EXISTS `partner_advertisers`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `partner_advertisers` (
  `id_entity_partner` int(10) unsigned NOT NULL,
  `id_entity_advertiser` int(10) unsigned NOT NULL,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `status` enum('request','accept','decline') NOT NULL,
  UNIQUE KEY `advertiser` (`id_entity_advertiser`),
  KEY `partner` (`id_entity_partner`),
  KEY `ststus` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `partner_advertisers`
--

LOCK TABLES `partner_advertisers` WRITE;
/*!40000 ALTER TABLE `partner_advertisers` DISABLE KEYS */;
/*!40000 ALTER TABLE `partner_advertisers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partners`
--

DROP TABLE IF EXISTS `partners`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `partners` (
  `id_entity_partner` int(10) unsigned NOT NULL,
  `agency_name` char(100) NOT NULL,
  `agency_description` text NOT NULL,
  `agency_url` text NOT NULL,
  `agency_logo` text NOT NULL,
  `first_account_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `qualification` enum('base','advanced') NOT NULL,
  PRIMARY KEY  (`id_entity_partner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `partners`
--

LOCK TABLES `partners` WRITE;
/*!40000 ALTER TABLE `partners` DISABLE KEYS */;
/*!40000 ALTER TABLE `partners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partnership`
--

DROP TABLE IF EXISTS `partnership`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `partnership` (
  `id_entity_publisher` int(10) unsigned NOT NULL,
  `id_entity_advertiser` int(10) unsigned NOT NULL,
  `status` enum('request','accept','decline') NOT NULL,
  UNIQUE KEY `id` (`id_entity_advertiser`,`id_entity_publisher`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `partnership`
--

LOCK TABLES `partnership` WRITE;
/*!40000 ALTER TABLE `partnership` DISABLE KEYS */;
/*!40000 ALTER TABLE `partnership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_gateways`
--

DROP TABLE IF EXISTS `payment_gateways`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `payment_gateways`
--

LOCK TABLES `payment_gateways` WRITE;
/*!40000 ALTER TABLE `payment_gateways` DISABLE KEYS */;
INSERT INTO `payment_gateways` VALUES (2,'dfgdgggssd@sdf.yt','E-Mail','','',0,'enabled',NULL,'deposit','0.00','0.00','1.0','paypal','required|valid_email',NULL,50,NULL,'all','1.0000',NULL,NULL,0,NULL,NULL,0),(3,'dfgdgggssd','Account ID','ggfgfdffgdgfdfgd','Transaction Key',1,'enabled',NULL,'deposit','0.00','1.00','1.2','authorise','required|max_length[20]','required|exact_length[16]',20,16,'deposit','0.0100',NULL,NULL,0,NULL,NULL,0);
/*!40000 ALTER TABLE `payment_gateways` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_gateways_to_request`
--

DROP TABLE IF EXISTS `payment_gateways_to_request`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `payment_gateways_to_request` (
  `title` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `payment_gateways_to_request`
--

LOCK TABLES `payment_gateways_to_request` WRITE;
/*!40000 ALTER TABLE `payment_gateways_to_request` DISABLE KEYS */;
INSERT INTO `payment_gateways_to_request` VALUES ('2CheckOut'),('ClickBank'),('Ebullion'),('EGold'),('EvoCash'),('intGold'),('MoneyBookers'),('MultiCards'),('Netbilling'),('PayPalSubscribe'),('SecPay'),('WorldPay');
/*!40000 ALTER TABLE `payment_gateways_to_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_requests`
--

DROP TABLE IF EXISTS `payment_requests`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `payment_requests` (
  `id_payment_request` int(10) unsigned NOT NULL auto_increment,
  `id_entity` int(10) unsigned NOT NULL,
  `id_gateway` int(10) unsigned NOT NULL,
  `request_value` decimal(16,4) unsigned NOT NULL,
  `payout_value` decimal(16,4) unsigned NOT NULL,
  `charge_value` decimal(16,4) unsigned NOT NULL,
  `status` enum('requested','denied','paid','returned') NOT NULL default 'requested',
  `type` enum('user_request','net_15','net_30','net_45','custom_period') NOT NULL default 'user_request',
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `id_flow` int(10) unsigned NOT NULL,
  `valid_thru` timestamp NOT NULL default '0000-00-00 00:00:00',
  `description` varchar(255) default NULL,
  PRIMARY KEY  (`id_payment_request`),
  KEY `entity` (`id_entity`),
  KEY `status` (`status`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `payment_requests`
--

LOCK TABLES `payment_requests` WRITE;
/*!40000 ALTER TABLE `payment_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_transactions`
--

DROP TABLE IF EXISTS `payment_transactions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `payment_transactions` (
  `id_flow` int(10) unsigned NOT NULL COMMENT 'SmartPPC transaction ID',
  `transaction_id` varchar(32) NOT NULL default '' COMMENT 'Payment Gateway transaction ID',
  PRIMARY KEY  (`id_flow`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `payment_transactions`
--

LOCK TABLES `payment_transactions` WRITE;
/*!40000 ALTER TABLE `payment_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paypal_payment_transactions`
--

DROP TABLE IF EXISTS `paypal_payment_transactions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `paypal_payment_transactions` (
  `id_transaction` int(10) unsigned NOT NULL auto_increment,
  `id_payment_request` int(10) unsigned default NULL,
  `e_mail` varchar(100) NOT NULL,
  `time` datetime NOT NULL,
  `amount` varchar(20) NOT NULL,
  `money` varchar(20) default NULL,
  PRIMARY KEY  (`id_transaction`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `paypal_payment_transactions`
--

LOCK TABLES `paypal_payment_transactions` WRITE;
/*!40000 ALTER TABLE `paypal_payment_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `paypal_payment_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pending_escrows`
--

DROP TABLE IF EXISTS `pending_escrows`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `pending_escrows` (
  `id_group_site_channel` int(10) unsigned NOT NULL,
  `id_entity` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_group_site_channel`,`id_entity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `pending_escrows`
--

LOCK TABLES `pending_escrows` WRITE;
/*!40000 ALTER TABLE `pending_escrows` DISABLE KEYS */;
/*!40000 ALTER TABLE `pending_escrows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `plugins` (
  `name` varchar(100) NOT NULL,
  `config_file` varchar(100) NOT NULL default '',
  `status` enum('enabled','disabled') NOT NULL default 'enabled',
  `loading_order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `plugins`
--

LOCK TABLES `plugins` WRITE;
/*!40000 ALTER TABLE `plugins` DISABLE KEYS */;
/*!40000 ALTER TABLE `plugins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_clicks`
--

DROP TABLE IF EXISTS `product_clicks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `product_clicks` (
  `id_product_click` bigint(20) unsigned NOT NULL auto_increment,
  `id_product` int(10) unsigned NOT NULL,
  `datetime` timestamp NOT NULL default '0000-00-00 00:00:00',
  `price` double NOT NULL,
  `remote_addr` int(10) unsigned NOT NULL,
  `id_country` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_product_click`),
  KEY `ad` (`id_product`),
  KEY `dateime` (`datetime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `product_clicks`
--

LOCK TABLES `product_clicks` WRITE;
/*!40000 ALTER TABLE `product_clicks` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_clicks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `products` (
  `id_product` int(10) unsigned NOT NULL auto_increment,
  `id_group` int(10) unsigned NOT NULL,
  `status` enum('active','passive','blocked') NOT NULL,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `id_category` int(10) unsigned NOT NULL,
  `name` char(50) NOT NULL,
  `price` decimal(16,4) unsigned NOT NULL,
  `buy_url` char(100) NOT NULL,
  `description` char(100) default NULL,
  `manufacturer` char(30) default NULL,
  `format` char(30) default NULL,
  `in_stock` tinyint(1) default NULL,
  `product_condition` char(30) default NULL,
  `warranty` char(30) default NULL,
  PRIMARY KEY  (`id_product`),
  KEY `id_group` (`id_group`),
  KEY `status` (`status`),
  KEY `category` (`id_category`),
  KEY `price` (`price`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `program_purchases`
--

DROP TABLE IF EXISTS `program_purchases`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `program_purchases` (
  `id_purchase` int(10) unsigned NOT NULL auto_increment,
  `id_group_site_channel` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `volume` int(10) unsigned NOT NULL,
  `spent` decimal(16,4) unsigned NOT NULL,
  PRIMARY KEY  (`id_purchase`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `program_purchases`
--

LOCK TABLES `program_purchases` WRITE;
/*!40000 ALTER TABLE `program_purchases` DISABLE KEYS */;
/*!40000 ALTER TABLE `program_purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `program_types`
--

DROP TABLE IF EXISTS `program_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `program_types` (
  `id_program_type` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY  (`id_program_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `program_types`
--

LOCK TABLES `program_types` WRITE;
/*!40000 ALTER TABLE `program_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `program_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publisher_escrow`
--

DROP TABLE IF EXISTS `publisher_escrow`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `publisher_escrow` (
  `id_escrow` int(10) unsigned NOT NULL auto_increment,
  `id_entity` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `value` int(10) unsigned NOT NULL,
  `cost` decimal(16,4) NOT NULL,
  `program_type` enum('flat_rate','cpm') default NULL,
  `balance` decimal(16,4) NOT NULL,
  `status` enum('buy','completed','upgrade') default NULL,
  PRIMARY KEY  (`id_escrow`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `publisher_escrow`
--

LOCK TABLES `publisher_escrow` WRITE;
/*!40000 ALTER TABLE `publisher_escrow` DISABLE KEYS */;
/*!40000 ALTER TABLE `publisher_escrow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publishers`
--

DROP TABLE IF EXISTS `publishers`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `publishers` (
  `id_entity_publisher` int(10) unsigned NOT NULL,
  `commission` decimal(5,2) default NULL,
  `escrow` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_entity_publisher`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `publishers`
--

LOCK TABLES `publishers` WRITE;
/*!40000 ALTER TABLE `publishers` DISABLE KEYS */;
/*!40000 ALTER TABLE `publishers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publishers_feeds_commissions`
--

DROP TABLE IF EXISTS `publishers_feeds_commissions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `publishers_feeds_commissions` (
  `id_entity_publisher` int(10) unsigned NOT NULL,
  `commission_value` decimal(5,2) NOT NULL,
  `id_feed` int(10) unsigned NOT NULL,
  UNIQUE KEY `id_entity_publisher` (`id_entity_publisher`,`id_feed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `publishers_feeds_commissions`
--

LOCK TABLES `publishers_feeds_commissions` WRITE;
/*!40000 ALTER TABLE `publishers_feeds_commissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `publishers_feeds_commissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publishers_programs_commissions`
--

DROP TABLE IF EXISTS `publishers_programs_commissions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `publishers_programs_commissions` (
  `id_entity_publisher` int(10) unsigned NOT NULL,
  `commission_value` decimal(5,2) NOT NULL default '0.00',
  `program_type` enum('CPM','Flat_Rate','CPC') NOT NULL default 'CPM'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `publishers_programs_commissions`
--

LOCK TABLES `publishers_programs_commissions` WRITE;
/*!40000 ALTER TABLE `publishers_programs_commissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `publishers_programs_commissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `related_directories`
--

DROP TABLE IF EXISTS `related_directories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `related_directories` (
  `id_directory_base` int(10) unsigned NOT NULL,
  `id_directory_related` int(10) unsigned NOT NULL,
  `position` tinyint(3) unsigned NOT NULL,
  UNIQUE KEY `id` (`id_directory_base`,`id_directory_related`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `related_directories`
--

LOCK TABLES `related_directories` WRITE;
/*!40000 ALTER TABLE `related_directories` DISABLE KEYS */;
/*!40000 ALTER TABLE `related_directories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `renew_history`
--

DROP TABLE IF EXISTS `renew_history`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `renew_history` (
  `id_group_site_channel` int(11) unsigned NOT NULL,
  `impressions` int(11) unsigned NOT NULL default '0',
  `clicks` int(11) unsigned NOT NULL default '0',
  `spent` decimal(16,4) unsigned NOT NULL default '0.0000',
  `used` decimal(16,4) unsigned NOT NULL default '0.0000',
  `days` int(11) unsigned NOT NULL default '0',
  `start_date_time` datetime NOT NULL,
  KEY `index` (`id_group_site_channel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `renew_history`
--

LOCK TABLES `renew_history` WRITE;
/*!40000 ALTER TABLE `renew_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `renew_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_entity_columns`
--

DROP TABLE IF EXISTS `report_entity_columns`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `report_entity_columns` (
  `id_entity` int(10) unsigned NOT NULL,
  `id_report_type` int(10) unsigned NOT NULL,
  `visible_columns` set('c0','c1','c2','c3','c4','c5','c6','c7','c8','c9','c10','c11','c12','c13','c14','c15','c16','c17','c18','c19','c20','c21','c22','c23','c24') NOT NULL,
  PRIMARY KEY  (`id_entity`,`id_report_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `report_entity_columns`
--

LOCK TABLES `report_entity_columns` WRITE;
/*!40000 ALTER TABLE `report_entity_columns` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_entity_columns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_groups`
--

DROP TABLE IF EXISTS `report_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `report_groups` (
  `id_report_group` int(10) unsigned NOT NULL,
  `id_role` tinyint(3) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `controller` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id_report_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `report_groups`
--

LOCK TABLES `report_groups` WRITE;
/*!40000 ALTER TABLE `report_groups` DISABLE KEYS */;
INSERT INTO `report_groups` VALUES (0,1,'Global Report','admin/global_reports'),(1,1,'Advertiser Report','admin/ad_serving_reports');
/*!40000 ALTER TABLE `report_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_templates`
--

DROP TABLE IF EXISTS `report_templates`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `report_templates` (
  `id_report_template` int(10) unsigned NOT NULL auto_increment,
  `custom_title` varchar(100) NOT NULL,
  `schedule` enum('daily','weekly','monthly') NOT NULL,
  `id_entity` int(10) unsigned NOT NULL,
  `frozen` enum('false','true') NOT NULL default 'false',
  `creation_date` datetime NOT NULL,
  `e_mail` varchar(50) default NULL,
  `id_report_type` int(10) unsigned NOT NULL,
  `visible_columns` set('c0','c1','c2','c3','c4','c5','c6','c7','c8','c9','c10','c11','c12','c13','c14','c15','c16','c17','c18','c19') NOT NULL,
  `extra_params` longblob,
  `period` enum('today','yesterday','lastweek','lastbusinessweek','thismonth','lastmonth','alltime') NOT NULL,
  `last_date` date NOT NULL,
  `urgent` enum('false','true') NOT NULL default 'false',
  `last_report_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id_report_template`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `report_templates`
--

LOCK TABLES `report_templates` WRITE;
/*!40000 ALTER TABLE `report_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_type_fields`
--

DROP TABLE IF EXISTS `report_type_fields`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `report_type_fields`
--

LOCK TABLES `report_type_fields` WRITE;
/*!40000 ALTER TABLE `report_type_fields` DISABLE KEYS */;
INSERT INTO `report_type_fields` VALUES (1,'stat_date','Date',0,'record date',3,'asc','false','true'),(1,'url','Site URL',1,'site url',1,'asc','false','true'),(1,'channel','Channel',2,'channel\'s name',1,'asc','false','true'),(1,'impressions','Impressions',3,'quantity of impressions',2,'asc','true','false'),(1,'clicks','Clicks',4,'quantity of clicks',2,'asc','true','false'),(1,'ctr','% CTR',5,'click-through rate',7,'asc','true','false'),(1,'cpc','CPC',6,'average cost per click',8,'asc','true','false'),(1,'earned','Earnings',7,'revenue of the system',8,'asc','true','false'),(2,'advertiser','Advertiser',0,'e-mail of certain advertiser',1,'asc','false','true'),(2,'spent','Spent',1,'money spent by advertiser during report period',8,'asc','true','false'),(2,'impressions','Impressions',2,'quantity of impressions for certain advertiser',2,'asc','true','false'),(2,'clicks','Clicks',3,'quantity of clicks',2,'asc','true','false'),(2,'ctr','% CTR',4,'click-through rate',7,'asc','true','false'),(2,'cpc','CPC',5,'average cost per click',8,'asc','true','false'),(2,'earned','Earnings',6,'revenue of the system',8,'asc','true','false'),(3,'name','Feed Name',0,'name of external feed',1,'asc','false','true'),(3,'earned','Earnings',5,'revenue of the system',8,'asc','true','false'),(3,'impressions','Impressions',1,'quantity of impressions for certain feed',2,'asc','true','false'),(3,'clicks','Clicks',2,'quantity of clicks',2,'asc','true','false'),(3,'ctr','% CTR',3,'click-through rate',7,'asc','true','false'),(3,'cpc','CPC',4,'average cost per click',8,'asc','true','false'),(4,'advertiser','Advertiser',0,'e-mail of certain advertiser',1,'asc','false','true'),(4,'event_date','Date',1,'date of certain event',3,'asc','false','true'),(4,'description','Description',2,'description of the event',10,'asc','false','false'),(4,'payment','Payment',3,'advertiser\'s deposit',8,'asc','true','false'),(4,'balance','Balance',4,'balance of certain advertiser after operation',8,'asc','false','false'),(5,'advertiser','Advertiser',0,'e-mail of certain advertiser',1,'asc','false','true'),(5,'campaign','Campaign',1,'name of certain campaign',1,'asc','false','true'),(5,'ad_group','Ad Group',2,'name of certain ad group',1,'asc','false','true'),(5,'ad_type','Ad Type',3,'ad type',10,'asc','false','true'),(5,'spent','Spent',4,'money spent by advertiser during report period for certain ad type (ad group, ad campaign)',8,'asc','true','false'),(5,'impressions','Impressions',5,'impressions of certain ad type (ad group, ad campaign)',2,'asc','true','false'),(5,'clicks','Clicks',6,'clicks on certain ad type (ad group, ad campaign)',2,'asc','true','false'),(5,'ctr','% CTR',7,'click-through rate',7,'asc','true','false'),(5,'earned','Earnings',9,'revenue of the system',8,'asc','true','false'),(5,'cpc','CPC',8,'average cost per click for certain ad type (ad group, ad campaign)',8,'asc','true','false'),(6,'advertiser','Advertiser',0,'e-mail of certain advertiser',1,'asc','false','true'),(6,'campaign','Campaign',1,'name of certain campaign',1,'asc','false','true'),(6,'campaign_status','Campaign Status',2,'status of certain campaign',1,'asc','false','false'),(6,'ad_group','Ad Group',3,'name of certain ad group',1,'asc','false','true'),(6,'group_status','Group Status',4,'status of certain ad group',1,'asc','false','false'),(6,'ad_type','Ad Type',5,'ad type',10,'asc','false','true'),(6,'title','Title',6,'title of certain ad and a poping-up preview of the ad',1,'asc','false','false'),(6,'ad_status','Ad Status',7,'status of certain ad',1,'asc','false','false'),(6,'description','Description 1',8,'description that is displaed in certain ad',1,'asc','false','false'),(6,'description2','Description 2',9,'second line of description that is displaed in certain ad',1,'asc','false','false'),(6,'display_url','Display URL',10,'URL that is displaed in certain ad',1,'asc','false','false'),(6,'destination_url','Destination URL',11,'real destination URL of certain ad',1,'asc','false','false'),(6,'spent','Spent',12,'money spent by advertiser during report period for certain ad',8,'asc','true','false'),(6,'impressions','Impressions',13,'quantity of impressions of certain ad (ad type, ad group, ad campaign)',2,'asc','true','false'),(6,'clicks','Clicks',14,'quantity of clicks on certain ad (ad type, ad group, ad campaign)',2,'asc','true','false'),(6,'ctr','% CTR',15,'click-through rate for certain ad (ad type, ad group, ad campaign)',7,'asc','true','false'),(6,'earned','Earnings',17,'revenue of the system',8,'asc','true','false'),(6,'cpc','CPC',16,'average cost per click for certain ad (ad type, ad group, ad campaign)',8,'asc','true','false'),(7,'event_date','Date',0,'date of certain event',3,'asc','false','true'),(7,'description','Description',1,'description of the event',10,'asc','false','false'),(7,'payment','Payment',2,'advertiser\'s deposit',8,'asc','true','false'),(7,'balance','Balance',3,'balance of the advertiser after operation',8,'asc','false','false'),(8,'campaign','Campaign',0,'name of certain campaign',1,'asc','false','true'),(8,'ad_group','Ad Group',1,'name of certain ad group',1,'asc','false','true'),(8,'ad_type','Ad Type',2,'ad type',10,'asc','false','true'),(8,'spent','Spent',3,'money spent by advertiser during report period for certain ad type (ad group, ad campaign)',8,'asc','true','false'),(8,'impressions','Impressions',4,'impressions of certain ad type (ad group, ad campaign)',2,'asc','true','false'),(8,'clicks','Clicks',5,'clicks on certain ad type (ad group, ad campaign)',2,'asc','true','false'),(8,'ctr','% CTR',6,'click-through rate',7,'asc','true','false'),(8,'cpc','CPC',7,'average cost per click for certain ad type (ad group, ad campaign)',8,'asc','true','false'),(9,'campaign','Campaign',0,'name of certain campaign',1,'asc','false','true'),(9,'campaign_status','Campaign Status',1,'status of certain campaign',1,'asc','false','false'),(9,'ad_group','Ad Group',2,'name of certain ad group',1,'asc','false','true'),(9,'group_status','Group Status',3,'status of certain ad group',1,'asc','false','false'),(9,'ad_type','Ad Type',4,'ad type',10,'asc','false','true'),(9,'title','Title',5,'title of certain ad and a poping-up preview of the ad',1,'asc','false','false'),(9,'ad_status','Ad Status',6,'status of certain ad',1,'asc','false','false'),(9,'description','Description 1',7,'description that is displaed in certain ad',1,'asc','false','false'),(9,'description2','Description 2',8,'second line of description that is displaed in certain ad',1,'asc','false','false'),(9,'display_url','Display URL',9,'URL that is displaed in certain ad',1,'asc','false','false'),(9,'destination_url','Destination URL',10,'real destination URL of certain ad',1,'asc','false','false'),(9,'spent','Spent',11,'money spent by advertiser during report period for certain ad',8,'asc','true','false'),(9,'impressions','Impressions',12,'quantity of impressions of certain ad (ad type, ad group, ad campaign)',2,'asc','true','false'),(9,'clicks','Clicks',13,'quantity of clicks on certain ad (ad type, ad group, ad campaign)',2,'asc','true','false'),(9,'ctr','% CTR',14,'click-through rate for certain ad (ad type, ad group, ad campaign)',7,'asc','true','false'),(9,'cpc','CPC',15,'average cost per click for certain ad (ad type, ad group, ad campaign)',8,'asc','true','false');
/*!40000 ALTER TABLE `report_type_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_types`
--

DROP TABLE IF EXISTS `report_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `report_types` (
  `id_report_type` int(10) unsigned NOT NULL auto_increment,
  `id_role` int(11) NOT NULL,
  `report_group` tinyint(3) NOT NULL,
  `report_order` tinyint(3) unsigned NOT NULL,
  `title` char(50) NOT NULL,
  `description` text,
  `sort_column` tinyint(3) unsigned NOT NULL default '0',
  `sort_direction` enum('asc','desc') NOT NULL default 'asc',
  PRIMARY KEY  (`id_report_type`),
  UNIQUE KEY `role` (`id_role`,`report_group`,`report_order`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `report_types`
--

LOCK TABLES `report_types` WRITE;
/*!40000 ALTER TABLE `report_types` DISABLE KEYS */;
INSERT INTO `report_types` VALUES (1,1,0,0,'Performance report','{@Performance report@}',0,'asc'),(2,1,0,1,'Revenue by Advertisers','{@Revenue statistics for certain advertiser(s)@}',0,'asc'),(3,1,0,2,'Revenue by External Feeds','{@Revenue statistics for certain External feed(s)@}',0,'asc'),(4,1,1,0,'Billing summary for Advertisers','{@Summary of deposits made by certain advertiser\'s; summary of payments charged from the deposit by the system@}',0,'asc'),(5,1,1,1,'General advertiser/campaign report','{@General report for certain advertiser(s) and campaign(s)@}',0,'asc'),(6,1,1,2,'Detailed advertiser/campaign report','{@Detailed report for certain advertiser(s) and campaign(s)@}',0,'asc'),(7,3,0,0,'Billing summary','{@Summary of deposits; summary of payments charged from the deposit by the system@}',0,'asc'),(8,3,0,1,'General campaign report','{@General report for certain campaign(s)@}',0,'asc'),(9,3,0,2,'Detailed campaign report','{@Detailed report for certain campaign(s)@}',0,'asc');
/*!40000 ALTER TABLE `report_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requested_reports`
--

DROP TABLE IF EXISTS `requested_reports`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `requested_reports` (
  `id_requested_report` int(10) unsigned NOT NULL auto_increment,
  `id_entity` int(10) unsigned NOT NULL,
  `custom_title` varchar(100) NOT NULL,
  `status` enum('requested','ready','inprocess','deleted') NOT NULL default 'requested',
  `request_date` datetime NOT NULL,
  `e_mail` varchar(50) default NULL,
  `urgent` enum('false','true') NOT NULL default 'false',
  `id_report_type` int(10) unsigned NOT NULL,
  `visible_columns` set('c0','c1','c2','c3','c4','c5','c6','c7','c8','c9','c10','c11','c12','c13','c14','c15','c16','c17','c18','c19','c20','c21','c22','c23','c24') NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `process_start` datetime default NULL,
  `extra_params` longblob,
  PRIMARY KEY  (`id_requested_report`),
  KEY `entity` (`id_entity`),
  KEY `status` (`status`),
  KEY `urgent` (`urgent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `requested_reports`
--

LOCK TABLES `requested_reports` WRITE;
/*!40000 ALTER TABLE `requested_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `requested_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `roles` (
  `id_role` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `is_used` enum('true','false') NOT NULL default 'true',
  `is_recipient` enum('true','false') NOT NULL default 'true',
  `class` varchar(100) NOT NULL default 'Sppc_Entity_Role',
  PRIMARY KEY  (`id_role`),
  KEY `is_used` (`is_used`),
  KEY `is_recipient` (`is_recipient`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','true','false','Sppc_Entity_Role'),(2,'guest','true','false','Sppc_Entity_Role'),(3,'advertiser','true','true','Sppc_Entity_Role'),(4,'publisher','false','true','Sppc_Entity_Role'),(5,'affiliate','false','true','Sppc_Entity_Role'),(6,'partner','false','true','Sppc_Entity_Role'),(8,'guest_admin','true','false','Sppc_Entity_Role'),(9,'payment_gateway','true','false','Sppc_Entity_Role'),(10,'member','false','true','Sppc_Entity_Role_Member');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_timetables`
--

DROP TABLE IF EXISTS `schedule_timetables`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `schedule_timetables` (
  `id_schedule` int(10) unsigned NOT NULL,
  `id_timetable` int(11) NOT NULL,
  `weekday` tinyint(1) unsigned NOT NULL,
  KEY `id_schedule` (`id_schedule`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `schedule_timetables`
--

LOCK TABLES `schedule_timetables` WRITE;
/*!40000 ALTER TABLE `schedule_timetables` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_timetables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `schedules` (
  `id_schedule` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id_schedule`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `schedules`
--

LOCK TABLES `schedules` WRITE;
/*!40000 ALTER TABLE `schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_engines`
--

DROP TABLE IF EXISTS `search_engines`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `search_engines` (
  `id_search_engine` int(10) unsigned NOT NULL auto_increment,
  `id_search_type` int(10) unsigned NOT NULL,
  `alias` varchar(50) NOT NULL,
  `class` varchar(50) default NULL,
  `title` varchar(100) NOT NULL,
  `is_active` enum('true','false') NOT NULL default 'false',
  `is_default` enum('true','false') NOT NULL default 'false',
  `is_cached` enum('true','false') NOT NULL default 'false',
  `cache_ttl` tinyint(3) unsigned NOT NULL,
  `is_metasearch` enum('true','false') NOT NULL default 'false',
  `is_metasearch_included` enum('true','false') NOT NULL default 'false',
  `options` text,
  `options_admin_controller` varchar(255) default NULL,
  `position` int(10) NOT NULL,
  `timeout` int(11) default '2800',
  PRIMARY KEY  (`id_search_engine`),
  UNIQUE KEY `plugin_search_engines_name_index` USING BTREE (`alias`),
  UNIQUE KEY `plugin_search_engine_alias_index` (`alias`),
  KEY `status` (`is_active`),
  KEY `is_default` (`is_default`),
  KEY `plugin_search_engines_class_index` (`class`),
  KEY `position` (`position`),
  KEY `plugin_search_engine_class_index` (`class`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `search_engines`
--

LOCK TABLES `search_engines` WRITE;
/*!40000 ALTER TABLE `search_engines` DISABLE KEYS */;
/*!40000 ALTER TABLE `search_engines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_feeds`
--

DROP TABLE IF EXISTS `search_feeds`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `search_feeds` (
  `id_search_type` int(10) unsigned NOT NULL,
  `id_feed` int(10) unsigned NOT NULL,
  `is_active` enum('true','false') NOT NULL default 'false',
  `affiliate_id_1` varchar(100) default NULL,
  `affiliate_id_2` varchar(100) default NULL,
  `affiliate_id_3` varchar(100) default NULL,
  `commission` decimal(5,2) unsigned default NULL,
  `num_results` mediumint(4) unsigned NOT NULL,
  PRIMARY KEY  (`id_search_type`,`id_feed`),
  KEY `status` (`is_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `search_feeds`
--

LOCK TABLES `search_feeds` WRITE;
/*!40000 ALTER TABLE `search_feeds` DISABLE KEYS */;
/*!40000 ALTER TABLE `search_feeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_queries`
--

DROP TABLE IF EXISTS `search_queries`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `search_queries` (
  `id_search_query` int(10) unsigned NOT NULL auto_increment,
  `search_query` varchar(255) NOT NULL,
  PRIMARY KEY  (`id_search_query`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `search_queries`
--

LOCK TABLES `search_queries` WRITE;
/*!40000 ALTER TABLE `search_queries` DISABLE KEYS */;
/*!40000 ALTER TABLE `search_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_relateds`
--

DROP TABLE IF EXISTS `search_relateds`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `search_relateds` (
  `id_search_related` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `class` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `url` varchar(1024) NOT NULL,
  `is_active` enum('true','false') NOT NULL default 'false',
  `options` text,
  PRIMARY KEY  (`id_search_related`),
  UNIQUE KEY `name` (`name`),
  KEY `status` (`is_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `search_relateds`
--

LOCK TABLES `search_relateds` WRITE;
/*!40000 ALTER TABLE `search_relateds` DISABLE KEYS */;
/*!40000 ALTER TABLE `search_relateds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_summary_urls`
--

DROP TABLE IF EXISTS `search_summary_urls`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `search_summary_urls` (
  `id_search_type` int(10) unsigned NOT NULL,
  `id_url` char(32) NOT NULL,
  `num_ratings` mediumint(8) unsigned NOT NULL,
  `sum_ratings` mediumint(8) unsigned NOT NULL,
  `num_comments` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id_search_type`,`id_url`),
  KEY `id_url` (`id_url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `search_summary_urls`
--

LOCK TABLES `search_summary_urls` WRITE;
/*!40000 ALTER TABLE `search_summary_urls` DISABLE KEYS */;
/*!40000 ALTER TABLE `search_summary_urls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_types`
--

DROP TABLE IF EXISTS `search_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `search_types` (
  `id_search_type` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(50) NOT NULL,
  `alias` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `num_results` mediumint(4) unsigned NOT NULL,
  `ad_num_results` mediumint(4) unsigned NOT NULL,
  `display_ads` enum('ads_organic','ads_organic_blend') NOT NULL default 'ads_organic',
  `use_ad_block` enum('true','false') NOT NULL default 'true',
  `use_sidebar_ads` enum('true','false') NOT NULL default 'true',
  `sidebar_ad_num_results` mediumint(4) unsigned NOT NULL,
  `id_targeting_group` int(10) unsigned NOT NULL,
  `is_active` enum('true','false') NOT NULL default 'true',
  `is_default` enum('true','false') NOT NULL default 'false',
  `use_top` enum('true','false') NOT NULL default 'true',
  `top_num_results` mediumint(4) unsigned NOT NULL,
  `id_search_related` int(10) unsigned NOT NULL,
  `use_related` enum('true','false') NOT NULL default 'true',
  `related_num_results` mediumint(4) unsigned NOT NULL,
  `use_comments` enum('true','false') NOT NULL default 'true',
  `use_quick_views` enum('true','false') NOT NULL default 'true',
  `use_favorites` enum('true','false') NOT NULL default 'true',
  `use_ratings` enum('true','false') NOT NULL default 'true',
  `use_bookmarks` enum('true','false') NOT NULL default 'true',
  `use_thumbnails` enum('true','false') NOT NULL default 'true',
  `use_email_link` enum('true','false') NOT NULL default 'true',
  `options` text,
  `description` text,
  `guid` char(36) NOT NULL,
  `order` int(10) unsigned NOT NULL default '100000',
  `is_custom` enum('false','true') default 'false',
  `ttl` int(11) default '0',
  `is_cached` enum('false','true') default 'false',
  PRIMARY KEY  (`id_search_type`),
  UNIQUE KEY `alias` USING BTREE (`alias`),
  KEY `status` (`is_active`),
  KEY `id_targeting_group` (`id_targeting_group`),
  KEY `id_related_search` (`id_search_related`),
  KEY `is_default` (`is_default`)
) ENGINE=MyISAM AUTO_INCREMENT=331 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `search_types`
--

LOCK TABLES `search_types` WRITE;
/*!40000 ALTER TABLE `search_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `search_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sessions` (
  `session_id` varchar(40) NOT NULL default '0',
  `ip_address` varchar(16) NOT NULL default '0',
  `user_agent` varchar(50) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL default '0',
  `user_data` text,
  PRIMARY KEY  (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting_field_restrictions`
--

DROP TABLE IF EXISTS `setting_field_restrictions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `setting_field_restrictions` (
  `name` char(32) NOT NULL,
  `value` varchar(50) NOT NULL,
  `value_order` tinyint(3) unsigned NOT NULL,
  KEY `id_contact_field` (`name`),
  KEY `value_order` (`value_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `setting_field_restrictions`
--

LOCK TABLES `setting_field_restrictions` WRITE;
/*!40000 ALTER TABLE `setting_field_restrictions` DISABLE KEYS */;
/*!40000 ALTER TABLE `setting_field_restrictions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting_fields`
--

DROP TABLE IF EXISTS `setting_fields`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `setting_fields` (
  `name` char(32) NOT NULL default '0',
  `type` enum('int','string','datetime','bool') NOT NULL,
  `title` char(50) default NULL,
  `description` text,
  `setting_order` tinyint(3) unsigned default NULL,
  `default_value` varchar(200) default NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `setting_fields`
--

LOCK TABLES `setting_fields` WRITE;
/*!40000 ALTER TABLE `setting_fields` DISABLE KEYS */;
INSERT INTO `setting_fields` VALUES ('WeekFromMonday','bool','{@Week starts at Monday@}',NULL,NULL,NULL);
/*!40000 ALTER TABLE `setting_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `settings` (
  `id_entity` int(10) unsigned NOT NULL,
  `name` char(64) NOT NULL,
  `value` varchar(200) NOT NULL,
  `expired` datetime default NULL,
  PRIMARY KEY  (`id_entity`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (0,'PathToFiles','./system/files',NULL),(0,'MaxReportsInProgress','10',NULL),(0,'MaxReportProcessHours','12',NULL),(0,'SystemEMail','admin@orbitscripts.com',NULL),(0,'MinPasswordLen','6',NULL),(0,'DefaultCountry','US',NULL),(0,'ApproveSignUp','1',NULL),(0,'ReserveCPMSlot','0',NULL),(0,'ApproveCampaigns','0',NULL),(0,'NewsPerPage','10',NULL),(0,'DefaultLocale','en_US',NULL),(0,'WeekFromMonday','false',NULL),(0,'MaxAlertsOnPage','3',NULL),(0,'SiteName','Orbit AdServer',NULL),(0,'MaxNewsOnPage','5',NULL),(0,'MaxMailsOnPage','5',NULL),(0,'ProxiesPerPage','10',NULL),(0,'TopCampaignsOnPage','5',NULL),(0,'ReportsPerPage','20',NULL),(0,'PaymentGatewaysPerPage','5',NULL),(0,'LastReportDate','2010-07-26',NULL),(0,'RepTemplatesPerPage','5',NULL),(0,'TopSitesOnPage','5',NULL),(0,'TopAdvertisersOnPage','5',NULL),(0,'AdvertisersPerPage','30',NULL),(0,'FeedsPerPage','20',NULL),(0,'DefaultFeedCommission','5',NULL),(0,'OrbitscriptsEMail','jak@orbitscripts.com',NULL),(0,'CampaignsPerPage','10',NULL),(0,'GroupsPerPage','10',NULL),(0,'AdsPerPage','20',NULL),(0,'ChannelsPerPage','10',NULL),(0,'ReportRowsPerPage','50',NULL),(0,'contextual_keywords_ttl','86400',NULL),(0,'client_timeout','1000',NULL),(0,'orbitscripts_news_xml','http://orbitscripts.com/rss/smartppcevo.xml',NULL),(0,'FirewallPerPage','10',NULL),(0,'feed_entity','4',NULL),(0,'admin_entity','1',NULL),(0,'Flat_RateAutoEscrow','true',NULL),(0,'CPMAutoEscrow','true',NULL),(0,'ValidateUserEMail','0',NULL),(0,'topActualPeriod','30',NULL),(0,'ApprovePubSignUp','0',NULL),(0,'TopMembersOnPage','5',NULL),(0,'MembersPerPage','30',NULL),(0,'PaymentSettings_Type','by_admin',NULL),(0,'PaymentSettings_Method','net_30',NULL),(0,'PaymentSettings_MinimalWithdraw','10.00',NULL),(0,'PaymentSettings_Period','0',NULL),(0,'RefererDomainPerPage','10',NULL),(0,'SiteDirectoryPerPage','10',NULL),(0,'TopPublishersOnPage','5',NULL),(0,'CpcDefaultCommission','50.00',NULL),(0,'FlatRateDefaultCommission','50.00',NULL),(0,'PaypalEmail','r2@orbita1.ru',NULL),(0,'CpmDefaultCommission','50.00',NULL),(0,'SelectedPaymentType','paypal',NULL),(0,'PaypalMinAmount','33',NULL),(0,'ApproveMemberSignUp','0',NULL),(0,'ID_SuperAdmin','1',NULL),(0,'ShowYourAdHereLink','1',NULL),(0,'YourAdHereLinkText','Your ad here',NULL),(0,'ParkedDomainResultsPerPage','10',NULL),(0,'DefaultParkedDomainStatus','not_confirmed',NULL),(0,'MinimalKeywordSearchBid','0.01',NULL),(0,'MinimalKeywordIntextBid','0.01',NULL),(0,'MinimalKeywordSitesBid','0.01',NULL),(0,'MinimalParkedDomainAdvertiserBid','0.01',NULL),(0,'DefaultTextBid','0.01',NULL),(0,'DefaultImageBid','0.01',NULL),(0,'DefaultCappingValue','0',NULL),(0,'build_adserver','5',NULL),(0,'MinimalKeywordInlineBid','0.01',NULL);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings_big`
--

DROP TABLE IF EXISTS `settings_big`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `settings_big` (
  `id_entity` int(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` text,
  `expired` datetime default NULL,
  PRIMARY KEY  (`id_entity`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `settings_big`
--

LOCK TABLES `settings_big` WRITE;
/*!40000 ALTER TABLE `settings_big` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings_big` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `show_contents`
--

DROP TABLE IF EXISTS `show_contents`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `show_contents` (
  `id` char(32) NOT NULL default '',
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `content` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `show_contents`
--

LOCK TABLES `show_contents` WRITE;
/*!40000 ALTER TABLE `show_contents` DISABLE KEYS */;
/*!40000 ALTER TABLE `show_contents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_categories`
--

DROP TABLE IF EXISTS `site_categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `site_categories` (
  `id_site` int(10) unsigned NOT NULL,
  `id_category` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_site`,`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `site_categories`
--

LOCK TABLES `site_categories` WRITE;
/*!40000 ALTER TABLE `site_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_channels`
--

DROP TABLE IF EXISTS `site_channels`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `site_channels` (
  `id_site` int(10) unsigned NOT NULL,
  `id_channel` int(10) unsigned NOT NULL,
  `status` enum('active','blocked','deleted','paused') NOT NULL default 'active',
  `id_site_channel` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id_site_channel`),
  UNIQUE KEY `site_channel` (`id_site`,`id_channel`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `site_channels`
--

LOCK TABLES `site_channels` WRITE;
/*!40000 ALTER TABLE `site_channels` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_channels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_color_schemes`
--

DROP TABLE IF EXISTS `site_color_schemes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `site_color_schemes` (
  `id_color_scheme` int(10) unsigned NOT NULL auto_increment,
  `name` char(50) NOT NULL,
  `path_to_css` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_color_scheme`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `site_color_schemes`
--

LOCK TABLES `site_color_schemes` WRITE;
/*!40000 ALTER TABLE `site_color_schemes` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_color_schemes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_layout_channels`
--

DROP TABLE IF EXISTS `site_layout_channels`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `site_layout_channels`
--

LOCK TABLES `site_layout_channels` WRITE;
/*!40000 ALTER TABLE `site_layout_channels` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_layout_channels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_layout_intext`
--

DROP TABLE IF EXISTS `site_layout_intext`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `site_layout_intext` (
  `id_site_layout_intext` int(10) unsigned NOT NULL auto_increment,
  `id_site_layout` int(10) unsigned NOT NULL,
  `id_site_layout_zone` int(10) unsigned NOT NULL,
  `x` smallint(5) unsigned NOT NULL default '0',
  `y` smallint(5) unsigned NOT NULL default '0',
  `width` smallint(5) unsigned NOT NULL default '0',
  `height` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  USING BTREE (`id_site_layout_intext`),
  KEY `index_site_layout_channels_id_site_layout` (`id_site_layout`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `site_layout_intext`
--

LOCK TABLES `site_layout_intext` WRITE;
/*!40000 ALTER TABLE `site_layout_intext` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_layout_intext` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_layout_searches`
--

DROP TABLE IF EXISTS `site_layout_searches`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `site_layout_searches` (
  `id_site_layout_search` int(10) unsigned NOT NULL auto_increment,
  `id_site_layout` int(10) unsigned NOT NULL,
  `id_site_layout_zone` int(10) unsigned NOT NULL,
  `x` smallint(5) unsigned NOT NULL default '0',
  `y` smallint(5) unsigned NOT NULL default '0',
  `width` smallint(5) unsigned NOT NULL default '0',
  `height` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  USING BTREE (`id_site_layout_search`),
  KEY `index_site_layout_channels_id_site_layout` (`id_site_layout`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `site_layout_searches`
--

LOCK TABLES `site_layout_searches` WRITE;
/*!40000 ALTER TABLE `site_layout_searches` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_layout_searches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_layout_zones`
--

DROP TABLE IF EXISTS `site_layout_zones`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `site_layout_zones` (
  `id_site_layout_zone` int(10) unsigned NOT NULL auto_increment,
  `id_site_layout` int(10) unsigned NOT NULL,
  `colspan` tinyint(3) unsigned NOT NULL,
  `rowspan` tinyint(3) unsigned NOT NULL,
  `order` tinyint(3) unsigned NOT NULL default '0',
  `id_json_zone` varchar(32) NOT NULL default '0',
  PRIMARY KEY  (`id_site_layout_zone`),
  KEY `site_layout_zones_id_site_layout` (`id_site_layout`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `site_layout_zones`
--

LOCK TABLES `site_layout_zones` WRITE;
/*!40000 ALTER TABLE `site_layout_zones` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_layout_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_layouts`
--

DROP TABLE IF EXISTS `site_layouts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `site_layouts`
--

LOCK TABLES `site_layouts` WRITE;
/*!40000 ALTER TABLE `site_layouts` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_layouts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sites`
--

DROP TABLE IF EXISTS `sites`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sites` (
  `id_site` int(10) unsigned NOT NULL auto_increment,
  `id_targeting_group` int(10) unsigned default NULL,
  `id_entity_publisher` int(10) unsigned NOT NULL,
  `url` varchar(150) NOT NULL,
  `name` varchar(100) NOT NULL,
  `include_subdomains` tinyint(1) unsigned NOT NULL default '0',
  `description` text,
  `status` enum('unapproved','pending','denied','active','blocked','deleted','paused') NOT NULL default 'active',
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `use_cpc` enum('true','false') default 'true',
  `min_cpc` decimal(16,4) unsigned default NULL,
  `min_cpc_image` decimal(16,4) unsigned default NULL,
  `ownership_confirmation_code` char(32) default NULL,
  `use_intext` enum('true','false') default 'false',
  PRIMARY KEY  (`id_site`),
  KEY `publisher` (`id_entity_publisher`),
  KEY `status` (`status`),
  KEY `min_cpc` (`min_cpc`),
  KEY `use_cpc` (`use_cpc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `sites`
--

LOCK TABLES `sites` WRITE;
/*!40000 ALTER TABLE `sites` DISABLE KEYS */;
/*!40000 ALTER TABLE `sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_ads`
--

DROP TABLE IF EXISTS `stat_ads`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_ads` (
  `id_ad` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` int(10) unsigned NOT NULL default '0',
  `sales` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  `id_flow` int(10) unsigned NOT NULL,
  `is_processed` enum('false','true') NOT NULL default 'false',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_ad`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_ads`
--

LOCK TABLES `stat_ads` WRITE;
/*!40000 ALTER TABLE `stat_ads` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_ads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_ads_cpc`
--

DROP TABLE IF EXISTS `stat_ads_cpc`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_ads_cpc` (
  `id_ad` int(10) unsigned NOT NULL,
  `id_group_site` int(10) unsigned NOT NULL,
  `id_channel` int(10) NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` int(10) unsigned NOT NULL default '0',
  `sales` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_ad`,`id_group_site`,`id_channel`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_ads_cpc`
--

LOCK TABLES `stat_ads_cpc` WRITE;
/*!40000 ALTER TABLE `stat_ads_cpc` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_ads_cpc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_ads_packet`
--

DROP TABLE IF EXISTS `stat_ads_packet`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_ads_packet` (
  `id_ad` int(10) unsigned NOT NULL,
  `id_group_site_channel` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` int(10) unsigned NOT NULL default '0',
  `sales` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_ad`,`id_group_site_channel`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_ads_packet`
--

LOCK TABLES `stat_ads_packet` WRITE;
/*!40000 ALTER TABLE `stat_ads_packet` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_ads_packet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_advertiser_channels`
--

DROP TABLE IF EXISTS `stat_advertiser_channels`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_advertiser_channels` (
  `id_entity_advertiser` int(11) unsigned NOT NULL,
  `id_group` int(10) unsigned NOT NULL,
  `id_site` int(10) unsigned NOT NULL,
  `id_channel` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) NOT NULL default '0.0000',
  `sales` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_channel`,`stat_date`,`id_site`,`id_entity_advertiser`,`id_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_advertiser_channels`
--

LOCK TABLES `stat_advertiser_channels` WRITE;
/*!40000 ALTER TABLE `stat_advertiser_channels` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_advertiser_channels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_advertiser_cpc_sites`
--

DROP TABLE IF EXISTS `stat_advertiser_cpc_sites`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_advertiser_cpc_sites` (
  `id_entity_advertiser` int(11) unsigned NOT NULL,
  `id_group_site` int(10) unsigned NOT NULL,
  `id_channel` int(10) NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) NOT NULL default '0.0000',
  `sales` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_entity_advertiser`,`id_group_site`,`stat_date`,`id_channel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_advertiser_cpc_sites`
--

LOCK TABLES `stat_advertiser_cpc_sites` WRITE;
/*!40000 ALTER TABLE `stat_advertiser_cpc_sites` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_advertiser_cpc_sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_advertiser_keywords`
--

DROP TABLE IF EXISTS `stat_advertiser_keywords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_advertiser_keywords` (
  `id_entity_advertiser` int(11) unsigned NOT NULL,
  `id_group` int(10) unsigned NOT NULL,
  `id_keyword` char(32) NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` double NOT NULL default '0',
  `sales` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`stat_date`,`id_keyword`,`id_entity_advertiser`,`id_group`),
  KEY `stat_group` (`id_keyword`,`id_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_advertiser_keywords`
--

LOCK TABLES `stat_advertiser_keywords` WRITE;
/*!40000 ALTER TABLE `stat_advertiser_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_advertiser_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_advertiser_search`
--

DROP TABLE IF EXISTS `stat_advertiser_search`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_advertiser_search` (
  `id_entity_advertiser` int(10) unsigned NOT NULL,
  `id_group` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) unsigned NOT NULL default '0.0000',
  `spent` decimal(16,4) unsigned NOT NULL default '0.0000',
  `sales` int(10) unsigned NOT NULL default '0',
  `earned_admin` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) unsigned NOT NULL default '0.0000',
  PRIMARY KEY  (`id_entity_advertiser`,`id_group`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_advertiser_search`
--

LOCK TABLES `stat_advertiser_search` WRITE;
/*!40000 ALTER TABLE `stat_advertiser_search` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_advertiser_search` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_advertiser_sites`
--

DROP TABLE IF EXISTS `stat_advertiser_sites`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_advertiser_sites` (
  `id_entity_advertiser` int(10) unsigned NOT NULL,
  `id_group` int(10) unsigned NOT NULL,
  `id_site` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) unsigned NOT NULL default '0.0000',
  `sales` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_admin` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) unsigned NOT NULL default '0.0000',
  PRIMARY KEY  (`id_entity_advertiser`,`id_group`,`id_site`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_advertiser_sites`
--

LOCK TABLES `stat_advertiser_sites` WRITE;
/*!40000 ALTER TABLE `stat_advertiser_sites` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_advertiser_sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_advertisers`
--

DROP TABLE IF EXISTS `stat_advertisers`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_advertisers` (
  `id_entity_advertiser` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  `impressions` int(10) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) NOT NULL default '0.0000',
  `sales` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_entity_advertiser`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_advertisers`
--

LOCK TABLES `stat_advertisers` WRITE;
/*!40000 ALTER TABLE `stat_advertisers` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_advertisers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_campaigns`
--

DROP TABLE IF EXISTS `stat_campaigns`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_campaigns` (
  `id_campaign` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` int(10) unsigned NOT NULL default '0',
  `sales` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_campaign`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_campaigns`
--

LOCK TABLES `stat_campaigns` WRITE;
/*!40000 ALTER TABLE `stat_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_channels`
--

DROP TABLE IF EXISTS `stat_channels`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_channels` (
  `id_channel` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  `alternative_impressions` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) NOT NULL default '0.0000',
  `sales` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_channel`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_channels`
--

LOCK TABLES `stat_channels` WRITE;
/*!40000 ALTER TABLE `stat_channels` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_channels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_codes`
--

DROP TABLE IF EXISTS `stat_codes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_codes` (
  `id_code` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) NOT NULL default '0.0000',
  `sales` int(10) unsigned NOT NULL default '0',
  `fraud_cliks` int(10) unsigned NOT NULL default '0',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_code`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_codes`
--

LOCK TABLES `stat_codes` WRITE;
/*!40000 ALTER TABLE `stat_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_countries`
--

DROP TABLE IF EXISTS `stat_countries`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_countries` (
  `id_country` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL,
  `clicks` int(10) unsigned NOT NULL,
  `fraud_clicks` int(10) unsigned NOT NULL,
  `revenue` decimal(16,4) NOT NULL,
  PRIMARY KEY  (`id_country`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_countries`
--

LOCK TABLES `stat_countries` WRITE;
/*!40000 ALTER TABLE `stat_countries` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_directories`
--

DROP TABLE IF EXISTS `stat_directories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_directories` (
  `id_directory` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `searches` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_directory`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_directories`
--

LOCK TABLES `stat_directories` WRITE;
/*!40000 ALTER TABLE `stat_directories` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_directories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_domain_searches`
--

DROP TABLE IF EXISTS `stat_domain_searches`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_domain_searches` (
  `id_domain` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `id_keyword` char(32) NOT NULL,
  `searches` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_domain`,`id_keyword`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_domain_searches`
--

LOCK TABLES `stat_domain_searches` WRITE;
/*!40000 ALTER TABLE `stat_domain_searches` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_domain_searches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_domains`
--

DROP TABLE IF EXISTS `stat_domains`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_domains` (
  `id_domain` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL,
  `clicks` int(10) unsigned NOT NULL,
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `revenue_from_feeds` decimal(16,4) NOT NULL default '0.0000',
  `revenue_from_advertisers` decimal(16,4) NOT NULL,
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_domain`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_domains`
--

LOCK TABLES `stat_domains` WRITE;
/*!40000 ALTER TABLE `stat_domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_domains_feeds`
--

DROP TABLE IF EXISTS `stat_domains_feeds`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_domains_feeds` (
  `id_domain` int(11) unsigned NOT NULL,
  `id_feed` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_admin` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) unsigned NOT NULL default '0.0000',
  PRIMARY KEY  (`id_domain`,`id_feed`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_domains_feeds`
--

LOCK TABLES `stat_domains_feeds` WRITE;
/*!40000 ALTER TABLE `stat_domains_feeds` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_domains_feeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_feeds`
--

DROP TABLE IF EXISTS `stat_feeds`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_feeds` (
  `id_feed` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_feed`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_feeds`
--

LOCK TABLES `stat_feeds` WRITE;
/*!40000 ALTER TABLE `stat_feeds` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_feeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_groups`
--

DROP TABLE IF EXISTS `stat_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_groups` (
  `id_group` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) NOT NULL default '0.0000',
  `sales` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_group`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_groups`
--

LOCK TABLES `stat_groups` WRITE;
/*!40000 ALTER TABLE `stat_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_intext`
--

DROP TABLE IF EXISTS `stat_intext`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_intext` (
  `id_site` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL,
  `clicks` int(10) unsigned NOT NULL,
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) unsigned NOT NULL default '0.0000',
  `spent` decimal(16,4) unsigned NOT NULL default '0.0000',
  `revenue_from_feeds` decimal(16,4) NOT NULL default '0.0000',
  `revenue_from_advertisers` decimal(16,4) NOT NULL,
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_site`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_intext`
--

LOCK TABLES `stat_intext` WRITE;
/*!40000 ALTER TABLE `stat_intext` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_intext` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_intext_feeds`
--

DROP TABLE IF EXISTS `stat_intext_feeds`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_intext_feeds` (
  `id_site` int(11) unsigned NOT NULL,
  `id_feed` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_admin` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) unsigned NOT NULL default '0.0000',
  PRIMARY KEY  (`id_site`,`id_feed`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_intext_feeds`
--

LOCK TABLES `stat_intext_feeds` WRITE;
/*!40000 ALTER TABLE `stat_intext_feeds` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_intext_feeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_intext_groups`
--

DROP TABLE IF EXISTS `stat_intext_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_intext_groups` (
  `id_group` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) NOT NULL default '0.0000',
  `sales` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_group`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_intext_groups`
--

LOCK TABLES `stat_intext_groups` WRITE;
/*!40000 ALTER TABLE `stat_intext_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_intext_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_ips`
--

DROP TABLE IF EXISTS `stat_ips`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_ips` (
  `remote_ip` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL,
  `clicks` int(10) unsigned NOT NULL,
  `fraud_clicks` int(10) unsigned NOT NULL,
  `revenue` decimal(16,4) NOT NULL,
  PRIMARY KEY  (`remote_ip`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_ips`
--

LOCK TABLES `stat_ips` WRITE;
/*!40000 ALTER TABLE `stat_ips` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_ips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_keywords`
--

DROP TABLE IF EXISTS `stat_keywords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_keywords` (
  `id_keyword` char(32) NOT NULL,
  `id_group` int(10) NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL,
  `clicks` int(10) unsigned NOT NULL,
  `fraud_clicks` int(10) unsigned NOT NULL,
  `revenue` decimal(16,4) NOT NULL,
  `spent` decimal(16,4) NOT NULL,
  PRIMARY KEY  (`id_keyword`,`id_group`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_keywords`
--

LOCK TABLES `stat_keywords` WRITE;
/*!40000 ALTER TABLE `stat_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_links`
--

DROP TABLE IF EXISTS `stat_links`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_links` (
  `id_link` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` int(10) unsigned NOT NULL default '0',
  `sales` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  `id_flow` int(10) unsigned NOT NULL,
  `is_processed` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY  (`id_link`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_links`
--

LOCK TABLES `stat_links` WRITE;
/*!40000 ALTER TABLE `stat_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_members`
--

DROP TABLE IF EXISTS `stat_members`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_members` (
  `id_member` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `num_ratings` mediumint(8) NOT NULL,
  `sum_ratings` mediumint(8) NOT NULL,
  `num_comments` mediumint(8) NOT NULL,
  `num_votes` int(10) unsigned default NULL,
  PRIMARY KEY  (`id_member`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_members`
--

LOCK TABLES `stat_members` WRITE;
/*!40000 ALTER TABLE `stat_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_products`
--

DROP TABLE IF EXISTS `stat_products`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_products` (
  `id_product` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` int(10) unsigned NOT NULL default '0',
  `sales` int(10) unsigned NOT NULL default '0',
  `spent` decimal(16,4) NOT NULL default '0.0000',
  `id_flow` int(10) unsigned NOT NULL,
  `is_processed` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY  (`id_product`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_products`
--

LOCK TABLES `stat_products` WRITE;
/*!40000 ALTER TABLE `stat_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_publishers`
--

DROP TABLE IF EXISTS `stat_publishers`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_publishers` (
  `id_entity_publisher` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) NOT NULL default '0.0000',
  `sales` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_entity_publisher`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_publishers`
--

LOCK TABLES `stat_publishers` WRITE;
/*!40000 ALTER TABLE `stat_publishers` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_publishers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_search_browsers`
--

DROP TABLE IF EXISTS `stat_search_browsers`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_search_browsers` (
  `id_search_type` int(10) unsigned NOT NULL,
  `id_browser` varchar(50) NOT NULL,
  `stat_date` date NOT NULL,
  `searches` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_admin` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) unsigned NOT NULL default '0.0000',
  PRIMARY KEY  (`id_search_type`,`id_browser`,`stat_date`),
  KEY `stat_date` (`stat_date`),
  KEY `id_browser` (`id_browser`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_search_browsers`
--

LOCK TABLES `stat_search_browsers` WRITE;
/*!40000 ALTER TABLE `stat_search_browsers` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_search_browsers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_search_countries`
--

DROP TABLE IF EXISTS `stat_search_countries`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_search_countries` (
  `id_search_type` int(10) unsigned NOT NULL,
  `id_country` char(2) NOT NULL,
  `stat_date` date NOT NULL,
  `searches` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_admin` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) unsigned NOT NULL default '0.0000',
  PRIMARY KEY  (`id_search_type`,`id_country`,`stat_date`),
  KEY `stat_date` (`stat_date`),
  KEY `id_country` (`id_country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_search_countries`
--

LOCK TABLES `stat_search_countries` WRITE;
/*!40000 ALTER TABLE `stat_search_countries` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_search_countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_search_queries`
--

DROP TABLE IF EXISTS `stat_search_queries`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_search_queries` (
  `id_search_type` int(10) unsigned NOT NULL,
  `id_entity` int(10) unsigned NOT NULL,
  `id_search_query` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `searches` int(10) unsigned NOT NULL,
  `impressions` int(10) unsigned NOT NULL,
  `clicks` int(10) unsigned NOT NULL,
  `fraud_clicks` int(10) unsigned NOT NULL,
  `conversions` int(10) unsigned NOT NULL,
  `conversions_value` decimal(16,4) unsigned NOT NULL,
  `earned_admin` decimal(16,4) unsigned NOT NULL,
  `earned_affiliate` decimal(16,4) unsigned NOT NULL,
  `earned_publisher` decimal(16,4) unsigned NOT NULL,
  PRIMARY KEY  (`id_search_type`,`id_entity`,`id_search_query`,`stat_date`),
  KEY `stat_date` (`stat_date`),
  KEY `id_search_query` (`id_search_query`),
  KEY `id_entity` (`id_entity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_search_queries`
--

LOCK TABLES `stat_search_queries` WRITE;
/*!40000 ALTER TABLE `stat_search_queries` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_search_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_search_types`
--

DROP TABLE IF EXISTS `stat_search_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_search_types` (
  `id_search_type` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `searches` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `clicks` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_admin` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) unsigned NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) unsigned NOT NULL default '0.0000',
  PRIMARY KEY  (`id_search_type`,`stat_date`),
  KEY `stat_date` (`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_search_types`
--

LOCK TABLES `stat_search_types` WRITE;
/*!40000 ALTER TABLE `stat_search_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_search_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_searches`
--

DROP TABLE IF EXISTS `stat_searches`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_searches` (
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL,
  `clicks` int(10) unsigned NOT NULL,
  `fraud_clicks` int(10) unsigned NOT NULL,
  `revenue` decimal(16,4) NOT NULL,
  PRIMARY KEY  (`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_searches`
--

LOCK TABLES `stat_searches` WRITE;
/*!40000 ALTER TABLE `stat_searches` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_searches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_sites`
--

DROP TABLE IF EXISTS `stat_sites`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_sites` (
  `id_site` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  `alternative_impressions` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) NOT NULL default '0.0000',
  `sales` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_site`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_sites`
--

LOCK TABLES `stat_sites` WRITE;
/*!40000 ALTER TABLE `stat_sites` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_sites_channels`
--

DROP TABLE IF EXISTS `stat_sites_channels`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_sites_channels` (
  `id_channel` int(10) unsigned NOT NULL,
  `id_site` int(10) unsigned NOT NULL,
  `stat_date` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL default '0',
  `impressions` int(10) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  `alternative_impressions` int(10) unsigned NOT NULL default '0',
  `conversions` int(10) unsigned NOT NULL default '0',
  `conversions_value` decimal(16,4) NOT NULL default '0.0000',
  `sales` int(10) unsigned NOT NULL default '0',
  `fraud_clicks` int(10) unsigned NOT NULL default '0',
  `earned_admin` decimal(16,4) NOT NULL default '0.0000',
  `earned_affiliate` decimal(16,4) NOT NULL default '0.0000',
  `earned_publisher` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`id_channel`,`id_site`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_sites_channels`
--

LOCK TABLES `stat_sites_channels` WRITE;
/*!40000 ALTER TABLE `stat_sites_channels` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_sites_channels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stat_user_agents`
--

DROP TABLE IF EXISTS `stat_user_agents`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stat_user_agents` (
  `id_user_agent` varchar(20) NOT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(10) unsigned NOT NULL,
  `searches` int(10) unsigned NOT NULL,
  `clicks` int(10) unsigned NOT NULL,
  `fraud_clicks` int(10) unsigned NOT NULL,
  `revenue` decimal(16,4) NOT NULL,
  PRIMARY KEY  (`id_user_agent`,`stat_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stat_user_agents`
--

LOCK TABLES `stat_user_agents` WRITE;
/*!40000 ALTER TABLE `stat_user_agents` DISABLE KEYS */;
/*!40000 ALTER TABLE `stat_user_agents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_infos`
--

DROP TABLE IF EXISTS `stock_infos`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stock_infos` (
  `id_stock` int(10) unsigned NOT NULL,
  `info_date` date NOT NULL,
  `value` decimal(16,4) NOT NULL,
  `value_change` decimal(16,4) NOT NULL,
  `volume` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_stock`,`info_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stock_infos`
--

LOCK TABLES `stock_infos` WRITE;
/*!40000 ALTER TABLE `stock_infos` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_infos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stocks`
--

DROP TABLE IF EXISTS `stocks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stocks` (
  `id_stock` int(10) unsigned NOT NULL auto_increment,
  `id_company` int(10) unsigned NOT NULL,
  `name` char(50) NOT NULL,
  `symbol` char(10) NOT NULL,
  PRIMARY KEY  (`id_stock`),
  KEY `company` (`id_company`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stocks`
--

LOCK TABLES `stocks` WRITE;
/*!40000 ALTER TABLE `stocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stopwords`
--

DROP TABLE IF EXISTS `stopwords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `stopwords` (
  `id_stopword` char(32) NOT NULL,
  `stopword` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_stopword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `stopwords`
--

LOCK TABLES `stopwords` WRITE;
/*!40000 ALTER TABLE `stopwords` DISABLE KEYS */;
/*!40000 ALTER TABLE `stopwords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `summary_members`
--

DROP TABLE IF EXISTS `summary_members`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `summary_members` (
  `id_member` int(10) unsigned NOT NULL,
  `num_ratings` mediumint(8) unsigned NOT NULL default '0',
  `sum_ratings` mediumint(8) unsigned NOT NULL default '0',
  `num_comments` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_member`),
  KEY `num_comments` (`num_comments`),
  KEY `rating` (`num_ratings`,`sum_ratings`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `summary_members`
--

LOCK TABLES `summary_members` WRITE;
/*!40000 ALTER TABLE `summary_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `summary_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surfer_ips`
--

DROP TABLE IF EXISTS `surfer_ips`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `surfer_ips` (
  `id_surfer` int(10) unsigned NOT NULL,
  `remote_ip` int(10) unsigned NOT NULL,
  `cookie` varchar(200) NOT NULL,
  UNIQUE KEY `id` (`id_surfer`,`remote_ip`),
  UNIQUE KEY `cookie` (`cookie`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `surfer_ips`
--

LOCK TABLES `surfer_ips` WRITE;
/*!40000 ALTER TABLE `surfer_ips` DISABLE KEYS */;
/*!40000 ALTER TABLE `surfer_ips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surfer_news_feeds`
--

DROP TABLE IF EXISTS `surfer_news_feeds`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `surfer_news_feeds` (
  `id_surfer` int(10) unsigned NOT NULL,
  `id_news_feed` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_surfer`,`id_news_feed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `surfer_news_feeds`
--

LOCK TABLES `surfer_news_feeds` WRITE;
/*!40000 ALTER TABLE `surfer_news_feeds` DISABLE KEYS */;
/*!40000 ALTER TABLE `surfer_news_feeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surfer_stocks`
--

DROP TABLE IF EXISTS `surfer_stocks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `surfer_stocks` (
  `id_surfer` int(10) unsigned NOT NULL,
  `id_stock` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_stock`,`id_surfer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `surfer_stocks`
--

LOCK TABLES `surfer_stocks` WRITE;
/*!40000 ALTER TABLE `surfer_stocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `surfer_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tabs_quickinfo`
--

DROP TABLE IF EXISTS `tabs_quickinfo`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tabs_quickinfo` (
  `url_hash` char(32) NOT NULL,
  `comments` int(10) unsigned NOT NULL,
  `files` int(10) unsigned NOT NULL,
  `bookmarks` int(10) unsigned NOT NULL,
  `rated_count` int(10) unsigned NOT NULL,
  `rating_sum` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`url_hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `tabs_quickinfo`
--

LOCK TABLES `tabs_quickinfo` WRITE;
/*!40000 ALTER TABLE `tabs_quickinfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `tabs_quickinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tabs_rating_detailed`
--

DROP TABLE IF EXISTS `tabs_rating_detailed`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tabs_rating_detailed` (
  `id_surfer` int(3) unsigned NOT NULL default '0',
  `id_tab_item` int(10) unsigned NOT NULL,
  `type` enum('comment','bookmark','file') NOT NULL,
  `rating_mark` tinyint(1) unsigned NOT NULL,
  `rating_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_surfer`,`id_tab_item`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `tabs_rating_detailed`
--

LOCK TABLES `tabs_rating_detailed` WRITE;
/*!40000 ALTER TABLE `tabs_rating_detailed` DISABLE KEYS */;
/*!40000 ALTER TABLE `tabs_rating_detailed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tags` (
  `id_tag` int(11) unsigned NOT NULL auto_increment,
  `id_entity` int(10) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `width` int(10) unsigned NOT NULL,
  `height` int(10) unsigned NOT NULL,
  `code` text NOT NULL,
  `status` enum('active','suspended','deleted') NOT NULL default 'active',
  PRIMARY KEY  (`id_tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `targeting_group_values`
--

DROP TABLE IF EXISTS `targeting_group_values`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `targeting_group_values`
--

LOCK TABLES `targeting_group_values` WRITE;
/*!40000 ALTER TABLE `targeting_group_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `targeting_group_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `targeting_groups`
--

DROP TABLE IF EXISTS `targeting_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `targeting_groups`
--

LOCK TABLES `targeting_groups` WRITE;
/*!40000 ALTER TABLE `targeting_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `targeting_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `themes`
--

DROP TABLE IF EXISTS `themes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `themes` (
  `id_theme` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(200) NOT NULL,
  `file` varchar(100) NOT NULL,
  `preview` varchar(100) NOT NULL,
  `status` enum('active','paused') NOT NULL default 'active',
  `sorting` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_theme`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `themes`
--

LOCK TABLES `themes` WRITE;
/*!40000 ALTER TABLE `themes` DISABLE KEYS */;
/*!40000 ALTER TABLE `themes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timetables`
--

DROP TABLE IF EXISTS `timetables`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `timetables`
--

LOCK TABLES `timetables` WRITE;
/*!40000 ALTER TABLE `timetables` DISABLE KEYS */;
/*!40000 ALTER TABLE `timetables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timezones`
--

DROP TABLE IF EXISTS `timezones`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `timezones` (
  `id_timezone` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `position` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_timezone`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `timezones`
--

LOCK TABLES `timezones` WRITE;
/*!40000 ALTER TABLE `timezones` DISABLE KEYS */;
INSERT INTO `timezones` VALUES (351,'(GMT-11:00) Apia',0),(371,'(GMT-11:00) Midway',1),(373,'(GMT-11:00) Niue',2),(376,'(GMT-11:00) Pago Pago',3),(357,'(GMT-10:00) Fakaofo',4),(364,'(GMT-10:00) Hawaii Time',5),(365,'(GMT-10:00) Johnston',6),(381,'(GMT-10:00) Rarotonga',7),(383,'(GMT-10:00) Tahiti',8),(370,'(GMT-09:30) Marquesas',9),(55,'(GMT-09:00) Alaska Time',10),(361,'(GMT-09:00) Gambier',11),(1,'(GMT-08:00) Pacific Time',12),(172,'(GMT-08:00) Pacific Time - Tijuana',13),(175,'(GMT-08:00) Pacific Time - Vancouver',14),(176,'(GMT-08:00) Pacific Time - Whitehorse',15),(378,'(GMT-08:00) Pitcairn',16),(93,'(GMT-07:00) Mountain Time',17),(148,'(GMT-07:00) Mountain Time - Arizona',18),(130,'(GMT-07:00) Mountain Time - Chihuahua, Mazatlan',19),(92,'(GMT-07:00) Mountain Time - Dawson Creek',20),(96,'(GMT-07:00) Mountain Time - Edmonton',21),(111,'(GMT-07:00) Mountain Time - Hermosillo',22),(179,'(GMT-07:00) Mountain Time - Yellowknife',23),(75,'(GMT-06:00) Belize',24),(85,'(GMT-06:00) Central Time',25),(169,'(GMT-06:00) Central Time',26),(133,'(GMT-06:00) Central Time - Mexico City',27),(156,'(GMT-06:00) Central Time - Regina',28),(177,'(GMT-06:00) Central Time - Winnipeg',29),(87,'(GMT-06:00) Costa Rica',30),(354,'(GMT-06:00) Easter Island',31),(98,'(GMT-06:00) El Salvador',32),(360,'(GMT-06:00) Galapagos',33),(106,'(GMT-06:00) Guatemala',34),(127,'(GMT-06:00) Managua',35),(77,'(GMT-05:00) Bogota',36),(84,'(GMT-05:00) Cayman',37),(140,'(GMT-05:00) Eastern Time',38),(118,'(GMT-05:00) Eastern Time - Iqaluit',39),(137,'(GMT-05:00) Eastern Time - Montreal',40),(173,'(GMT-05:00) Eastern Time - Toronto',41),(103,'(GMT-05:00) Grand Turk',42),(107,'(GMT-05:00) Guayaquil',43),(110,'(GMT-05:00) Havana',44),(119,'(GMT-05:00) Jamaica',45),(124,'(GMT-05:00) Lima',46),(139,'(GMT-05:00) Nassau',47),(145,'(GMT-05:00) Panama',48),(149,'(GMT-05:00) Port-au-Prince',49),(157,'(GMT-05:00) Rio Branco',50),(82,'(GMT-04:30) Caracas',51),(56,'(GMT-04:00) Anguilla',52),(57,'(GMT-04:00) Antigua',53),(70,'(GMT-04:00) Aruba',54),(71,'(GMT-04:00) Asuncion',55),(109,'(GMT-04:00) Atlantic Time - Halifax',56),(73,'(GMT-04:00) Barbados',57),(268,'(GMT-04:00) Bermuda',58),(76,'(GMT-04:00) Boa Vista',59),(80,'(GMT-04:00) Campo Grande',60),(88,'(GMT-04:00) Cuiaba',61),(89,'(GMT-04:00) Curacao',62),(95,'(GMT-04:00) Dominica',63),(104,'(GMT-04:00) Grenada',64),(105,'(GMT-04:00) Guadeloupe',65),(108,'(GMT-04:00) Guyana',66),(123,'(GMT-04:00) La Paz',67),(128,'(GMT-04:00) Manaus',68),(129,'(GMT-04:00) Martinique',69),(138,'(GMT-04:00) Montserrat',70),(185,'(GMT-04:00) Palmer',71),(150,'(GMT-04:00) Port of Spain',72),(151,'(GMT-04:00) Porto Velho',73),(152,'(GMT-04:00) Puerto Rico',74),(158,'(GMT-04:00) Santiago',75),(159,'(GMT-04:00) Santo Domingo',76),(164,'(GMT-04:00) St. Kitts',77),(165,'(GMT-04:00) St. Lucia',78),(166,'(GMT-04:00) St. Thomas',79),(167,'(GMT-04:00) St. Vincent',80),(276,'(GMT-04:00) Stanley',81),(170,'(GMT-04:00) Thule',82),(174,'(GMT-04:00) Tortola',83),(163,'(GMT-03:30) Newfoundland Time - St. Johns',84),(58,'(GMT-03:00) Araguaina',85),(74,'(GMT-03:00) Belem',86),(59,'(GMT-03:00) Buenos Aires',87),(83,'(GMT-03:00) Cayenne',88),(99,'(GMT-03:00) Fortaleza',89),(101,'(GMT-03:00) Godthab',90),(126,'(GMT-03:00) Maceio',91),(134,'(GMT-03:00) Miquelon',92),(136,'(GMT-03:00) Montevideo',93),(147,'(GMT-03:00) Paramaribo',94),(155,'(GMT-03:00) Recife',95),(186,'(GMT-03:00) Rothera',96),(72,'(GMT-03:00) Salvador',97),(160,'(GMT-03:00) Sao Paulo',98),(143,'(GMT-02:00) Noronha',99),(275,'(GMT-02:00) South Georgia',100),(267,'(GMT-01:00) Azores',101),(270,'(GMT-01:00) Cape Verde',102),(161,'(GMT-01:00) Scoresbysund',103),(2,'(GMT+00:00) Abidjan',104),(3,'(GMT+00:00) Accra',105),(7,'(GMT+00:00) Bamako',106),(9,'(GMT+00:00) Banjul',107),(10,'(GMT+00:00) Bissau',108),(269,'(GMT+00:00) Canary Islands',109),(15,'(GMT+00:00) Casablanca',110),(17,'(GMT+00:00) Conakry',111),(18,'(GMT+00:00) Dakar',112),(90,'(GMT+00:00) Danmarkshavn',113),(300,'(GMT+00:00) Dublin',114),(22,'(GMT+00:00) El Aaiun',115),(271,'(GMT+00:00) Faeroe',116),(23,'(GMT+00:00) Freetown',117),(306,'(GMT+00:00) Lisbon',118),(33,'(GMT+00:00) Lome',119),(308,'(GMT+00:00) London',120),(42,'(GMT+00:00) Monrovia',121),(46,'(GMT+00:00) Nouakchott',122),(47,'(GMT+00:00) Ouagadougou',123),(274,'(GMT+00:00) Reykjavik',124),(49,'(GMT+00:00) Sao Tome',125),(277,'(GMT+00:00) St Helena',126),(5,'(GMT+01:00) Algiers',127),(288,'(GMT+01:00) Amsterdam',128),(289,'(GMT+01:00) Andorra',129),(8,'(GMT+01:00) Bangui',130),(293,'(GMT+01:00) Berlin',131),(12,'(GMT+01:00) Brazzaville',132),(295,'(GMT+01:00) Brussels',133),(297,'(GMT+01:00) Budapest',134),(319,'(GMT+01:00) Central European Time',135),(16,'(GMT+01:00) Ceuta',136),(299,'(GMT+01:00) Copenhagen',137),(21,'(GMT+01:00) Douala',138),(301,'(GMT+01:00) Gibraltar',139),(30,'(GMT+01:00) Kinshasa',140),(31,'(GMT+01:00) Lagos',141),(32,'(GMT+01:00) Libreville',142),(34,'(GMT+01:00) Luanda',143),(309,'(GMT+01:00) Luxembourg',144),(310,'(GMT+01:00) Madrid',145),(37,'(GMT+01:00) Malabo',146),(311,'(GMT+01:00) Malta',147),(314,'(GMT+01:00) Monaco',148),(44,'(GMT+01:00) Ndjamena',149),(45,'(GMT+01:00) Niamey',150),(317,'(GMT+01:00) Oslo',151),(318,'(GMT+01:00) Paris',152),(48,'(GMT+01:00) Porto-Novo',153),(321,'(GMT+01:00) Rome',154),(328,'(GMT+01:00) Stockholm',155),(330,'(GMT+01:00) Tirane',156),(52,'(GMT+01:00) Tunis',157),(332,'(GMT+01:00) Vaduz',158),(334,'(GMT+01:00) Vienna',159),(336,'(GMT+01:00) Warsaw',160),(53,'(GMT+01:00) Windhoek',161),(339,'(GMT+01:00) Zurich',162),(193,'(GMT+02:00) Amman',163),(290,'(GMT+02:00) Athens',164),(202,'(GMT+02:00) Beirut',165),(11,'(GMT+02:00) Blantyre',166),(296,'(GMT+02:00) Bucharest',167),(13,'(GMT+02:00) Bujumbura',168),(14,'(GMT+02:00) Cairo',169),(298,'(GMT+02:00) Chisinau',170),(209,'(GMT+02:00) Damascus',171),(24,'(GMT+02:00) Gaborone',172),(214,'(GMT+02:00) Gaza',173),(25,'(GMT+02:00) Harare',174),(302,'(GMT+02:00) Helsinki',175),(303,'(GMT+02:00) Istanbul',176),(26,'(GMT+02:00) Johannesburg',177),(305,'(GMT+02:00) Kiev',178),(29,'(GMT+02:00) Kigali',179),(35,'(GMT+02:00) Lubumbashi',180),(36,'(GMT+02:00) Lusaka',181),(38,'(GMT+02:00) Maputo',182),(39,'(GMT+02:00) Maseru',183),(40,'(GMT+02:00) Mbabane',184),(313,'(GMT+02:00) Minsk',185),(304,'(GMT+02:00) Moscow-01 - Kaliningrad',186),(237,'(GMT+02:00) Nicosia',187),(320,'(GMT+02:00) Riga',188),(327,'(GMT+02:00) Sofia',189),(329,'(GMT+02:00) Tallinn',190),(222,'(GMT+02:00) Tel Aviv',191),(51,'(GMT+02:00) Tripoli',192),(335,'(GMT+02:00) Vilnius',193),(4,'(GMT+03:00) Addis Ababa',194),(191,'(GMT+03:00) Aden',195),(340,'(GMT+03:00) Antananarivo',196),(6,'(GMT+03:00) Asmera',197),(198,'(GMT+03:00) Baghdad',198),(199,'(GMT+03:00) Bahrain',199),(344,'(GMT+03:00) Comoro',200),(19,'(GMT+03:00) Dar es Salaam',201),(20,'(GMT+03:00) Djibouti',202),(27,'(GMT+03:00) Kampala',203),(28,'(GMT+03:00) Khartoum',204),(231,'(GMT+03:00) Kuwait',205),(349,'(GMT+03:00) Mayotte',206),(41,'(GMT+03:00) Mogadishu',207),(315,'(GMT+03:00) Moscow+00',208),(43,'(GMT+03:00) Nairobi',209),(244,'(GMT+03:00) Qatar',210),(247,'(GMT+03:00) Riyadh',211),(188,'(GMT+03:00) Syowa',212),(257,'(GMT+03:30) Tehran',213),(200,'(GMT+04:00) Baku',214),(212,'(GMT+04:00) Dubai',215),(346,'(GMT+04:00) Mahe',216),(348,'(GMT+04:00) Mauritius',217),(322,'(GMT+04:00) Moscow+01 - Samara',218),(236,'(GMT+04:00) Muscat',219),(350,'(GMT+04:00) Reunion',220),(256,'(GMT+04:00) Tbilisi',221),(266,'(GMT+04:00) Yerevan',222),(223,'(GMT+04:30) Kabul',223),(195,'(GMT+05:00) Aqtau',224),(196,'(GMT+05:00) Aqtobe',225),(197,'(GMT+05:00) Ashgabat',226),(213,'(GMT+05:00) Dushanbe',227),(225,'(GMT+05:00) Karachi',228),(345,'(GMT+05:00) Kerguelen',229),(347,'(GMT+05:00) Maldives',230),(265,'(GMT+05:00) Moscow+02 - Yekaterinburg',231),(255,'(GMT+05:00) Tashkent',232),(208,'(GMT+05:30) Colombo',233),(205,'(GMT+05:30) India Standard Time',234),(192,'(GMT+06:00) Almaty',235),(203,'(GMT+06:00) Bishkek',236),(341,'(GMT+06:00) Chagos',237),(210,'(GMT+06:00) Dhaka',238),(183,'(GMT+06:00) Mawson',239),(239,'(GMT+06:00) Moscow+03 - Omsk, Novosibirsk',240),(258,'(GMT+06:00) Thimphu',241),(189,'(GMT+06:00) Vostok',242),(343,'(GMT+06:30) Cocos',243),(246,'(GMT+06:30) Rangoon',244),(201,'(GMT+07:00) Bangkok',245),(342,'(GMT+07:00) Christmas',246),(181,'(GMT+07:00) Davis',247),(248,'(GMT+07:00) Hanoi',248),(217,'(GMT+07:00) Hovd',249),(220,'(GMT+07:00) Jakarta',250),(228,'(GMT+07:00) Moscow+04 - Krasnoyarsk',251),(241,'(GMT+07:00) Phnom Penh',252),(262,'(GMT+07:00) Vientiane',253),(204,'(GMT+08:00) Brunei',254),(180,'(GMT+08:00) Casey',255),(252,'(GMT+08:00) China Time - Beijing',256),(206,'(GMT+08:00) Choibalsan',257),(216,'(GMT+08:00) Hong Kong',258),(229,'(GMT+08:00) Kuala Lumpur',259),(232,'(GMT+08:00) Macau',260),(234,'(GMT+08:00) Makassar',261),(235,'(GMT+08:00) Manila',262),(218,'(GMT+08:00) Moscow+05 - Irkutsk',263),(253,'(GMT+08:00) Singapore',264),(254,'(GMT+08:00) Taipei',265),(260,'(GMT+08:00) Ulaanbaatar',266),(286,'(GMT+08:00) Western Time - Perth',267),(211,'(GMT+09:00) Dili',268),(221,'(GMT+09:00) Jayapura',269),(264,'(GMT+09:00) Moscow+06 - Yakutsk',270),(377,'(GMT+09:00) Palau',271),(243,'(GMT+09:00) Pyongyang',272),(251,'(GMT+09:00) Seoul',273),(259,'(GMT+09:00) Tokyo',274),(278,'(GMT+09:30) Central Time - Adelaide',275),(281,'(GMT+09:30) Central Time - Darwin',276),(182,'(GMT+10:00) Dumont D\'Urville',277),(279,'(GMT+10:00) Eastern Time - Brisbane',278),(282,'(GMT+10:00) Eastern Time - Hobart',279),(287,'(GMT+10:00) Eastern Time - Melbourne, Sydney',280),(363,'(GMT+10:00) Guam',281),(263,'(GMT+10:00) Moscow+07 - Yuzhno-Sakhalinsk',282),(380,'(GMT+10:00) Port Moresby',283),(382,'(GMT+10:00) Saipan',284),(386,'(GMT+10:00) Truk',285),(355,'(GMT+11:00) Efate',286),(362,'(GMT+11:00) Guadalcanal',287),(367,'(GMT+11:00) Kosrae',288),(233,'(GMT+11:00) Moscow+08 - Magadan',289),(375,'(GMT+11:00) Noumea',290),(379,'(GMT+11:00) Ponape',291),(374,'(GMT+11:30) Norfolk',292),(184,'(GMT+12:00) Antarctica/McMurdo',293),(187,'(GMT+12:00) Antarctica/South_Pole',294),(352,'(GMT+12:00) Auckland',295),(358,'(GMT+12:00) Fiji',296),(359,'(GMT+12:00) Funafuti',297),(368,'(GMT+12:00) Kwajalein',298),(369,'(GMT+12:00) Majuro',299),(224,'(GMT+12:00) Moscow+09 - Petropavlovsk-Kamchatskiy',300),(372,'(GMT+12:00) Nauru',301),(384,'(GMT+12:00) Tarawa',302),(387,'(GMT+12:00) Wake',303),(388,'(GMT+12:00) Wallis',304),(356,'(GMT+13:00) Enderbury',305),(385,'(GMT+13:00) Tongatapu',306),(366,'(GMT+14:00) Kiritimati',307);
/*!40000 ALTER TABLE `timezones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tokens`
--

DROP TABLE IF EXISTS `tokens`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tokens` (
  `id_keyword` char(32) NOT NULL,
  `token` varchar(50) NOT NULL,
  KEY `id_keyword` (`id_keyword`),
  KEY `token` (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `tokens`
--

LOCK TABLES `tokens` WRITE;
/*!40000 ALTER TABLE `tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wizard_steps`
--

DROP TABLE IF EXISTS `wizard_steps`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `wizard_steps`
--

LOCK TABLES `wizard_steps` WRITE;
/*!40000 ALTER TABLE `wizard_steps` DISABLE KEYS */;
INSERT INTO `wizard_steps` VALUES ('cpm_flatrate_step_main','cpm_flatrate','Create CPM / Flatrate Campaign','Edit CPM / Flatrate Campaign','advertiser/create_campaign_step_main/index/cpm_flatrate',NULL,0,NULL,NULL,NULL),('cpm_flatrate_step_group','cpm_flatrate','Create Group','Edit Group','advertiser/create_campaign_step_group_name/index/cpm_flatrate',NULL,1,NULL,NULL,NULL),('cpm_flatrate_step_choose_site_channels','cpm_flatrate','Choose Sites/Channels','Choose Sites/Channels','advertiser/create_campaign_step_choose_sites_channels/index/cpm_flatrate',NULL,2,NULL,'advertiser/create_campaign_step_set_pricing/index/cpm_flatrate',NULL),('cpm_flatrate_step_set_pricing','cpm_flatrate','Set Pricing','Set Pricing','advertiser/create_campaign_step_set_pricing/index/cpm_flatrate',NULL,3,NULL,NULL,'advertiser/create_campaign_step_choose_sites_channels/index/cpm_flatrate'),('cpm_flatrate_step_create_ad','cpm_flatrate','Create Ad',NULL,'advertiser/create_campaign_step_create_ad/index/cpm_flatrate',NULL,4,NULL,NULL,NULL),('cpm_flatrate_step_preview_ads','cpm_flatrate','Preview Ads',NULL,'advertiser/create_campaign_step_preview_ads/index/cpm_flatrate',NULL,5,NULL,NULL,NULL),('cpm_flatrate_step_review_selections','cpm_flatrate','Review your selections',NULL,'advertiser/create_campaign_step_review_selections/index/cpm_flatrate',NULL,6,NULL,NULL,NULL),('cpc_step_create_ad','cpc','Create Ad',NULL,'advertiser/create_campaign_step_create_ad/index/cpc',NULL,3,NULL,NULL,NULL),('cpc_step_review_selections','cpc','Review your selections',NULL,'advertiser/create_campaign_step_review_selections/index/cpc',NULL,5,NULL,NULL,NULL),('cpc_step_preview_ads','cpc','Preview Ads','Preview Ads','advertiser/create_campaign_step_preview_ads/index/cpc',NULL,4,NULL,NULL,NULL),('edit_channels_step_choose_site_channels','edit_channels','Choose Sites/Channels',NULL,'advertiser/edit_channels',NULL,0,NULL,NULL,NULL),('edit_channels_step_set_pricing','edit_channels','Set Pricing',NULL,'advertiser/edit_set_pricing',NULL,1,NULL,NULL,NULL),('edit_sites_step_select_sites','edit_sites','Select Sites',NULL,'advertiser/edit_sites',NULL,0,NULL,NULL,NULL),('edit_bids_step_manage_bids','edit_bids','Manage Bids',NULL,'advertiser/edit_cpc_bids',NULL,0,NULL,NULL,NULL),('create_ad_step_create_ad','create_ad','Create Ad',NULL,'advertiser/create_ad',NULL,0,NULL,NULL,NULL),('edit_compaign_step_edit_compaign','edit_campaign','Edit Campaign',NULL,'advertiser/edit_campaign',NULL,0,'Save',NULL,NULL),('cpc_step_select_sites','cpc','Select Sites','Select Sites','advertiser/create_campaign_step_select_sites/index/cpc',NULL,2,NULL,NULL,NULL),('cpc_step_main','cpc','Create CPC Campaign','Edit CPC Campaign','advertiser/create_campaign_step_main/index/cpc',NULL,0,NULL,NULL,NULL),('cpc_step_group','cpc','Create Group','Edit Group','advertiser/create_campaign_step_group_name/index/cpc',NULL,1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `wizard_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wizards`
--

DROP TABLE IF EXISTS `wizards`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `wizards` (
  `id_wizard` varchar(30) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`id_wizard`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `wizards`
--

LOCK TABLES `wizards` WRITE;
/*!40000 ALTER TABLE `wizards` DISABLE KEYS */;
INSERT INTO `wizards` VALUES ('cpm_flatrate','Create CPM/Flatrate Compaing Wizard'),('cpc','Create CPC Compaing Wizard'),('edit_channels','Edit channels Wizard'),('edit_sites','Edit sites Wizard'),('edit_bids','Edit bids Wizard'),('create_ad','Create Ad Wizard'),('edit_campaign','Edit campaign Wizard');
/*!40000 ALTER TABLE `wizards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zips`
--

DROP TABLE IF EXISTS `zips`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `zips` (
  `id_zip` int(10) unsigned NOT NULL auto_increment,
  `id_city` int(10) unsigned NOT NULL,
  `zip` char(20) NOT NULL,
  PRIMARY KEY  (`id_zip`),
  KEY `city` (`id_city`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `zips`
--

LOCK TABLES `zips` WRITE;
/*!40000 ALTER TABLE `zips` DISABLE KEYS */;
/*!40000 ALTER TABLE `zips` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-11-18 13:21:24
