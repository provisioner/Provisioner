<?php

// Define default directories
define("ROOT_PATH", dirname(__FILE__));

define("PROVISIONER_BASE", ROOT_PATH . '/');
define("LIB_BASE", PROVISIONER_BASE . 'lib/');

define("MODULES_DIR", ROOT_PATH . "/endpoint/");
define("WRAPPER_DIR", PROVISIONER_BASE . 'wrapper/');

// Include our auto-loader
require_once(PROVISIONER_BASE . 'autoload.php');
