<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CampusGeoIP;

class Console {
    
    private $output = null;
    private $outputSet = false;
    
    public function __construct(){
    }
    
    public function setOutput($output){
        $this->output = $output;
        $this->outputSet = true;
    }
    
    public function write($message, $newLine = true){
        if($this->outputSet){
            $message = is_string($message)? $message : print_r($message,true);

			if($newLine){
				$this->output->writeln(PHP_EOL.$message);
			}
			else
			{
				$this->output->write($message);
			}
        }
    }
}