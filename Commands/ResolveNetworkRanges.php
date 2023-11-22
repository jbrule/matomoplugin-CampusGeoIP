<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CampusGeoIP\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CampusGeoIP\CampusGeoIP;

class ResolveNetworkRanges extends ConsoleCommand
{

  protected function configure()
  {
    $this->setName('campusgeoip:resolve-network-ranges');
    $this->setDescription('Resolve the network_start and network_end values for networks with cidr and NULL ranges. Useful if the networks table is populated by external means (Sequel Ace, phpMyAdmin, mysql client)');
  }

  protected function doExecute(): int
  {
    $input  = $this->getInput();
    $output = $this->getOutput();

    $campusGeoIp = new CampusGeoIP();
    $campusGeoIp->setOutput($output);
    
    $campusGeoIp->resolveNetworkRanges();

    return self::SUCCESS;
  }
}
