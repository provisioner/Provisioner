<?php

// We assume we have:
// DATABASE: SYSTEM_ACCOUNT -- All global preferences/settings
// DATABASE: PROVIDERS -- A document for each provider, by provider URL
// DATABASE: <ACCOUNT_ID> - An account_id (which is random) which belongs to a provider and has all of a customer's default account settings AND the individual phone MAC address settings

require_once '../bootstrap.php' ;
require_once 'model/utils.php';
require_once 'model/configfile.php';

/*
$uri = $_SERVER['REQUEST_URI'];
$ua = $_SERVER['HTTP_USER_AGENT'];
$host = $_SERVER['HTTP_HOST'];
*/

$uri = "/accounts/002e3a6fe532d90943e6fcaf08e1a408/001565000000.cfg";
$ua = "yealink SIP-T22P 7.40.1.2 00:15:65:00:00:00";

$settings_array = array();
$account_id = null;
$mac_address = null;
$needs_manual_provisioning = false;

$db_type = "BigCouch";
$db = new $db_type('http://localhost');

//echo $_SERVER['HTTP_HOST']; // This should give you the provider.
// Get the mac address
// Find the account_id from uri (if no account_id, lookup a default in the provider's settings, if any)
// Go to the the mac_address doc inside of the account_id database
// -> get the phone settings (keep it for now)
// -> retrieve the family and the brand
// Go to factory_default/brand_family
// -> get the settings
// Go to the provider_id Doc
// -> get the settings
// Go to the account_id Doc
// -> get the settings
// put the phone settings now
// merge everything
// --> TWIG

$settings_manager = new ConfigFile();

// Let's get the mac address
$mac_address = ProvisionerUtils::get_mac_address($ua, $uri);
if (!$mac_address)
    exit();

// Let's check if there is an account_id
if (preg_match("#[0-9a-z]{32}#", $uri, $match_result)) {
    $account_id = "account/" . $match_result[0];
} else {
    // Look in a database named "authorized_ips" for the IP this request is coming from. If it's there, we'll get the account_id
    $account_id = "127.0.0.1";
    //$ip = $_SERVER['REMOTE_ADDR'];
    $account_id = $db->get_account_from_ip($ip);

    // If no IP and no account_id, send them the config settings for the "manual, remote provisioning"
    if (!$account_id)
        $needs_manual_provisioning = true;
}

// Finally gathering the settings.
$settings_manager->import_settings($db->load_settings("system_account", "globals"));

if ($needs_manual_provisioning)
    $settings_manager->import_settings($db->load_settings("system_account", "manual_provisioning"));
else {
    //$json_2 = $db->load_settings("providers", $provider_id);
    var_dump($account_id);
    $settings_manager->import_settings($db->load_settings($account_id, "account_settings"));
    $settings_manager->import_settings($db->load_settings($account_id, $mac_address));
}

$final_settings = $settings_manager->merge_config_objects();

if (!$final_settings['family'] or !$final_settings['brand']) {
    // If family or brand is unknown, try to auto-detect
    if (!$settings_manager->detect_phone_info($mac_address, $ua))
        exit();
} else 
    $settings_manager->set_device_infos($final_settings['brand'], $final_settings['family']);

$settings_manager->set_config_file($uri);
$settings_manager->generate_config_file($final_settings);

?>