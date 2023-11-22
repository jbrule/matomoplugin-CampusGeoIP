<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CampusGeoIP\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CampusGeoIP\CampusGeoIP;

class GeoLocate extends ConsoleCommand
{
  
  const IP_ARGUMENT = "ipaddresses";

  protected function configure()
  {
    $this->setName('campusgeoip:geolocate');
    $this->setDescription('Geo Locate IP Addresses (comma seperated)');
    $this->addRequiredArgument(self::IP_ARGUMENT, 'IP Addresses (csv)');
  }

  protected function doExecute(): int
  {
    $input  = $this->getInput();
    $output = $this->getOutput();

    $csvIpAddresses = $input->getArgument(self::IP_ARGUMENT);
    
    if(strpos($csvIpAddresses,",") !== false){
      $ipAddresses = explode(",",$csvIpAddresses);
      $matches = CampusGeoIP::findMatches($ipAddresses);
    }
    else{
      $matches = CampusGeoIP::findMatch($csvIpAddresses);
    }
    
    $output->write(print_r($matches,true));

    return self::SUCCESS;
  }
}