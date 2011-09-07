<?php
/**
 * Demo Script for Provisioner
 *
 * @author Darren Schreiber & Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
echo "<pre>";
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
define('PROVISIONER_BASE', '../');

//Get line options
foreach($_REQUEST as $key => $data) {
	if(preg_match("/line\|(.*)\|(.*)/i",$key,$matches)) {
		$stuff = $matches;
		$line = $stuff[1];
		$var = $stuff[2];
		$req = $stuff[0];
	
		$line_options[$line]['options'][$var] = $_REQUEST[$req];
		unset($_REQUEST[$req]);
	}elseif(preg_match("/loop\|(.*)_(.*)_([\d]*)/i",$key,$matches)) {
		$stuff = $matches;
		
		$loop = $stuff[1];
		$var = $stuff[2];
		$count = $stuff[3];
		$req = $stuff[0];
	
		$loops_options[$loop][$count][$var] = $_REQUEST[$req];
		unset($_REQUEST[$req]);
	}elseif(preg_match("/option\|(.*)/i",$key,$matches)) {
		$stuff = $matches;
		$var = $stuff[1];
		$req = $stuff[0];

		$options[$var] = $_REQUEST[$req];
		unset($_REQUEST[$req]);
	}elseif(preg_match("/line_static\|(.*)\|(.*)/i",$key,$matches)) {
		$stuff = $matches;
		$line = $stuff[1];
		$var = $stuff[2];
		$req = $stuff[0];

		$line_static[$line][$var] = $_REQUEST[$req];
		unset($_REQUEST[$req]);
	}
}
$final_ops = array_merge($loops_options,$options);

include('../autoload.php');

$brand = $_REQUEST['brand'];
$family = $_REQUEST['product'];
$model = $_REQUEST['model'];

$class = "endpoint_" . $brand . "_" . $family . '_phone';

$endpoint = new $class();

//have to because of versions less than php5.3
$endpoint->brand_name = $brand;
$endpoint->family_line = $family;

$endpoint->processor_info = "Web Provisioner 2.0";

//Mac Address
$endpoint->mac = $_REQUEST['mac'];

//Phone Model (Please reference family_data.xml in the family directory for a list of recognized models)
$endpoint->model = $model;

//Timezone
if (!class_exists("DateTimeZone")) { require('tz.php'); }
$endpoint->DateTimeZone = new DateTimeZone($_REQUEST['timezone']);;

//Server IP Address & Port
$endpoint->server[1]['ip'] = $_REQUEST['server'];
$endpoint->server[1]['port'] = 5060;

//Backup Server Address
$endpoint->server[2]['ip'] = "20.20.20.20";
$endpoint->server[2]['port'] = 7000;

//Provide alternate Configuration file instead of the one from the hard drive
//$endpoint->config_files_override['$mac.cfg'] = "{\$srvip}\n{\$admin_pass|0}\n{\$test.line.1}";

foreach($line_static as $key => $data) {
	$endpoint->lines[$key] = $data;
}

foreach($line_options as $key => $data) {
	$endpoint->lines[$key]['options'] = $data['options'];
}

//Set Variables according to the template_data files included. We can include different template.xml files within family_data.xml also one can create
//template_data_custom.xml which will get included or template_data_<model_name>_custom.xml which will also get included
//line 'global' will set variables that aren't line dependant
$endpoint->options =  $final_ops;
//Setting a line variable here...these aren't defined in the template_data.xml file yet. however they will still be parsed 
//and if they have defaults assigned in a future template_data.xml or in the config file using pipes (|) those will be used, pipes take precedence


// Because every brand is an extension (eventually) of endpoint, you know this function will exist regardless of who it is
$returned_data = $endpoint->generate_config();
ksort($returned_data);

if (isset($_REQUEST['brand'])) {
    foreach($returned_data as $key => $files) {
        echo 'File:'.$key;
        if(in_array($key, $endpoint->protected_files)){
                echo " [<b>PROTECTED</b>]";
        }
        echo '<br/><textarea rows="50" cols="100">'.$files.'</textarea><br/><br/>';
    }
} else {
    print_r($returned_data);
}
