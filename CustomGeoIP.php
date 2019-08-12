<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomGeoIP;

use Piwik\Common;
use Piwik\Db;
use Piwik\Network\IP;
use Piwik\Tracker\Request as TrackerRequest;

class CustomGeoIP extends \Piwik\Plugin
{
	const TBL_LOCATIONS = "customgeoip_locations";
	const TBL_NETWORKS = "customgeoip_networks";
	
	public function install()
	{
		try {
            $sql = "CREATE TABLE " . Common::prefixTable(self::TBL_LOCATIONS) . " (
					`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`name` varchar(50) NOT NULL,
					`city_name` varchar(50) NOT NULL,
					`campus` char(2) DEFAULT NULL,
					`region` char(2) NOT NULL DEFAULT '',
					`country` char(2) NOT NULL DEFAULT '',
					`latitude` float(10,6) NOT NULL,
					`longitude` float(10,6) NOT NULL,
					`created_on` datetime NOT NULL,
					`updated_on` datetime NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `campus` (`campus`)
				  ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
				  
            Db::exec($sql);
			
        } catch (Exception $e) {
            // ignore error if table already exists (1050 code is for 'table already exists')
            if (!Db::get()->isErrNo($e, '1050')) {
                throw $e;
            }
        }
		
		try {
            $sql = "CREATE TABLE " . Common::prefixTable(self::TBL_NETWORKS) . " (
					`network_start` varbinary(16) DEFAULT NULL,
					`network_end` varbinary(16) DEFAULT NULL,
					`cidr` varchar(43) CHARACTER SET utf8 NOT NULL DEFAULT '',
					`note` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
					`region` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
					`campus` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
					`building` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
					`use` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
					`disabled` tinyint(1) DEFAULT NULL,
					`created_on` datetime NOT NULL,
					`updated_on` datetime NOT NULL,
					UNIQUE KEY `cidr` (`cidr`),
					UNIQUE KEY `network_range` (`network_start`,`network_end`),
					KEY `campus` (`campus`)
				  ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
			  
            Db::exec($sql);
		
        } catch (Exception $e) {
            // ignore error if table already exists (1050 code is for 'table already exists')
            if (!Db::get()->isErrNo($e, '1050')) {
                throw $e;
            }
        }
	}
	
	public function uninstall()
	{
		Db::dropTables([Common::prefixTable(self::TBL_LOCATIONS),Common::prefixTable(self::TBL_NETWORKS)]);
	}
}

/*
CREATE TABLE `piwik_customgeoip_locations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `city_name` varchar(50) NOT NULL,
  `campus` char(2) DEFAULT NULL,
  `region` char(2) NOT NULL DEFAULT '',
  `country` char(2) NOT NULL DEFAULT '',
  `latitude` float(10,6) NOT NULL,
  `longitude` float(10,6) NOT NULL,
  `created_on` datetime NOT NULL,
  `updated_on` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `campus` (`campus`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE DEFINER=`m055658`@`%` TRIGGER `customgeoip_location_updatetimeoninsert` BEFORE INSERT ON `piwik_customgeoip_locations` FOR EACH ROW SET NEW.created_on = NOW(), NEW.updated_on = NOW();
CREATE DEFINER=`m055658`@`%` TRIGGER `customgeoip_location_updatetimeonupdate` BEFORE UPDATE ON `piwik_customgeoip_locations` FOR EACH ROW SET NEW.updated_on = NOW();

CREATE TABLE `piwik_customgeoip_networks` (
  `network_start` varbinary(16) DEFAULT NULL,
  `network_end` varbinary(16) DEFAULT NULL,
  `cidr` varchar(43) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `note` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `region` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `campus` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `building` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `use` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `disabled` tinyint(1) DEFAULT NULL,
  UNIQUE KEY `cidr` (`cidr`),
  UNIQUE KEY `network_start` (`network_start`,`network_end`),
  KEY `campus` (`campus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
*/