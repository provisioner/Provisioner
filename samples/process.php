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

//print_r($_REQUEST);

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
	
		$line_options[$line]['options'][$var] = $_REQUEST[$req];
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
	$final_ops = array_merge($loops_options,$options);
} elseif(empty($loops_options) && !empty($options)) {
	$final_ops = $options;
} elseif(!empty($loops_options) && empty($options)) {
	$final_ops = $loops_options;
}

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

//Proxy Server IP Address & Port
$endpoint->proxy[1]['ip'] = $_REQUEST['proxyserver'];
$endpoint->proxy[1]['port'] = 5060;

//Proxy Server Backup IP Address & Port
//$endpoint->proxy[2]['ip'] = $_REQUEST['server'];
//$endpoint->proxy[2]['port'] = 5060;

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
