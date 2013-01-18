<?php

/**
 * Index file for the config generator
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

// Just to be sure
set_time_limit(5);

require_once 'bootstrap.php' ;
require_once 'classes/settings.php';

$uri = strtolower($_SERVER['REQUEST_URI']);
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
$http_host = strtolower($_SERVER['HTTP_HOST']);

// YEALINK
//$uri = "/002e3a6fe532d90943e6fcaf08e1a408/001565000000.cfg";
//$ua = strtolower("Yealink SIP-T22P 3.2.2.1136 00:15:65:00:00:00");

// Polycom
//$ua = strtolower("FileTransport PolycomSoundStationIP-SSIP_5000-UA/4.0.3.7562 Type/Application");

// Load the settings
$objSettings = new Settings();
$settings = $objSettings->getSettings();

// HTTP
if ($settings->server_type == "http") {
    // Load the config manager
    // This will return a config_manager
    $config_generator = new $this->config_manager();
    $config_manager = $config_generator->get_config_manager($uri, $ua, $http_host, $settings);

    // Set the file that will be genrated
    $target = ProvisionerUtils::strip_uri($uri);
    $config_manager->set_config_file($target);
    
    echo $config_manager->generate_config_file();

// TFTP
} elseif ($settings->server_type == "tftp") {
    echo "TBD";
}

