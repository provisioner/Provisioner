<?php

// Define default directories
define("ROOT_PATH", dirname(__FILE__));
define("PROVISIONER_BASE", ROOT_PATH . '/');

define("LIB_BASE", PROVISIONER_BASE . 'lib/');
define("LOGS_BASE", PROVISIONER_BASE . 'logs/');
define("MODULES_DIR", PROVISIONER_BASE . "endpoint/");
define("STATIC_DIR", PROVISIONER_BASE . "static_data/");

// Include our auto-loader
require_once(PROVISIONER_BASE . 'autoload.php');
