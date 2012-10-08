<?php

// Define default directories
define("ROOT_PATH", dirname(__FILE__));
define("MODULES_DIR", ROOT_PATH."/endpoint");
define("PROVISIONER_BASE", ROOT_PATH.'/');

// Include our auto-loader
require_once(PROVISIONER_BASE.'autoload.php');
require_once(PROVISIONER_BASE.'utils.php');

// Correct timezone is required or PHP freaks out
date_default_timezone_set('America/Los_Angeles');


// Load Twig
$loader = new Twig_Loader_Filesystem("endpoint/yealink/t2x/");
$twig = new Twig_Environment($loader);
