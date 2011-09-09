<?php
//Permit/Deny

if($_SERVER["REMOTE_ADDR"] != "70.36.146.245") {
	//die('Not Authorized');
}

$regex = '/\/(.*)\/accounts\/(.*)\/(.*)\/(.*)/';
preg_match($regex, $_SERVER['REQUEST_URI'], $matches);

$version = $matches[1];
$account_id = $matches[2];
$phone = $matches[3];
$file = $matches[4];

$path = str_replace('servers/rest/generate','',dirname(__FILE__));
define('PROVISIONER_BASE', $path);
require(PROVISIONER_BASE.'autoload.php');
require('pest.php');

$pest = new Pest('http://www.provisioner.net/r/v1/accounts');

if(preg_match('/[0-9A-Fa-f]{12}/i', $file, $matches) && !(preg_match('/[0]{10}[0-9]{2}/i',$file))) {
	$mac = $matches[0];
	//Ironically we call our own rest here	
	$data = $pest->get('/web/provision/'.strtoupper($mac));
	$data = json_decode($data,TRUE);
	
	if(isset($data['data']['success']) && !$data['data']['success']) {
		$data = $pest->get('/web/provision/'.strtolower($mac));
		$data = json_decode($data,TRUE);
		if(isset($data['data']['success']) && !$data['data']['success']) {
			die($data['data']['message']);
		}
	}
			
	$brand = $data['data']['statics']['brand'];
	$family = $data['data']['statics']['family'];
	$model = $data['data']['statics']['model'];

	$class = "endpoint_" . $brand . "_" . $family . '_phone';

	if(!class_exists($class)) { die('Unable to load class: '. $class); }
	$endpoint = new $class();
		
	$endpoint->server_type = 'dynamic';		//Can be file or dynamic
    $endpoint->provisioning_type = 'http';

	//have to because of versions less than php5.3
	$endpoint->brand_name = $brand;
	$endpoint->family_line = $family;

	$endpoint->processor_info = "Web Provisioner 2.0";

	//Mac Address
	$endpoint->mac = $mac;

	//Phone Model (Please reference family_data.xml in the family directory for a list of recognized models)
	$endpoint->model = $model;

	//Timezone
	if (!class_exists("DateTimeZone")) { require('tz.php'); }
	$endpoint->DateTimeZone = new DateTimeZone($data['data']['statics']['timezone']);;

	//Server IP Address & Port
	$endpoint->server[1]['ip'] = $data['data']['statics']['server'];
	$endpoint->server[1]['port'] = 5060;
	
	$endpoint->proxy[1]['ip'] = $data['data']['statics']['proxyserver'];
	$endpoint->proxy[1]['port'] = 5060;
	
	
	$endpoint->provisioning_path = 'http://www.provisioner.net'.$_SERVER['REQUEST_URI'];

	//Provide alternate Configuration file instead of the one from the hard drive
	//$endpoint->config_files_override['$mac.cfg'] = "{\$srvip}\n{\$admin_pass|0}\n{\$test.line.1}";

	/*
	foreach($data['data']['lines'] as $key => $data) {
		$endpoint->lines[$key] = $data;
	}
	*/
	
	$endpoint->lines = $data['data']['lines'];

	/*
	foreach($line_options as $key => $data) {
		$endpoint->lines[$key]['options'] = $data['options'];
	}

	//Set Variables according to the template_data files included. We can include different template.xml files within family_data.xml also one can create
	//template_data_custom.xml which will get included or template_data_<model_name>_custom.xml which will also get included
	//line 'global' will set variables that aren't line dependant
	*/
	$endpoint->options =  $data['data']['options'];
	
	/*
	//Setting a line variable here...these aren't defined in the template_data.xml file yet. however they will still be parsed 
	//and if they have defaults assigned in a future template_data.xml or in the config file using pipes (|) those will be used, pipes take precedence

	*/

	// Because every brand is an extension (eventually) of endpoint, you know this function will exist regardless of who it is
	$returned_data = $endpoint->generate_config();
	ksort($returned_data);

	echo $returned_data[$file];

} elseif(preg_match('/[0]{10}[0-9]{2}/i',$file)) {
	echo '#blank';
} elseif($file == 'aastra.cfg') {
	echo '#blank';
}