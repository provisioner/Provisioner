<?php

/**
 * This is the entry point for the APIs
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

require_once 'bootstrap.php' ;
require_once LIB_BASE . 'restler/restler.php';
require_once LIB_BASE . 'KLogger.php';

use Luracast\Restler\Restler;

// CORS
header('Access-Control-Allow-Headers:Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control, X-Auth-Token');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Origin:*');
header('Access-Control-Max-Age:86400');

$r = new Restler();
$r->setSupportedFormats('JsonFormat', 'UploadFormat');
$r->addAPIClass('phones');
$r->addAPIClass('providers');
$r->addAPIClass('accounts');
$r->addAPIClass('users');
$r->addAPIClass('files');
$r->addAuthenticationClass('AccessControl');
$r->handle();
