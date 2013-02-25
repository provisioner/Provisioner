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

// Load the settings
$objSettings = new helper_settings();
$settings = $objSettings->getSettings();

// Databse Based
if (!isset($argv)) {
    $uri = strtolower($_SERVER['REQUEST_URI']);
    $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
    $http_host = strtolower($_SERVER['HTTP_HOST']);

    // Load the configuration adapter (converts format from FreePBX/Kazoo/etc. to a standard format)
    // This will return a class which will pre-process configurations
    $adapter_name = "adapter_" . $settings->adapter . "_adapter";
    $adapter = new $adapter_name();

    $config_manager = $adapter->get_config_manager($uri, $ua, $http_host, $settings);
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

    // This is adapter is generic and is basically building a simple config manager
    // with a minimum of information (brand/model/a file containing the settings)
    $adapter = new adapter_generic_adapter();
    $config_manager = $adapter->get_config_manager($brand, $model, $arrConfig);
    $config_manager->set_request_type('tftp');

    foreach (ProvisionerUtils::get_file_list($brand, $model) as $value) {
        $config_manager->set_config_file($value);

        // make a file with the returned value
        // This is not doing it for now, it will need to be implemented
        echo $config_manager->generate_config_file();
    }
}
