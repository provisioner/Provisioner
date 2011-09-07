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
	}
}

$final_ops = array_merge($loops_options,$options);

include('../autoload.php');

// Allow running this test from the command line
if (isset($_REQUEST['brand'])) {
        $brand = $_REQUEST['brand'];
} else {
    $brand = $argv[1];
}

if (isset($_REQUEST['family'])) {
    $family = $_REQUEST['family'];
} elseif (isset($_REQUEST['product'])) {
	$family = $_REQUEST['product'];
} elseif (isset($_REQUEST['model_demo'])) {
    $temp = explode('+',$_REQUEST['model_demo']);
    $family = $temp[0];
} else {
    $family = $argv[2];
}

if (isset($_REQUEST['model'])) {
	$model = $_REQUEST['model'];
} elseif (isset($_REQUEST['model_demo'])) {
    $temp = explode('+',$_REQUEST['model_demo']);
    $model = $temp[1];
} else {
    $model = $argv[3];
}

date_default_timezone_set('America/Los_Angeles');

$class = "endpoint_" . $brand . "_" . $family . '_phone';

$endpoint = new $class();

//have to because of versions less than php5.3
$endpoint->brand_name = $brand;
$endpoint->family_line = $family;

$endpoint->processor_info = "Web Provisioner 2.0";

//Mac Address
$endpoint->mac = '000B820D0057';

//Phone Model (Please reference family_data.xml in the family directory for a list of recognized models)
$endpoint->model = $model;

//Timezone
$endpoint->timezone = 'GMT-11:00';

//Server IP Address & Port
$endpoint->server[1]['ip'] = "10.10.10.10";
$endpoint->server[1]['port'] = 5060;

//Backup Server Address
$endpoint->server[2]['ip'] = "20.20.20.20";
$endpoint->server[2]['port'] = 7000;

//Provide alternate Configuration file instead of the one from the hard drive
//$endpoint->config_files_override['$mac.cfg'] = "{\$srvip}\n{\$admin_pass|0}\n{\$test.line.1}";

//Pretend we have three lines, we could just have one line or 20...whatever the phone supports
if(!isset($_REQUEST['secret'])) {
    $endpoint->lines[1] = array('ext' => '103', 'secret' => 'blah', 'displayname' => 'Joe Blow');
} else {
    $endpoint->lines[1] = array('ext' => $_REQUEST['ext'], 'secret' => $_REQUEST['secret'], 'displayname' => $_REQUEST['displayname']);
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
