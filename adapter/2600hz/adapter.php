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

class adapter_2600hz_adapter {
    private $account_id = null;
    private $needs_manual_provisioning = false;
    private $mac_address = null;

    public function get_config_manager($uri, $ua, $http_host, $settings) {
        // Load the datasource
        $db_type = 'wrapper_' . $settings->database->type;
        $db = new $db_type($settings->database->url, $settings->database->port);

        // Load the config manager
        $config_manager = new system_configfile();

        // Getting the provider from the host
        $provider_domain = helper_utils::get_provider_domain($http_host);

        // This is retrieve from a view, it is NOT the full doc
        $provider_view = $db->get_provider($provider_domain);

        // Getting the mac address in the URI OR in the User-Agent
        $this->mac_address = helper_utils::get_mac_address($ua, $uri);

        if (!$this->mac_address) {
            // http://cdn.memegenerator.net/instances/250x250/30687023.jpg
            echo '';
            exit();
        }

        // Getting the account_id from the URI
        $this->account_id = helper_utils::get_account_id($uri);
        if (!$this->account_id) {
            $this->account_id = $provider_view['default_account_id'];

            // If we still don't get an account_id then we need a manual provisioning
            if (!$this->account_id)
                $this->needs_manual_provisioning = true;
            else
                $account_db = helper_utils::get_account_db($this->account_id);
        } else
            $account_db = helper_utils::get_account_db($this->account_id);

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
                $config_manager->set_device_infos($phone_doc['brand'], $phone_doc['family'], $phone_doc['model']);

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

            // Set the targeted config file
            $target = helper_utils::strip_uri($uri);
            $config_file_list = helper_utils::get_file_list($config_manager->get_brand(), $config_manager->get_model());
            $regex_list = helper_utils::get_regex_list($config_manager->get_brand(), $config_manager->get_model());

            $merged_settings = $config_manager->get_merged_config_objects();

            $loader = new Twig_Loader_Filesystem(PROVISIONER_BASE . 'adapter/2600hz/');
            $objTwig = new Twig_Environment($loader);

            $line_settings = json_decode($objTwig->render('master.json', $merged_settings), true);
            $merged_settings = array_merge($merged_settings, $line_settings);
            
            $config_manager->set_settings($merged_settings);

            // We check first if the file is suppose to go through TWIG
            // for each configuration file possible for this model
            for ($i=0; $i < count($config_file_list); $i++) { 
                if (preg_match($regex_list[$i], $target)) {
                    $config_manager->set_config_file($config_file_list[$i]);

                    return $config_manager;
                }
            }

            // Otherwise
            helper_utils::is_static_file($ua, $uri, $config_manager->get_model(), $config_manager->get_brand(), $settings);

            die("Could not find the file to send back");
        }

        return false;
    }
}

?>