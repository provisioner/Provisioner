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
    private $account_id = null;
    private $needs_manual_provisioning = false;
    private $mac_address = null;

    public function get_config_manager($uri, $ua, $http_host, $settings) {
        // Logger
        $log = KLogger::instance(LOGS_BASE, Klogger::DEBUG);
        $log->logInfo('- Entering 2600hz adapter -');

        // Load the datasource
        $db_type = 'wrapper_' . $settings->database->type;
        $db = new $db_type($settings->database->url, $settings->database->port);
        $log->logInfo("$db_type loaded");

        $log->logInfo('Looking for the mac address...');
        // Getting the mac address in the URI OR in the User-Agent
        $this->mac_address = helper_utils::get_mac_address($ua, $uri);
        if (!$this->mac_address) {
            $log->logFatal('Could not find a mac address - EXIT');
            // http://cdn.memegenerator.net/instances/250x250/30687023.jpg
            return false;
        }
        $log->logDebug("Mac address found: $this->mac_address");   

        // Load the config manager
        $config_manager = new system_configfile();

        $log->logInfo('Looking for the provider domain...');
        // Getting the provider from the host
        $provider_domain = helper_utils::get_provider_domain($http_host);
        $log->logDebug("Current provider domain: $provider_domain");

        $log->logInfo('Looking for the provider information...');
        // This is retrieve from a view, it is NOT the full doc
        $provider_view = $db->get_provider($provider_domain);
        if (!$provider_view) {
            $log->logFatal("Could not load the provider information - EXIT");
            return false;
        }
            
        $log->logInfo('Looking for the account_id...');
        // Getting the account_id from the URI
        $this->account_id = helper_utils::get_account_id($uri);
        // If not found, let's try with the mac_lookup
        if (!$this->account_id) {
            $log->logNotice("Did not find the account_id in the url. let's look in the mac_lookup...");
            $this->account_id = $db->get_account_id($this->mac_address);
        }

        if (!$this->account_id) {
            $log->logFatal('Still did not find the account_id... Going to use the default account_id');
            $this->account_id = $provider_view['default_account_id'];

            // If we still don't get an account_id then we need a manual provisioning
            if (!$this->account_id)
                $this->needs_manual_provisioning = true;
            else
                $account_db = helper_utils::get_account_db($this->account_id);
        } else {
            $log->logDebug("Current account_id: $this->account_id");
            $account_db = helper_utils::get_account_db($this->account_id);
            $log->logDebug("Current account database name (without the prefix): $account_db");
        }
            

        // Manual provisioning
        if ($this->needs_manual_provisioning) {
            $log->logWarn('Needs manual provisioning... Apparently');
            $config_manager->import_settings($db->load_settings('system_account', 'manual_provisioning'));

            // For now at least
            return $config_manager;
        } else {
            $log->logInfo('Will now gather all the information from the database / finish the config_manager building...');

            $log->logInfo('Looking for the device information...');
            // This is the full doc
            $phone_doc = $db->load_settings($account_db, $this->mac_address, false);

            // If we have the doc for this phone but there are no brand or no family
            if (!$phone_doc['brand'] or !$phone_doc['family'] or !$phone_doc['model']) {
                $log->logFatal('HuHo... something is missing here! Canceling request');

                return false;
                /*$log->logInfo('Will now try to detect phone information...');
                // /!\ with the current code, it will override the current infos
                // i.e. if there was no brand but the family was filled, it would be override anyway.
                if (!$config_manager->detect_phone_info($this->mac_address, $ua)) {
                    $log->logFatal("And that's a fail... - EXIT");
                    return false;
                } */
            } else {
                $log->logInfo('Setting brand/family/model info for config manager...');
                $log->logInfo('Current brand: ', $phone_doc['brand']);
                $log->logInfo('Current family: ', $phone_doc['family']);
                $log->logInfo('Current model: ', $phone_doc['model']);
                $config_manager->set_device_infos($phone_doc['brand'], $phone_doc['family'], $phone_doc['model']);
            }  

            $log->logInfo('Generating doc name for brand/family/model...');
            // Generate the doc names for the brand/family/model settings
            $brand_doc_name = $config_manager->get_brand();
            $family_doc_name = $brand_doc_name . "_" . $config_manager->get_family();
            $model_doc_mame = $family_doc_name . "_" . $config_manager->get_model();
            $log->logDebug("Brand doc name: $brand_doc_name");
            $log->logDebug("Family doc name: $family_doc_name");
            $log->logDebug("Model doc name: $model_doc_mame");

            // This will import all the settings
            $log->logInfo('Will now import default settings...');
            // Getting static data from different data sources
            if ($settings->static_data_source == "flat") {
                $log->logInfo('Doing it from flat files...');

                $brand_file = STATIC_DIR . $brand_doc_name . ".json";
                $family_file = STATIC_DIR . $family_doc_name . ".json";
                $model_file = STATIC_DIR . $model_doc_mame . ".json";

                $config_manager->import_settings(json_decode(file_get_contents($brand_file), true));
                $config_manager->import_settings(json_decode(file_get_contents($family_file), true));
                $config_manager->import_settings(json_decode(file_get_contents($model_file), true));
            } else {
                $log->logInfo('Doing it from the database...');
                //$config_manager->import_settings($db->load_settings('system_account', 'global_settings'));
                $config_manager->import_settings($db->load_settings('factory_defaults', $brand_doc_name));
                $config_manager->import_settings($db->load_settings('factory_defaults', $family_doc_name));
                $config_manager->import_settings($db->load_settings('factory_defaults', $model_doc_mame));
            }
            // =======

            // Why should we add that if it is empty?
            if (isset($provider_view['settings'])) {
                $log->logInfo('Importing provider settings...');
                $config_manager->import_settings($provider_view['settings']);
            }
                
            $log->logInfo('Importing account settings...');
            $config_manager->import_settings($db->load_settings($account_db, $this->account_id));

            // See above...
            if (isset($phone_doc['settings'])) {
                $log->logInfo('Importing device settings');
                $config_manager->import_settings($phone_doc['settings']);
            }

            $log->logInfo('Retrieving a first version of the merge setting object...');
            // Retrieve the settings (meaning a first merged object)
            $merged_settings = $config_manager->get_merged_config_objects();

            $log->logInfo('Loading Twig...');
            $loader = new Twig_Loader_Filesystem(PROVISIONER_BASE . 'adapter/2600hz/');
            $objTwig = new Twig_Environment($loader);
            $log->logInfo('Twig loaded!');

            $log->logInfo('Building lines settings...');
            // Building lines settings
            $line_settings = json_decode($objTwig->render('master.json', $merged_settings), true);
            if (!$line_settings) {
                $log->logWarn('Line settings NULL!');
                return false;
            }

            $log->logInfo('Remerging everything...');
            // Remerge everything
            $merged_settings = array_merge($merged_settings, $line_settings);
            
            $log->logInfo('Reassigning merge object into the config manager...');
            $config_manager->set_settings($merged_settings);

            // Set the targeted config file
            $log->logInfo('Will now select the file to generate...');
            $target = helper_utils::strip_uri($uri);
            $log->logDebug("Current target file: $target");

            $log->logInfo('Loading file list...');
            $config_file_list = helper_utils::get_file_list($config_manager->get_brand(), $config_manager->get_model());
            $log->logInfo('Current file list: ', $config_file_list);

            $log->logInfo('Loading regex list...');
            $regex_list = helper_utils::get_regex_list($config_manager->get_brand(), $config_manager->get_model());
            $log->logInfo('Current regex list: ', $regex_list);

            // We check first if the file is suppose to go through TWIG
            // for each configuration file possible for this model
            for ($i=0; $i < count($config_file_list); $i++) { 
                if (preg_match($regex_list[$i], $target)) {
                    $current_file = $config_file_list[$i];
                    $config_manager->set_config_file($current_file);

                    $log->logInfo("Found the correct file: $current_file");
                    $log->logDebug('SUCCESS! return the config manager...');
                    return $config_manager;
                }
            }

            $log->logInfo('Could not find a file to dynamically generate... Maybe it is a static file?');
            // Otherwise the file is suppose to be static, just redirecting.
            helper_utils::is_static_file($ua, $uri, $config_manager->get_model(), $config_manager->get_brand(), $settings);

            $log->logFatal("Nop, I just don't know what this file is... This is a fail! - EXIT");
            return false;
        }

        $log->logFatal('Something went wrong apparently...');
        return false;
    }
}

?>