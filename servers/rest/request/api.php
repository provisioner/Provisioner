<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
include 'Core.php';

require('RestServer.php');
$rest = new RestServer('production');

$rest->cacheDir = '/var/www/rest/cache';

// File-based REST APIs
const BEHAVIOR = 'FILE';
const DIRECTORY = '/var/www/rest/provision/';

// REST API proxy pass-thru -- using this will allow for generation of configs and validation, but all config changes
// are proxied to a third party service
// const BEHAVIOR = 'REST';
// const URL = 'http://apps001-sdflkjsflksdjfkls-ord.2600hz.com:8000/v1/accounts/bb361ffd5a373ac463ac9/';

if (BEHAVIOR == 'REST') {
    $handler = new Pest(URL);
} else {
    $handler = new FileRest(DIRECTORY,$rest);
}

// A simple router, to invoke the proper class
// Parse URL here
$uri = $rest->getPath();
$verb = $rest->getMethod();

header('Access-Control-Allow-Headers:Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control, X-Auth-Token');
//header('Access-Control-Allow-Methods:'.$verb);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Origin:*');
header('Access-Control-Max-Age:86400');

// Adjust per your expectations from the software you're using
$regex = preg_match('/\/(.*)\/accounts\/(.*)\/(.*)\/(.*)/', $uri) ? '/\/(.*)\/accounts\/(.*)\/(.*)\/(.*)/' : '/\/(.*)\/accounts\/(.*)\/(.*)/';

$handler->regex = $regex;
preg_match($regex, $uri, $matches);

$version = $matches[1];
$account_id = $matches[2];
$method = $matches[3];
$id = $matches[4];

$data = $rest->getData();
$handler->json_error();

//$method = 'provision';
if(isset($id)) {
	switch ($method) {
	    case 'provision':
	        $response = $handler->$verb($uri, json_encode($data));
			if($response && $verb == 'GET') {
				$response = json_decode($response);
			}
			break;
	    case 'provision_template':
	        $response = $handler->$verb($uri, json_encode($data));
			if($response && $verb == 'GET') {
				$response = json_decode($response);
			}
			break;
	    default:
	            $response = array(
	                'data' => array(
	                    'success' => 'false',
	                    'message' => 'Unknown API command ' . $method
	                )
	            );

	        break;
	}
} else {
	switch ($method) {
	    case 'provision_template':
			$list = array();
			foreach (glob("/var/www/rest/provision/".$account_id."/provision_template/*",GLOB_ONLYDIR) as $filename) {
				$key = basename($filename);
				if(file_exists("/var/www/rest/provision/".$account_id."/provision_template/".$key.'/data')) {
					$data = file_get_contents("/var/www/rest/provision/".$account_id."/provision_template/".$key.'/data');
					$data = json_decode($data,TRUE);
					if(is_array($data) && array_key_exists('product',$data) && array_key_exists('brand',$data) && array_key_exists('model',$data)) {
						$list[$key]['product'] = $data['product'];
						$list[$key]['brand'] = $data['brand'];
						$list[$key]['model'] = $data['model'];
						$list[$key]['description'] = isset($data['description']) ? $data['description'] : '';
					}
				}				
			}			
			if(!empty($list) && $verb == 'GET') {
				$response = $list;
			} else {
				$response = array(
	                'data' => array(
	                    'success' => 'false',
	                    'message' => 'Not allowed: ' . $verb
	                )
	            );
			}
			break;
	    default:
	            $response = array(
	                'data' => array(
	                    'success' => 'false',
	                    'message' => 'Unknown API command ' . $method
	                )
	            );

	        break;
	}
}

$handler->send($response);
