<?php

// Define default directories
define("ROOT_PATH", dirname(__FILE__));
define("MODULES_DIR", ROOT_PATH."/endpoint");
define("PROVISIONER_BASE", ROOT_PATH.'/');

// Include our auto-loader
require_once(PROVISIONER_BASE.'autoload.php');
