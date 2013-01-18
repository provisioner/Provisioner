<?php 

/**
 * Represent the config file class that will merge / load / return the requested config file
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

require_once PROVISIONER_BASE . 'classes/configfile.php';

class ConfigGenerator_generic {
    public function get_config_manager($brand, $model, $arrConfig) {
        // Load the config manager
        $config_manager = new ConfigFile();

        $config_manager->set_device_infos($brand, $model);
        $config_manager->import_settings($arrConfig);

        return $config_manager;
    }
}

?>