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
		
        $ipData = CampusGeoIP::findMatches($ipAddresses);
        
        //Avoid sumArrayRow error
        if (!empty($ipData[0])) {
            $columnsToNotAggregate = array_map(function () {
                return 'skip';
            }, $ipData[0]);
        }
        
		$dataTable = DataTable::makeFromSimpleArray($ipData);
        
        $dataTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnsToNotAggregate); 
        		
        return $dataTable;
    }
}