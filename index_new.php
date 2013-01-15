<?php

/**
 * Index file for the config generator
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

// Just to be sure
set_time_limit(5);

require_once 'bootstrap.php' ;
require_once 'model/utils.php';
require_once 'model/configfile.php';

$uri = strtolower($_SERVER['REQUEST_URI']);
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
$http_host = strtolower($_SERVER['HTTP_HOST']);

$settings_array = array();
$account_id = null;
$mac_address = null;
$provider = null;
$needs_manual_provisioning = false;

$is_static = ProvisionerUtils::is_static_file_request($ua, $uri);