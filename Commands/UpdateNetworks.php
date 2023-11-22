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

class UpdateNetworks extends ConsoleCommand
{
  const IMPORTLOCATION_ARGUMENT = "import-location";

  protected function configure()
  {
    $this->setName('campusgeoip:update-networks');
    $this->setDescription('UpdateNetworks');
    $this->addRequiredArgument(self::IMPORTLOCATION_ARGUMENT, 'Import Location (path to networks data file):');
  }

  protected function doExecute(): int
  {
    $input  = $this->getInput();
    $output = $this->getOutput();

    $campusGeoIp = new CampusGeoIP();
    $campusGeoIp->setOutput($output);
    
    $importLocation = $input->getArgument(self::IMPORTLOCATION_ARGUMENT);
    
    $output->writeln("Importing From: $importLocation");
    
    $campusGeoIp->importNetworksFromSource($importLocation);

    return self::SUCCESS;
  }
}
