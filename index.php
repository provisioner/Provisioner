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

// YEALINK
//$uri = "/002e3a6fe532d90943e6fcaf08e1a408/001565000000.cfg";
//$ua = strtolower("Yealink SIP-T22P 3.2.2.1136 00:15:65:00:00:00");


// POLYCOM
//$uri = "/002e3a6fe532d90943e6fcaf08e1a408/000000000000.cfg";
//$uri = "/002e3a6fe532d90943e6fcaf08e1a408/0004f2e765da-phone.cfg";
//$uri = "/002e3a6fe532d90943e6fcaf08e1a408/common.cfg";
//$ua = strtolower("FileTransport PolycomSoundStationIP-SSIP_5000-UA/4.0.3.7562 Type/Application");

//$http_host = '::1';

$settings_array = array();
$account_id = null;
$mac_address = null;
$provider = null;
$needs_manual_provisioning = false;

// TODO: (urgent!)
// Polycom specific test.
// I think this is dirty and we should find another way.
// A brand specific code in a generic code... ugh.
$poly_template = ProvisionerUtils::is_generic_polycom_request($ua, $uri);

//echo MODULES_DIR . "polycom/" . $poly_template;

if ($poly_template) {
    echo file_get_contents(MODULES_DIR . "polycom/" . $poly_template);
    exit();
}

$db_type = 'BigCouch';
$db = new $db_type('http://10.10.9.57', '15984');

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
    if (!$phone_doc['brand'] or !$phone_doc['family'] or !$phone_doc['model']) {
        // /!\ with the current code, it will override the current infos
        // i.e. if there was no brand but the family was filled, it would be override anyway.
        if (!$settings_manager->detect_phone_info($mac_address, $ua)) {
            echo '';
            exit();
        } 
    } else 
        $settings_manager->set_device_infos($phone_doc['brand'], $phone_doc['family'], $phone_doc['model']);

    // Generate the doc names for the brand/family/model settings
    $brand_doc_name = $settings_manager->get_brand();
    $family_doc_name = $brand_doc_name . "_" . $settings_manager->get_family();
    $model_doc_mame = $family_doc_name . "_" . $settings_manager->get_model();

    // This will import all the settings
    
    // Need to be flat files
    // =======
    $settings_manager->import_settings($db->load_settings('system_account', 'global_settings'));
    $settings_manager->import_settings($db->load_settings('factory_defaults', $brand_doc_name));
    $settings_manager->import_settings($db->load_settings('factory_defaults', $family_doc_name));
    $settings_manager->import_settings($db->load_settings('factory_defaults', $model_doc_mame));
    // =======

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