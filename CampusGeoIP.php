<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CampusGeoIP;

use Piwik\Common;
use Piwik\Http;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Network\IP;
use Piwik\Network\IPUtils;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\CampusGeoIP\Network;
use Piwik\Plugins\CampusGeoIP\Console;

class CampusGeoIP extends \Piwik\Plugin
{
  const TBL_LOCATIONS = "campusgeoip_locations";
  const TBL_NETWORKS = "campusgeoip_networks";

  const COLUMN_TEMPLATE = "NetworkFirstAsInteger|NetworkLastAsInteger|NetworkAsInteger|NetworkWithCIDR|NetworkAddress|NetworkCIDR|NetworkMask|NetworkHostCount|NetworkComment|Region|CampusCode|BuildingCode|FloorCode|Use|Disabled";
  const DATA_COLUMN_MAP = ["cidr"=>3,"note"=>8,"region"=>9,"campus"=>10,"building"=>11,"floor"=>12,"use"=>13,"disabled"=>14];
  const DELIMITER = "|";
  const START_OFFSET = 1;
  const UNRESOLVED_NOTE = "Not Found";

  private $console;

  public function __construct()
  {
    $this->console = new Console();
    
    parent::__construct();
  }
    
  public function isTrackerPlugin()
  {
    return true;
  }
  
  public function setOutput($output){
    $this->console->setOutput($output);
  }

  public function deactivate()
  {
    // switch to default provider
    if (LocationProvider::getCurrentProvider() instanceof \Piwik\Plugins\CampusGeoIP\LocationProvider\CampusGeoIPLocationProvider) {
      LocationProvider::setCurrentProvider(LocationProvider\DefaultProvider::ID);
    }
  }

