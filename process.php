#!/usr/bin/php
<?php
/**
 * Demo Script for Provisioner
 *
 * @author Darren Schreiber & Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
include('setup.php');


// Allow running this test from the command line
if (isset($_POST['brand'])) {
    $brand = $_POST['brand'];
} else {
    $brand = $argv[1];
}

if (isset($_POST['family'])) {
    $family = $_POST['family'];
} else {
    $family = $argv[2];
}

if (isset($_POST['model'])) {
    $model = $_POST['model'];
} else {
    $model = $argv[3];
}

$class = "endpoint_" . $brand . "_" . $family . '_phone';

$endpoint = new $class();

//have to because of versions less than php5.3
$endpoint->brand_name = $brand;
$endpoint->family_line = $family;

//Mac Address
$endpoint->mac = '000B820D0057';

//Phone Model (Please reference family_data.xml in the family directory for a list of recognized models)
$endpoint->model = $model;

//Timezone
$endpoint->timezone = 'GMT-11:00';

//Server IP
$endpoint->server[1]['ip'] = "10.10.10.10";
$endpoint->server[1]['port'] = 5060;

//Provide alternate Configuration file instead of the one from the hard drive
//$endpoint->config_files_override['$mac.cfg'] = "{\$provider_lock|0}\n{\$provider_lock.line.8|0}\n{\$firmware_upgrade_type1.line.1}\n {\$line_active.line.2|0}";

//Pretend we have three lines, we could just have one line or 20...whatever the phone supports
$endpoint->lines[1] = array('ext' => '103', 'secret' => 'blah', 'displayname' => 'Joe Blow');
$endpoint->lines[2] = array('ext' => '104', 'secret' => 'blah4', 'displayname' => 'Display Name');
$endpoint->lines[3] = array('ext' => '105', 'secret' => 'blah5', 'displayname' => 'Other Account');


//Set Variables according to the template_data files included. We can include different template.xml files within family_data.xml also one can create
//template_data_custom.xml which will get included or template_data_<model_name>_custom.xml which will also get included
//line 'global' will set variables that aren't line dependant
$endpoint->options = 	array(
        "admin_pass" => array("value" => "password"),
        "main_icon" => array("value" => "Main ICON Line #3")
);
//Setting a line variable here...these aren't defined in the template_data.xml file yet. however they will still be parsed 
//and if they have defaults assigned in a future template_data.xml or in the config file using pipes (|) those will be used, pipes take precedence


// Because every brand is an extension (eventually) of endpoint, you know this function will exist regardless of who it is
$returned_data = $endpoint->generate_config();

if(isset($_POST['brand'])) {
    foreach($returned_data as $key => $files) {
        echo 'File:'.$key.'<br/><textarea rows="50" cols="100">'.$files.'</textarea><br/><br/>';
    }
} else {
    print_r($returned_data);
}

