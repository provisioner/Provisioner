<?php  

/**
* Yealink test related functions
*/
require_once "../bootstrap.php";
require_once '../classes/settings.php';

class YealinkTest extends PHPUnit_Framework_TestCase
{
    public function testT2x() {
        $objSettings = new Settings();
        $settings = $objSettings->getSettings();

        $uri = "/002e3a6fe532d90943e6fcaf08e1a420/0015651ab7b5.cfg";
        $ua = "Yealink SIP-T22P 3.2.2.1136 00:15:65:1a:b7:b5";
        $http_host = "10.10.9.79";

        // Load the config manager
        // This will return a config_manager
        $config_manager_name = "ConfigGenerator_" . $settings->config_manager;
        $config_generator = new $config_manager_name();

        $config_manager = $config_generator->get_config_manager($uri, $ua, $http_host, $settings);
        $config_manager->set_request_type('http');

        $config_file = $config_manager->generate_config_file();

        $this->assertContains("Label = 7930: Francis", $config_file, "There might be a problem with the T2x configuration");
    }
}

?>