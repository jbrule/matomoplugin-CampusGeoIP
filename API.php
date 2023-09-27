<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CampusGeoIP;

use Piwik\Piwik;
use Piwik\DataTable;
use Piwik\Plugins\CampusGeoIP\CampusGeoIP;

/**
 * API for plugin CustomGeoIP
 *
 * @method static \Piwik\Plugins\CustomGeoIP\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
  public function getLocationFromIP($ips = ""){

    Piwik::checkUserHasSomeViewAccess();
    
    $ipAddresses = explode(",",$ips);
  
    $ipData = CampusGeoIP::findMatches($ipAddresses);
    
    $ipDataArray = array_map(function($entry){
      return (array)$entry;
    },$ipData);
      
    //Avoid sumArrayRow error
    if (!empty($ipDataArray[0])) {
      $columnsToNotAggregate = array_map(function () {
        return 'skip';
      }, $ipDataArray[0]);
    }
    
    $dataTable = DataTable::makeFromSimpleArray($ipDataArray);
    
    $dataTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnsToNotAggregate); 
    
    return $dataTable;
  }
}