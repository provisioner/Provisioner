<?php
require_once('bootstrap.php');

// Gather customer information
$account_id = "12983471298471294";

// Define what you want to generate
$brand = 'polycom';
$family = 'spipm';
$model = 'SoundPoint IP 550';

// Create a class name based on the arguments provided
$class = "endpoint_" . $brand . "_" . $family . '_phone';

// Instantiate the class
$endpoint = new $class();

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

$final_settings = array();
// Import the global overall system default settings
import_settings("defaults.json", $final_settings);

// Add/Overlay settings for a particular provider (2600hz, Packet8, whomever)
import_settings("provider.json", $final_settings);

// Add/Overlay settings for a particular group (a Customer Account, or a Group of Phones in General)
import_settings("group.json", $final_settings);

// Add/Overlay settings for a particular phone/MAC address
import_settings("phone.json", $final_settings);


// Note that the above is pretty flexible. For example, you could have done:
// import_settings(file_get_contents("http://remote.server.com/test.json"), $final_settings);
// the content could also come from a database, or API call, or otherwise


// View final settings
var_dump($final_settings);

// Loop through all required files for this particular brand/model and produce them
$files = array('$mac.cfg');
foreach ($files as $file) {
    $template = $twig->loadTemplate($file);

    // Generate template using these settings
    $result = $template->render($final_settings);
    
    // TODO: Actual output as a file or to the requestor somehow
    echo $result;
}
