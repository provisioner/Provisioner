#!/usr/bin/php
<?php

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

//$brand = $_POST['brand'];
//$family = $_POST['family'];
//include ("endpoint_config_gen.class.inc");
//include (PHONE_MODULES_PATH . $brand . '/functions.inc');
//include (PHONE_MODULES_PATH . $brand . '/' . $family . '/functions.inc');

$class = "endpoint_" . $brand . "_" . $family . '_phone';

$endpoint = new $class();

//Mac Address
$endpoint->mac = '000B820D0057';

//Phone Model (Please reference family_data.xml in the family directory for a list of recognized models)
$endpoint->model = "SoundPoint IP 550";

//Timezone
$endpoint->timezone = 'GMT-11:00';

//Server IP
$endpoint->srvip = "10.10.10.10";

//Provide alternate Configuration file instead of the one from the hard drive
//$endpoint->config_files_override['server.cfg'] = "CFG FILE DATA";

//Pretend we have three lines, we could just have one line or 20...whatever the phone supports
$endpoint->ext['line'][1] = '103';
$endpoint->secret['line'][1] = 'blah';
$endpoint->displayname['line'][1] = "Display Name";

$endpoint->ext['line'][2] = '104';
$endpoint->secret['line'][2] = 'blah4';
$endpoint->displayname['line'][2] = "Display Name4";

$endpoint->ext['line'][3] = '105';
$endpoint->secret['line'][3] = 'blah5';
$endpoint->displayname['line'][3] = "Display Name5";

//Set Variables according to the template_data files included. We can include different template.xml files within family_data.xml also one can create
//template_data_custom.xml which will get included or template_data_<model_name>_custom.xml which will also get included
//line 'global' will set variables that aren't line dependant
$endpoint->xml_variables['line']['global'] = 	array(
														"admin_pass" => array("value" => "password")
													);
/*													
$endpoint->xml_variables['line'][1] = 	array(
														"idle_display_refresh" => array("value" => "500"),
														"microbrowser_main_home" => array("value" => "Microbrowser!")
													);
$endpoint->xml_variables['line'][2] = 	array(
														"idle_display_refresh" => array("value" => "500"),
														"microbrowser_main_home" => array("value" => "Microbrowser!")
													);
$endpoint->xml_variables['line'][3] = 	array(
														"idle_display_refresh" => array("value" => "500"),
														"microbrowser_main_home" => array("value" => "Microbrowser!")
													);
*/
//Setting a line variable here...these aren't defined in the template_data.xml file yet. however they will still be parsed 
//and if they have defaults assigned in a future template_data.xml or in the config file using pipes (|) those will be used, pipes take precedence
$endpoint->xml_variables['line'][3] = 	array(
														"main_url" => array("value" => "Main URL Line #3"),
														"digitmap" => array("value" => "DIGI!"),
														"main_icon" => array("value" => "Main ICON Line #3")
													);

// Because every brand is an extension (eventually) of endpoint, you know this function will exist regardless of who it is
$returned_data = $endpoint->generate_config();


print_r($returned_data);

?>