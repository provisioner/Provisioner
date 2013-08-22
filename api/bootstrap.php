<?php

// Define default directories
define("ROOT_PATH", dirname(__FILE__));
define("CONFIG_FILES_BASE", dirname(ROOT_PATH));
define("PROVISIONER_BASE", ROOT_PATH . '/');

define("LIB_BASE", PROVISIONER_BASE . 'lib/');
define("LOGS_BASE", PROVISIONER_BASE . 'logs/');
define("MODULES_DIR", PROVISIONER_BASE . "endpoint/");

// Include our auto-loader
require_once(PROVISIONER_BASE . 'autoload.php');
