<?php 

/**
 * Represent the class that will manage the provisioner's settings
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

define("CONFIG_FILE", PROVISIONER_BASE . 'config.json');

class helper_settings {
    private $_objSettings = null;

    public function __construct() {
        try {
            $arr_file_content = json_decode(file_get_contents(CONFIG_FILE), true);
            $this->_objSettings = helper_utils::array_to_object($arr_file_content);
        } catch (Exception $e) {
            echo "Could not load the settings: " . $e->getMessage() . "\n";
        }
    }

    public function getSettings() {
        return $this->_objSettings;
    }
}

?>