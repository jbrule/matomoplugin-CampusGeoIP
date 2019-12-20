<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CampusGeoIP\LocationProvider;

use Exception;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\CampusGeoIP\CampusGeoIP;

class CampusGeoIPLocationProvider extends LocationProvider
{
    const ID = "campusgeoip";
    const TITLE = "Campus Geo IP";
    
    public function getLocation($info)
    {
        $ipAddress = $this->getIpFromInfo($info);
        
        $ipAddressMatch = CampusGeoIP::findMatch($ipAddress);
        
        //No match. Return unpopulated location,
        if($ipAddressMatch === false){
            $location = [];
            $this->completeLocationResult($location);
            return $location;
        }
        
        $location = [
            self::COUNTRY_CODE_KEY => $ipAddressMatch["country"],
            self::REGION_CODE_KEY => $ipAddressMatch["region"],
            self::CITY_NAME_KEY  => $ipAddressMatch["city"],
            self::LATITUDE_KEY => $ipAddressMatch["latitude"],
            self::LONGITUDE_KEY => $ipAddressMatch["longitude"],
            self::ISP_KEY => $ipAddressMatch["provider"],
            self::ORG_KEY => $ipAddressMatch["org"],
        ];
                
        $this->completeLocationResult($location);
        return $location;
    }

    public function isWorking()
    {
        return true;
    }

    public function isAvailable()
    {
        return CampusGeoIP::isPopulated();
    }
    
    public function getSupportedLocationInfo()
    {
        return [
            self::COUNTRY_CODE_KEY => true,
            self::REGION_CODE_KEY => true,
            self::CITY_NAME_KEY => true,
            self::LATITUDE_KEY => true,
            self::LONGITUDE_KEY => true,
            self::ISP_KEY => true,
            self::ORG_KEY => true,
        ];
    }

    public function getInfo()
    {
        return array(
            'id' => self::ID,
            'title' => self::TITLE,
            'description' => 'This location provider is designed to geolocate ips across a campus in an intranet type environment',
            'order' => 4
        );
    }
}
