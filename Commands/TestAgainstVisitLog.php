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

class TestAgainstVisitLog extends ConsoleCommand
{
  
  const LIMIT_ARGUMENT = "limit";

  protected function configure()
  {
    $this->setName('campusgeoip:test-against-visit-log');
    $this->setDescription('Harvests IPs from visitor log and tests them against resolver database.');
    $this->addRequiredArgument(self::LIMIT_ARGUMENT, 'Match Limit:');
  }

  protected function doExecute(): int
  {
    $input  = $this->getInput();
    $output = $this->getOutput();

    $campusGeoIp = new CampusGeoIP();
    $campusGeoIp->setOutput($output);
    
    $limit = $input->getArgument(self::LIMIT_ARGUMENT);
    
    $campusGeoIp->testAgainstVisitLog($limit);

    return self::SUCCESS;
  }
}
