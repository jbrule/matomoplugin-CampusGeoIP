<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CampusGeoIP;

class Network {
    
  public $ip;
  public $cidr;
  public $city;
  public $campus;
  public $region;
  public $country;
  public $org;
  public $provider;
  public $latitude;
  public $longitude;
  public $building;
  public $floor;
  public $note;
  public $use;
  public $disabled;
  public $ts_created;
  public $ts_last_edit;
  
  public function __construct($networkData = []){

    foreach($networkData as $k => $v){
      $this->{$k} = $v;
    }
    
  }
  
  public function isValid(){
    return ($this->cidr !== null);
  }
}