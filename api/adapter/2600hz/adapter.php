<?php 

/**
 * Adapters take in desired settings for a phone from some other system and convert them into a standard form which we can use to generate config files.
 * In other words, some systems will send a SIP Proxy and some may send a SIP Registrar setting. This adapter will convert whatever gets sent from that
 * system into the format we need for provisioner.net, such as $settings['proxy'];
 *
 * This particular adapter is smart. It will take in settings from the Kazoo platform and break them into account, user and device settings and process them
 * accordingly, respecting the standard Kazoo GUI representation of codecs, proxies and other settings. 
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

require_once LIB_BASE . 'KLogger.php';

class adapter_2600hz_adapter {
    private $_needs_manual_provisioning;
    private $_settings;

    function __construct() {
        // First load the settings
        $this->_settings = helper_settings::get_instance();
    }

    public function get_config_manager($provider_id, $mac_address, $brand, $model) {
        $needs_manual_provisioning = false;

        // Logger
        $log = KLogger::instance(LOGS_BASE, Klogger::DEBUG);
        $log->logInfo('- Entering 2600hz adapter -');

        // Load the datasource
        $db = new wrapper_bigcouch();
        $log->logInfo("Bigcouch loaded");

        $log->logInfo("Current mac address: $mac_address");
        $log->logInfo("Current brand: $brand");
        $log->logInfo("Current model: $model");

        if (!$mac_address) {
            $log->logFatal('the mac address is null - EXIT');
            // http://cdn.memegenerator.net/instances/250x250/30687023.jpg
            return false;
        }
        $log->logDebug("Current mac address: $mac_address");   

        // Load the config manager
        $config_manager = new system_configfile();
        $config_manager->set_mac_address($mac_address);
        $config_manager->set_request_type('http');

        $log->logInfo('Looking for the provider information...');
        // This is retrieve from a view, it is NOT the full doc
        $provider_doc = $db->get('providers', $provider_id);

        if (!$provider_doc) {
            $log->logFatal("Could not load the provider information - EXIT");
            return false;
        }

        // This will be used to set the provisioner_url
        $config_manager->set_domain($provider_doc['domain']);
            
        $log->logInfo('Looking for the account_id in the mac_lookup...');
        $account_id = $db->get_account_id($mac_address);

        if (!$account_id) {
            $log->logFatal('Could not retrieve the account_id... Going to use the default account_id');
            $account_id = $provider_doc['default_account_id'];

            // If we still don't get an account_id then we need a manual provisioning
            if (!$account_id)
                $needs_manual_provisioning = true;
            else
                $account_db = helper_utils::get_account_db($account_id);
        } else {
            $log->logDebug("Current account_id: $account_id");
            $account_db = helper_utils::get_account_db($account_id);
            $log->logDebug("Current account database name (without the prefix): $account_db");
        }

        // Manual provisioning
        if ($needs_manual_provisioning) {
            $log->logWarn('Needs manual provisioning... Apparently');
            $config_manager->import_settings($db->load_settings('system_account', 'manual_provisioning'));

            // For now at least
            return $config_manager;
        } else {
            $log->logInfo('Will now gather all the information from the database / finish the config_manager building...');

            $log->logInfo('Looking for the device settings...');
            // This is just the settings
            $phone_settings = $db->load_settings($account_db, $mac_address);

            // Setting the device infos
            $config_manager->set_device_infos($brand, $model);

            $log->logInfo('Generating doc name for brand/family/model...');
            // Generate the doc names for the brand/family/model settings
            $brand_doc_name = $config_manager->get_brand();
            $family_doc_name = $brand_doc_name . "_" . $config_manager->get_family();
            $model_doc_mame = $family_doc_name . "_" . $config_manager->get_model();
            $log->logDebug("Brand doc name: $brand_doc_name");
            $log->logDebug("Family doc name: $family_doc_name");
            $log->logDebug("Model doc name: $model_doc_mame");

            $log->logInfo('Doing it from the database...');
            //$config_manager->import_settings($db->load_settings('system_account', 'global_settings'));
            $config_manager->import_settings($db->load_settings('factory_defaults', $brand_doc_name));
            $config_manager->import_settings($db->load_settings('factory_defaults', $family_doc_name));
            $config_manager->import_settings($db->load_settings('factory_defaults', $model_doc_mame));

            // Why should we add that if it is empty?
            if (isset($provider_doc['settings'])) {
                $log->logInfo('Importing provider settings...');
                $config_manager->import_settings($provider_doc['settings']);
            }
                
            $log->logInfo('Importing account settings...');
            $config_manager->import_settings($db->load_settings($account_db, $account_id));

            // See above...
            if (!empty($phone_settings)) {
                $log->logInfo('Importing device settings');
                $config_manager->import_settings($phone_settings);
            }

            $log->logInfo('Retrieving a first version of the merge setting object...');
            // Retrieve the settings (meaning a first merged object)
            $merged_settings = $config_manager->get_merged_config_objects();

            $log->logInfo('Loading Twig...');
            $loader = new Twig_Loader_Filesystem(PROVISIONER_BASE . 'adapter/2600hz/');
            $objTwig = new Twig_Environment($loader);
            $log->logInfo('Twig loaded!');

            $log->logInfo('Building lines settings...');

            // Yeah, let's choose the right template
            if (file_exists(PROVISIONER_BASE . 'adapter/2600hz/' . $brand_doc_name))
                $master_template = $model_template;
            elseif(file_exists(PROVISIONER_BASE . 'adapter/2600hz/' . $family_doc_name))
                $master_template = $family_template;
            elseif(file_exists(PROVISIONER_BASE . 'adapter/2600hz/' . $model_doc_mame))
                $master_template = $model_doc_mame;
            else
                $master_template = 'master.json';

            // Building lines settings
            $line_settings = json_decode($objTwig->render($master_template, $merged_settings), true);

            if (!$line_settings) {
                $log->logWarn('Line settings NULL!');
                return false;
            }

            $log->logInfo('Remerging everything...');
            // Remerge everything
            $merged_settings = array_merge($merged_settings, $line_settings);
            
            $log->logInfo('Reassigning merge object into the config manager at key 0...');
            $config_manager->set_settings($merged_settings, false);

            $log->logInfo('Returning config manager...');
            return $config_manager;
        }

        $log->logFatal('Something went wrong apparently...');
        return false;
    }
}

?>