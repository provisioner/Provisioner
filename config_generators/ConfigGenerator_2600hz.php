<?php 

/**
 * Represent the config file class that will merge / load / return the requested config file
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

require_once PROVISIONER_BASE . 'classes/utils.php';
require_once PROVISIONER_BASE . 'classes/configfile.php';

class ConfigGenerator_2600hz {
    private $account_id = null;
    private $needs_manual_provisioning = false;
    private $mac_address = null;

    public function get_config_manager($uri, $ua, $http_host, $settings) {
        // Load the datasource
        $db_type = $settings->database->type;
        $db = new $db_type($settings->database->url, $settings->database->port);

        // Load the config manager
        $config_manager = new ConfigFile();

        // Getting the provider from the host
        $provider_domain = ProvisionerUtils::get_provider_domain($http_host);

        // This is retrieve from a view, it is NOT the full doc
        $provider_view = $db->get_provider($provider_domain);

        // Getting the mac address in the URI OR in the User-Agent
        $this->mac_address = ProvisionerUtils::get_mac_address($ua, $uri);

        if (!$this->mac_address) {
            // http://cdn.memegenerator.net/instances/250x250/30687023.jpg
            echo '';
            exit();
        }

        // Getting the account_id from the URI
        $this->account_id = ProvisionerUtils::get_account_id($uri);
        if (!$this->account_id) {
            $this->account_id = $provider_view['default_account_id'];

            // If we still don't get an account_id then we need a manual provisioning
            if (!$this->account_id)
                $this->needs_manual_provisioning = true;
            else
                $account_db = ProvisionerUtils::get_account_db($this->account_id);
        } else
            $account_db = ProvisionerUtils::get_account_db($this->account_id);

        // Manual provisioning
        if ($this->needs_manual_provisioning) {
            $config_manager->import_settings($db->load_settings('system_account', 'manual_provisioning'));

            // For now at least
            echo '';
            exit();
        } else {
            // This is the full doc
            $phone_doc = $db->load_settings($account_db, $this->mac_address, false);

            // If we have the doc for this phone but there are no brand or no family
            if (!$phone_doc['brand'] or !$phone_doc['family'] or !$phone_doc['model']) {
                // /!\ with the current code, it will override the current infos
                // i.e. if there was no brand but the family was filled, it would be override anyway.
                if (!$config_manager->detect_phone_info($this->mac_address, $ua)) {
                    echo '';
                    exit();
                } 
            } else 
                $config_manager->set_device_infos($phone_doc['brand'], $phone_doc['model']);

            // If the requested file is not suppose to be dynamically generated
            // =================================
            $static_request = ProvisionerUtils::is_static_file($ua, $uri, $config_manager->get_model());

            if ($static_request) {
                $location = 'Location: ' . $static_request;
                header($location);
                exit();
            }
            // =================================

            // Generate the doc names for the brand/family/model settings
            $brand_doc_name = $config_manager->get_brand();
            $family_doc_name = $brand_doc_name . "_" . $config_manager->get_family();
            $model_doc_mame = $family_doc_name . "_" . $config_manager->get_model();

            $brand_file = STATIC_DIR . $brand_doc_name . ".json";
            $family_file = STATIC_DIR . $family_doc_name . ".json";
            $model_file = STATIC_DIR . $model_doc_mame . ".json";
            // This will import all the settings
            
            // Getting static data from different data sources
            if ($settings->static_data_source == "flat") {
                $config_manager->import_settings(json_decode(file_get_contents($brand_file), true));
                $config_manager->import_settings(json_decode(file_get_contents($family_file), true));
                $config_manager->import_settings(json_decode(file_get_contents($model_file), true));
            } else {
                //$config_manager->import_settings($db->load_settings('system_account', 'global_settings'));
                $config_manager->import_settings($db->load_settings('factory_defaults', $brand_doc_name));
                $config_manager->import_settings($db->load_settings('factory_defaults', $family_doc_name));
                $config_manager->import_settings($db->load_settings('factory_defaults', $model_doc_mame));
            }
            // =======

            // Why should we add that if it is empty?
            if (isset($provider_view['settings']))
                $config_manager->import_settings($provider_view['settings']);

            $config_manager->import_settings($db->load_settings($account_db, $this->account_id));

            // See above...
            if (isset($phone_doc['settings']))
                $config_manager->import_settings($phone_doc['settings']);

            return $config_manager;
        }

        return false;
    }
}

?>