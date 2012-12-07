<?php

// Just to be sure
set_time_limit(5);

// This is useless for now 
define('DEBUG', true);

// We assume we have:
// DATABASE: SYSTEM_ACCOUNT -- All global preferences/settings
// DATABASE: PROVIDERS -- A document for each provider, by provider URL
// DATABASE: <ACCOUNT_ID> - An account_id (which is random) which belongs to a provider and has all of a customer's default account settings AND the individual phone MAC address settings

require_once '../bootstrap.php' ;
require_once 'model/utils.php';
require_once 'model/configfile.php';

$uri = strtolower($_SERVER['REQUEST_URI']);
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
$http_host = strtolower($_SERVER['HTTP_HOST']);

//$uri = "/002e3a6fe532d90943e6fcaf08e1a408/00085d258d4f.cfg";
//$ua = strtolower("Aastra55i MAC:00-08-5D-25-8D-4F V:3.2.2.1136-SIP");
//$http_host = 'p.kazoo.io';

$settings_array = array();
$account_id = null;
$mac_address = null;
$provider = null;
$needs_manual_provisioning = false;

$db_type = 'BigCouch';
$db = new $db_type('http://localhost');

// Creation of the settings manager
$settings_manager = new ConfigFile();

// Getting the provider from the host
$provider_domain = ProvisionerUtils::get_provider_domain($http_host);

// This is retrieve from a view, it is NOT the full doc
$provider_view = $db->get_provider($provider_domain);

// Getting the mac address in the URI OR in the User-Agent
$mac_address = ProvisionerUtils::get_mac_address($ua, $uri);

if (!$mac_address) {
    // http://cdn.memegenerator.net/instances/250x250/30687023.jpg
    echo '';
    exit();
}   

// Getting the account_id from the URI
$account_id = ProvisionerUtils::get_account_id($uri);
if (!$account_id) {
    $account_id = $provider_view['default_account_id'];

    // If we still don't get an account_id then we need a manual provisioning
    if (!$account_id)
        $needs_manual_provisioning = true;
    else
        $account_db = ProvisionerUtils::get_account_db($account_id);
} else
    $account_db = ProvisionerUtils::get_account_db($account_id);

// Manual provisioning
if ($needs_manual_provisioning) {
    $settings_manager->import_settings($db->load_settings('system_account', 'manual_provisioning'));

    // For now at least
    echo '';
    exit();

} else {
    // This is the full doc
    $phone_doc = $db->load_settings($account_db, $mac_address, false);

    // If we have the doc for this phone but there are no brand or no family
    if (!$phone_doc['brand'] or !$phone_doc['family']) {
        // /!\ with the current code, it will override the current infos
        // i.e. if there was no brand but the family was filled, it would be override anyway.
        if (!$settings_manager->detect_phone_info($mac_address, $ua)) {
            echo '';
            exit();
        } 
    } else 
        $settings_manager->set_device_infos($phone_doc['brand'], $phone_doc['family']);

    $factory_default_target = $settings_manager->get_brand() . '_' . $settings_manager->get_family();

    // This will import all the settings
    $settings_manager->import_settings($db->load_settings('factory_defaults', $factory_default_target));
    $settings_manager->import_settings($db->load_settings('system_account', 'global_settings'));

    // Why should we add that if it is empty?
    if (isset($provider_view['settings']))
        $settings_manager->import_settings($provider_view['settings']);

    $settings_manager->import_settings($db->load_settings($account_db, $account_id));

    // See above...
    if (isset($phone_doc['settings']))
        $settings_manager->import_settings($phone_doc['settings']);
    
    $settings_manager->set_config_file($uri);

    //$settings_manager->generate_config_file();
    echo $settings_manager->generate_config_file();
}

?>