<?php

/**
 * Index file for the config generator
 *
 * @author Francis Genet
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

// Just to be sure
set_time_limit(5);

require_once 'bootstrap.php' ;
require_once 'classes/settings.php';

// Load the settings
$objSettings = new Settings();
$settings = $objSettings->getSettings();

// Databse Based
if (!isset($argv)) {
    $uri = strtolower($_SERVER['REQUEST_URI']);
    $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
    $http_host = strtolower($_SERVER['HTTP_HOST']);

    // YEALINK - t2x
    //$uri = "/002e3a6fe532d90943e6fcaf08e1a408/y000000000005.cfg";
    //$uri = "/002e3a6fe532d90943e6fcaf08e1a408/0015652103df.cfg";
    //$ua = strtolower("yealink SIP-T22P 7.61.0.80 00:15:65:21:03:df");

    // t3x
    //$uri = "/002e3a6fe532d90943e6fcaf08e1a408/y000000000038.cfg";
    //$uri = "/002e3a6fe532d90943e6fcaf08e1a408/00156527c764.cfg";
    //$ua = strtolower("Yealink SIP-T38G  38.0.0.105 00:15:65:27:c7:64");

    // Polycom
    //$ua = strtolower("FileTransport PolycomSoundStationIP-SSIP_5000-UA/4.0.3.7562 (SN:0004f2e765da) Type/Application");
    //$uri = "/002e3a6fe532d90943e6fcaf08e1a408/0004f2e765da_reg.cfg";

    // Cisco
    //$ua = "Cisco/SPA504G-7.4.9c (649EF3788E6A)(CCQ162306EA)";
    //$uri = "/002e3a6fe532d90943e6fcaf08e1a408/spa504g.cfg";

    // Load the config manager
    // This will return a config_manager
    $config_manager_name = "ConfigGenerator_" . $settings->config_manager;
    $config_generator = new $config_manager_name();

    $config_manager = $config_generator->get_config_manager($uri, $ua, $http_host, $settings);
    $config_manager->set_request_type('http');

    echo $config_manager->generate_config_file();

// CLI Based
} else {
    // Just making sure that everything is where it should be
    if(!isset($argv[1]) || !isset($argv[2]) || !isset($argv[3])) {
        die("Usage: php process.php <brand> <model> <source_file_path>\n");
    }

    $brand = strtolower($argv[1]);
    $model = strtolower($argv[2]);
    $source_file_path = $argv[3];

    if(!file_exists($source_file_path)) {
        die("File " . $source_file_path . " does not exist!\n");
    }

    $arrConfig = json_decode(file_get_contents($source_file_path), true);
    if(json_errors()) {
        die("FATAL: " . ProvisionerUtils::json_errors() . "\n");
    }

    // This is generator is generic and is basically building a simple config manager
    // with a minimum of information (brand/model/a file containing the settings)
    $config_generator = new ConfigGenerator_generic();
    $config_manager = $config_generator->get_config_manager($brand, $model, $arrConfig);
    $config_manager->set_request_type('tftp');

    foreach (ProvisionerUtils::get_file_list($brand, $model) as $value) {
        $config_manager->set_config_file($value);

        // make a file with the returned value
        // This is not doing it for now, it will need to be implemented
        echo $config_manager->generate_config_file();
    }
}
