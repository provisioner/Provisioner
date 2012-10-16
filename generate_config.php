<?php
require_once('bootstrap.php');

// Gather customer information
$account_id = "12983471298471294";

// Define what you want to generate
$brand = 'yealink';
$family = 't2x';
$model = 'T26';

// Create a class name based on the arguments provided
$class = "endpoint_" . $brand . "_" . $family . '_phone';

// Instantiate the class
$endpoint = new $class('T26');

/*
$endpoint->brand_name = $brand;
$endpoint->family_line = $family;
$endpoint->model = $model;
$endpoint->mac = '0123456789AB';
$endpoint->processor_info = 'PHPUNIT TESTING';

if (!class_exists("DateTimeZone")) { require(PROVISIONER_BASE.'/samples/tz.php'); }
$endpoint->DateTimeZone = new DateTimeZone('America/Los_Angeles');;

$endpoint->settings['line'][0] = array(
        "username" => "username_l1",
        "secret" => "secret_l1",
        "server_host" => "server_host_l1",
        "server_port" => "server_port_l1",
        "displayname" => "displayname_l1",
        "line" => "1"
);
$unmodified_settings = $endpoint->settings;
var_dump($endpoint->generate_all_files());
 * 
 */

// Import the global overall system default settings
$endpoint->import_settings("defaults.json");

// Add/Overlay settings for a particular provider (2600hz, Packet8, whomever)
$endpoint->import_settings("provider.json");

// Add/Overlay settings for a particular group (a Customer Account, or a Group of Phones in General)
//$endpoint->import_settings("group.json");

// Add/Overlay settings for a particular phone/MAC address
$endpoint->import_settings("phone.json");


// Note that the above is pretty flexible. For example, you could have done:
// import_settings(file_get_contents("http://remote.server.com/test.json"), $final_settings);
// the content could also come from a database, or API call, or otherwise


// View final settings
//print_r($endpoint->settings);

// Loop through all required files for this particular brand/model and produce them
print_r($endpoint->generate_all_files());