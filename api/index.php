<?php

define(DB_SERVER, 'http://localhost');

require_once 'wrapper/BigCouch.php';
require_once 'utils_validator.php';
require_once 'vendor/restler.php';
use Luracast\Restler\Restler;

$r = new Restler();
$r->addAPIClass('phones');
$r->addAPIClass('providers');
$r->addAPIClass('accounts');
//$r->addAuthenticationClass('auth');
$r->handle();

?>