<?php

/*
    This is the entry point for the APIs
*/

define('DB_SERVER', 'http://10.10.9.57');
define('DB_PORT', '15984');

// CORS
header('Access-Control-Allow-Headers:Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control, X-Auth-Token');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Origin:*');
header('Access-Control-Max-Age:86400');

require_once 'wrapper/bigcouch.php';
require_once 'utils_validator.php';
require_once 'lib/restler/restler.php';
use Luracast\Restler\Restler;

$r = new Restler();
$r->addAPIClass('phones');
$r->addAPIClass('providers');
$r->addAPIClass('accounts');
$r->addAuthenticationClass('AccessControl');
$r->handle();

?>