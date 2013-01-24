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


class ConfigGenerator_generic {
    public function get_config_manager($brand, $model, $arrConfig) {
        // Load the config manager
        $config_manager = new ConfigFile();

        $config_manager->set_device_infos($brand, $model);

        // Import the default settings
        /*
        $config_manager->import_settings(json_decode(file_get_contents($brand_file), true));
        $config_manager->import_settings(json_decode(file_get_contents($family_file), true));
        $config_manager->import_settings(json_decode(file_get_contents($model_file), true));
        */
        // Import the given settings 
        $config_manager->import_settings($arrConfig);

        return $config_manager;
    }
    
    private function json_errors() {
        switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    return false;
                break;
                case JSON_ERROR_DEPTH:
                    return ' - Maximum stack depth exceeded';
                break;
                case JSON_ERROR_STATE_MISMATCH:
                    return ' - Underflow or the modes mismatch';
                break;
                case JSON_ERROR_CTRL_CHAR:
                    return ' - Unexpected control character found';
                break;
                case JSON_ERROR_SYNTAX:
                    return ' - Syntax error, malformed JSON';
                break;
                case JSON_ERROR_UTF8:
                    return ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
                default:
                    return ' - Unknown error';
                break;
            }
    }
}