<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CampusGeoIP;

use Piwik\Piwik;
use Piwik\Plugins\UserCountry\UserCountry;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\LocationProvider\DefaultProvider;
use Piwik\Plugins\UserCountry\LocationProvider\DisabledProvider;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;
use Piwik\Validators\NotEmpty;

/**
 * Defines Settings for CampusGeoIP.
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{

  /** @var Setting */
  public $useFallback;
  
  protected $fallbackOptions = [];

  protected function init()
  {
    $this->title = ' Campus Geo IP';

    $geoIpAdminEnabled = UserCountry::isGeoLocationAdminEnabled();
    $this->fallbackOptions['default'] = 'Default';
  
    foreach(LocationProvider::getAvailableProviders() as $provider){
      $info = $provider->getInfo();
      if ($info['id']!=='campusgeoip'
        && $info['id']!==DefaultProvider::ID
        && $info['id']!==DisabledProvider::ID
        && $provider->isWorking()===true) {
        $this->fallbackOptions[$info['id']] = $info['title'];
      }
    }
    if (count($this->fallbackOptions) > 0 ){
      $this->useFallback = $this->makeSetting('fallback', 'default', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
        $field->title = 'Fallback location provider';
        $field->uiControl = FieldConfig::UI_CONTROL_RADIO;
        $field->availableValues = $this->fallbackOptions;
        $field->description = 'Choose an alternative location provider if the IP address was not found in campus range.';
      });
      $this->useFallback->setIsWritableByCurrentUser($geoIpAdminEnabled);
    }
  }
}