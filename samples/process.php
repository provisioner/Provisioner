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
$line_options = array();
$loops_options = array();
$options = array();
$line_static = array();

foreach($_REQUEST as $key => $data) {
	if(preg_match("/lineloop\|(.*)\|(.*)/i",$key,$matches)) {
		$stuff = $matches;
		$line = $stuff[1];
		$var = $stuff[2];
		$req = $stuff[0];
	
		$line_options['line'][$line][$var] = $_REQUEST[$req];
		$line_options['line'][$line]['line'] = $line;
		unset($_REQUEST[$req]);
	}elseif(preg_match("/loop\|.*\|(.*)_([\d]*)_(.*)/i",$key,$matches)) {
		$stuff = $matches;		
		$loop = $stuff[1];
		$var = $stuff[3];
		$count = $stuff[2];
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
if(!empty($loops_options) && !empty($options)) {
	$final_ops2 = array_merge($loops_options,$options);
} elseif(empty($loops_options) && !empty($options)) {
	$final_ops2 = $options;
} elseif(!empty($loops_options) && empty($options)) {
	$final_ops2 = $loops_options;
}

$temp = $line_options['line'];
$line_options = array();
$line_options['line'] = array_values($temp);

$final_ops = array_merge($line_options,$final_ops2);

include('../autoload.php');

$brand = $_REQUEST['brand'];
$family = $_REQUEST['product'];
$model = $_REQUEST['model'];

$class = "endpoint_" . $brand . "_" . $family . '_phone';

$endpoint = new $class();

//All Endpoint Settings
$endpoint->settings = $final_ops;

//Mac Address
$endpoint->mac = $final_ops['mac'];

//have to because of versions less than php5.3
$endpoint->brand_name = $brand;
$endpoint->family_line = $family;
$endpoint->model = $model; //Phone Model (Please reference family_data.xml in the family directory for a list of recognized models)

//Processor Information, tagged to the top of most configuration files
$endpoint->processor_info = "Web Provisioner 2.0";

//Timezone
if (!class_exists("DateTimeZone")) { require('tz.php'); }
$endpoint->DateTimeZone = new DateTimeZone($_REQUEST['timezone']);;

//Provide alternate Configuration file instead of the one from the hard drive
//$endpoint->config_files_override['$mac.cfg'] = "{\$srvip}\n{\$admin_pass|0}\n{\$test.line.1}";

// Because every brand is an extension (eventually) of endpoint, you know this function will exist regardless of who it is
$returned_data = $endpoint->generate_config();

ksort($returned_data);

/*
$prov_data['type'] = 'WEB';
$prov_data['statics']['brand'] = $brand;
$prov_data['statics']['family'] = $family;
$prov_data['statics']['model'] = $model;
$prov_data['statics']['timezone'] = $_REQUEST['timezone'];
$prov_data['statics']['server'] = $_REQUEST['server'];
$prov_data['statics']['proxyserver'] = $_REQUEST['proxyserver'];
$prov_data['lines'] = $endpoint->lines;
$prov_data['options'] = $endpoint->options;

echo 'Ok Pushing this to the REST Server so you can use it on your phone! :-) <br/>';
require('Pest.php');
$pest = new Pest('http://www.provisioner.net/r/v1/accounts');
$data = $pest->put('/web/provision/'.$_REQUEST['mac'],json_encode($prov_data));
$data = json_decode($data,TRUE);
if(!$data['data']['success']) {
	if($data['data']['message'] == 'Account Already Exists. Use POST instead') {
		$data = $pest->post('/web/provision/'.$_REQUEST['mac'],json_encode($prov_data));
		$data = json_decode($data,TRUE);
		if(!$data['data']['success']) {
			echo "Error From Rest Server: ". $data['data']['message']."<br />";
		} else {
			echo 'Sucess!<br /><br />Point your phones provisioning address to: http://www.provisioner.net/g/v1/accounts/web/provision/';
		}
	} else {
		echo "Error From Rest Server: ". $data['data']['message']."<br />";
	}
} else {
	echo 'Sucess!<br /><br />Point your phones provisioning address to: http://www.provisioner.net/g/v1/accounts/web/provision/';
}
*/

echo '<br/>';

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