  public function install()
  {
    DbHelper::createTable(self::TBL_LOCATIONS,"
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `city` varchar(50) NOT NULL DEFAULT '',
    `campus` char(2) DEFAULT NULL,
    `region` char(4) NOT NULL DEFAULT '',
    `country` char(3) NOT NULL DEFAULT '',
    `org` varchar(200) NOT NULL DEFAULT '',
    `provider` varchar(200) NOT NULL DEFAULT '',
    `latitude` float(9,6) NOT NULL,
    `longitude` float(9,6) NOT NULL,
    `ts_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ts_last_edit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`),
    UNIQUE KEY `campus` (`campus`)");

    DbHelper::createTable(self::TBL_NETWORKS,"
    `cidr` varchar(43) NOT NULL,
    `campus` char(2) DEFAULT NULL,
    `region` varchar(200) DEFAULT NULL,
    `building` varchar(50) DEFAULT NULL,
    `floor` varchar(50) DEFAULT NULL,
    `note` varchar(500) DEFAULT NULL,
    `network_start` varbinary(16) DEFAULT NULL,
    `network_end` varbinary(16) DEFAULT NULL,
    `use` varchar(50) DEFAULT NULL,
    `disabled` tinyint(1) DEFAULT NULL,
    `ts_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ts_last_edit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `ts_removed` timestamp NULL DEFAULT NULL,
    UNIQUE KEY `cidr` (`cidr`),
    UNIQUE KEY `network_start` (`network_start`,`network_end`),
    KEY `campus` (`campus`),
    KEY `ts_removed` (`ts_removed`)");
  }

  public function uninstall()
  {
    Db::dropTables([Common::prefixTable(self::TBL_LOCATIONS),Common::prefixTable(self::TBL_NETWORKS)]);
  }

  public function importNetworksFromSource($importLocation){

    // Handle Url
    if($importLocationUrl = filter_var($importLocation,FILTER_VALIDATE_URL)){

      $httpResponse = HTTP::fetchRemoteFile($importLocationUrl);
            
      if($httpResponse !== false){
        $this->updateNetworkData($httpResponse);
      }else{
        $this->console->write("Failed to get data file");
      }
    }
    // Handle File
    else if(is_file($importLocation)){

      $this->console->write("Detected Local File Exists. Attempting Import...");
       
      try{
        $fileContent = file_get_contents($importLocation);
        $this->updateNetworkData($fileContent);
      }
      catch(Exception $err){
        $this->console->write("Failed to load the data file");
        $this->console->write($err.getMessage());
      }

    }else{
      $this->console->write("Provided location failed to validate");
    }
  }

  private function updateNetworkData($delimitedData){
        
    $datasetCount = 0;
    $datasetCurrentIndex = 0;
    $importTime = Date::now();
    $campusCodes = [];
    
    $importedLines = array_filter(explode(PHP_EOL,$delimitedData));

    if(count($importedLines) === 0){
      $this->console->write("File Appears to be empty");
      return false;
    }

    if(!$this->isFormatValid($importedLines[0])){
      $this->console->write("File format is invalid");
      return false;
    }

    $updateClause = join(",", array_map(function($column_key){
      return sprintf("`%s`=VALUES(`%s`)",$column_key,$column_key);
      },array_merge(array_keys(self::DATA_COLUMN_MAP),["ts_last_edit","ts_removed"])));

    $datasetCount = count($importedLines) - self::START_OFFSET;

    for($i = self::START_OFFSET; $i <= $datasetCount; $i++){
      $datasetCurrentIndex = $i;

      $explodedLine = explode(self::DELIMITER,$importedLines[$i]);

      $associativeLine = [];

      foreach(self::DATA_COLUMN_MAP as $destColumnKey => $sourceColumnIndex){
        $associativeLine[$destColumnKey] = (!empty($explodedLine[$sourceColumnIndex]))? $explodedLine[$sourceColumnIndex] : null;
      }

      $campusCodes[] = $associativeLine["campus"];

      $associativeLine["disabled"] = (strtolower($associativeLine["disabled"]) === "true");

      list($associativeLine["network_start"], $associativeLine["network_end"]) = IPUtils::getIPRangeBounds($associativeLine["cidr"]);

      $associativeLine["ts_created"] = $associativeLine["ts_last_edit"] = $importTime->getDatetime();
      $associativeLine["ts_removed"] = null;

      $fieldList = '(`' . join('`,`', array_keys($associativeLine)) . '`)';

      $queryInsert = "INSERT INTO " . Common::prefixTable(self::TBL_NETWORKS) . "
      $fieldList
      VALUES (" . Common::getSqlStringFieldsArray($associativeLine) . ")
      ON DUPLICATE KEY UPDATE $updateClause";

      $message = sprintf("Processing %d of %d networks	%d%%", $i, $datasetCount, round($i / $datasetCount * 100,0));

      $this->console->write("\x0D", false);
      $this->console->write($message, false);

      try{
        Db::query($queryInsert,array_values($associativeLine));
      }
      catch(Exception $err){
        $this->console->write("An error occurred while trying to load the network data.");
        $this->console->write($err.getMessage());
        $this->console->write("[DEBUG] QUERY SQL:". var_export($queryInsert,true));
        $this->console->write("[DEBUG] QUERY VALUES:". var_export($associativeLine,true));
      }
    }

    $campusCodes = array_unique($campusCodes);

    $this->console->write("--Finding missing locations--");
    $this->auditCampuses($campusCodes);
    $this->console->write("--Flagging removed networks--");
    $this->markRemoved($importTime);
  }

  public function testAgainstVisitLog($limit = 1){

    $limit = intval($limit);
    
    $queryTest = sprintf("SELECT DISTINCT inet_ntoa(conv(hex(location_ip), 16, 10)) as ip FROM %s WHERE idvisit IN 
    (SELECT idvisit FROM (SELECT idvisit FROM %s ORDER BY RAND() LIMIT %d) t)", Common::prefixTable("log_visit"), Common::prefixTable("log_visit"), $limit);
    
    //$queryTest = sprintf("SELECT DISTINCT inet_ntoa(conv(hex(location_ip), 16, 10)) as ip FROM %s LIMIT %d");
    $this->console->write("Getting Test IPs from visitor log");
    $startTime = time();
    $testResult = Db::fetchAll($queryTest);
    $this->console->write(sprintf("Test IP Retrieval Time: %d seconds",time() - $startTime));
        
    $testIps = array_map(function($row){ return $row["ip"];},$testResult);

    $startTime = time();
        
    $matches = self::findMatches($testIps, $this->console);
    
    $this->console->write($matches);
        
    $this->console->write(sprintf("Query Time: %d seconds",time() - $startTime));

    $unresolvedIps = array_map(function($result){
      return $result->ip;
      },
      array_filter($matches,
        function($result){
          return (!$result->isValid());
        }
      )
    );
    
    $this->console->write(sprintf("--Unresolved IPs--%s%s", PHP_EOL, join(",",$unresolvedIps)));
  }

  private function auditCampuses($campuses){

    $querySelect = sprintf("SELECT l.city,l.campus,l.region FROM %s l WHERE l.campus IS NOT NULL ORDER BY l.city ASC", Common::prefixTable(self::TBL_LOCATIONS));

    $existingCampuses = Db::fetchAll($querySelect);

    $missingLocations = array_diff($campuses,array_map(function($location){ return $location["campus"]; },$existingCampuses));

    $message = sprintf("Locations could not be found for the following campuses%s%s", PHP_EOL, implode(PHP_EOL, $missingLocations));

    $this->console->write($message);
  }

  private function markRemoved($importTime){

    $queryUpdate = sprintf("UPDATE %s SET ts_removed = ? WHERE ts_last_edit < ? AND ts_removed IS NULL", Common::prefixTable(self::TBL_NETWORKS));

    $removalResult = Db::query($queryUpdate, [$importTime->getDatetime(),$importTime->getDatetime()]);

    $message = sprintf("%d Networks were marked as removed",$removalResult->rowCount());

    $this->console->write($message);
  }

  private function isFormatValid($header){
    return (trim($header) === trim(self::COLUMN_TEMPLATE));
  }
    
    public static function findMatches($ipAddresses = [], $console = null){

    if(! is_array($ipAddresses)){
      $ipAddresses = [$ipAddresses];
    }
        
    if($console === null){
      $console = new Console();
    }

    $matchCount = count($ipAddresses);

    if($matchCount > 1000){
      $errMessage = ["Error"=> "Lookup limit is 1000 records. Please retry with a smaller request."];
      $console->write($errMessage);
      return $errMessage;
    }

    $iter = 0;

    $results = [];

    foreach($ipAddresses as $ipAddress){
      $iter++;

      $results[] = self::findMatch($ipAddress);

      $message = sprintf("Processing %d of %d matches	%d%%", $iter, $matchCount, round($iter / $matchCount * 100,0));
      $console->write("\x0D", false);
      $console->write($message, false);
    }

    return $results;
  }

  public static function findMatch($ipAddress = ""){

    $ipAddress = IPUtils::sanitizeIp($ipAddress);
    
    $binIpAddress = IPUtils::stringToBinaryIP($ipAddress);
    
    $querySelect = sprintf("SELECT ? as ip,n.cidr,l.city,l.campus,l.region,l.country,l.org,l.provider,l.latitude,l.longitude,n.building,n.floor,n.note,n.use,n.disabled,n.ts_created,n.ts_last_edit
    FROM %s n LEFT JOIN %s l ON n.campus = l.campus
    WHERE ts_removed IS NULL AND ? BETWEEN n.network_start AND n.network_end ORDER BY n.network_start DESC, n.network_end ASC LIMIT 1",
    Common::prefixTable(self::TBL_NETWORKS), Common::prefixTable(self::TBL_LOCATIONS));

    $result = Db::fetchRow($querySelect,[$ipAddress,$binIpAddress]) ?: ["ip" => $ipAddress, "note" => self::UNRESOLVED_NOTE];
    
    return new Network($result);
  }

  public static function isPopulated(){

    $locationCount = Db::fetchOne(sprintf("SELECT COUNT(*) FROM %s", Common::prefixTable(self::TBL_LOCATIONS)));
    $networkCount = Db::fetchOne(sprintf("SELECT COUNT(*) FROM %s", Common::prefixTable(self::TBL_NETWORKS)));

    return ($locationCount > 0 && $networkCount > 0);
  }
}
