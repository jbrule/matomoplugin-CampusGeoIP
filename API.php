<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CampusGeoIP;

use Piwik\Piwik;
use Piwik\Plugins\CampusGeoIP\CampusGeoIP;

/**
 * API for plugin CustomGeoIP
 *
 * @method static \Piwik\Plugins\CustomGeoIP\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public function getLocationFromIP($ips = "")
    {
        Piwik::checkUserHasSomeViewAccess();
        
		$ipAddresses = explode(",",$ips);
		
        $ipData = CampusGeoIP::findMatches($ipAddresses);
                		
        return $ipData;
    }
}