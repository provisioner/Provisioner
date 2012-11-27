<?php

// We assume we have:
// DATABASE: SYSTEM_ACCOUNT -- All global preferences/settings
// DATABASE: PROVIDERS -- A document for each provider, by provider URL
// DATABASE: <ACCOUNT_ID> - An account_id (which is random) which belongs to a provider and has all of a customer's default account settings AND the individual phone MAC address settings

require_once '../bootstrap.php' ;
require_once 'model/utils.php';
require_once 'model/configfile.php';

$uri = $_SERVER['REQUEST_URI'];
$ua = $_SERVER['HTTP_USER_AGENT'];
$settings_array = array();
$account_id = null;
$mac_address = null;

$db_type = "BigCouch";
$db = new $db_type('http://localhost');

$settings_manager = new ConfigFile();

// Let's get the mac address
$mac_address = ProvisionerUtils::get_mac_address($ua, $uri);
if (!$mac_address)
    exit();

// Let's check if there is an account_id
if (preg_match("#/[0-9a-z]{32}/#", $uri, $match_result))
    $account_id = $match_result[0];
else {
    // Look in a database named "authorized_ips" for the IP this request is coming from. If it's there, we'll get the account_id
    $ip = $_SERVER['REMOTE_ADDR'];
    $account_id = $db->get_account_from_ip($ip);

    // If no IP and no account_id, send them the config settings for the "manual, remote provisioning"
    if (!$account_id)
        $needs_manual_provisioning = TRUE;
}

// Finally gathering the settings.
$settings_manager->import_settings($db->loadSettings("system_account", "globals"));

if ($needs_manual_provisioning)
    $settings_manager->import_settings($db->loadSettings("system_account", "manual_provisioning"));
else {
    //$json_2 = $db->loadSettings("providers", $provider_id);
    $settings_manager->import_settings($db->loadSettings($account_id, "account_settings"));
    $settings_manager->import_settings($db->loadSEttings($account_id, $mac_address));
}

$final_settings = $settings_manager->merge_config_objects();

if (!$final_settings['family'] or !$final_settings['brand']) {
    // If model or brand is unknown, try to auto-detect
    if (!$settings_manager->detect_phone_info($mac_address, $ua))
        exit();
} else {
    $settings_manager->set_brand($final_settings['brand']);
    $settings_manager->set_family($final_settings['family']);
}

$settings_manager->set_config_file($uri);
$settings_manager->generate_config_file();

?>