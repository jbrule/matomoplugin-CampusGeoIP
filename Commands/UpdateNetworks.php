<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CampusGeoIP\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Piwik\Plugins\CampusGeoIP\CampusGeoIP;

class UpdateNetworks extends ConsoleCommand
{
  const IMPORTLOCATION_ARGUMENT = "import-location";

  protected function configure()
  {
    $this->setName('campusgeoip:update-networks');
    $this->setDescription('UpdateNetworks');
    $this->addArgument(self::IMPORTLOCATION_ARGUMENT, InputArgument::REQUIRED, 'Import Location (path to networks data file):');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $campusGeoIp = new CampusGeoIP();
    $campusGeoIp->setOutput($output);
    
    $importLocation = $input->getArgument(self::IMPORTLOCATION_ARGUMENT);
    
    $output->writeln("Importing From: $importLocation");
    
    $campusGeoIp->importNetworksFromSource($importLocation);
  }
}
