<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CampusGeoIP\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Piwik\Plugins\CampusGeoIP\CampusGeoIP;

class GeoLocate extends ConsoleCommand
{
    
    const IP_ARGUMENT = "ipaddresses";

    protected function configure()
    {
        $this->setName('campusgeoip:geolocate');
        $this->setDescription('Geo Locate IP Addresses (comma seperated)');
        $this->addArgument(self::IP_ARGUMENT, InputArgument::REQUIRED, 'IP Addresses (csv)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $csvIpAddresses = $input->getArgument(self::IP_ARGUMENT);
				
        if(strpos($csvIpAddresses,",") !== false){
            $ipAddresses = explode(",",$csvIpAddresses);
            $matches = CampusGeoIP::findMatches($ipAddresses);
        }
        else{
            $matches = CampusGeoIP::findMatch($csvIpAddresses);
        }

        $output->write(print_r($matches,true));
    }
}