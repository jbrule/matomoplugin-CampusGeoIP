<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CampusGeoIP;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\CampusGeoIP\CampusGeoIP;

/**
 * API for plugin CustomGeoIP
 *
 * @method static \Piwik\Plugins\CustomGeoIP\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public function geoLocate($ips = "")
    {				
		$ipAddresses = explode(",",$ips);
		
		$table = DataTable::makeFromSimpleArray(CampusGeoIP::findMatches($ipAddresses));
		
        return $table;
    }
}
