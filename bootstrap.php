<?php

// Define default directories
define("ROOT_PATH", dirname(__FILE__));
define("PROVISIONER_BASE", ROOT_PATH . '/');

define("LIB_BASE", PROVISIONER_BASE . 'lib/');
define("MODULES_DIR", PROVISIONER_BASE . "endpoint/");
define("WRAPPER_DIR", PROVISIONER_BASE . 'wrapper/');
define("CONFIG_MANAGER_DIR", PROVISIONER_BASE . "config_generators/");

// Include our auto-loader
require_once(PROVISIONER_BASE . 'autoload.php');
