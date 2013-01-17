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

class Settings {
    private $_objSettings = null;

    public function __construct() {
        try {
            $arr_file_content = json_decode(file_get_contents(CONFIG_FILE), true);
            $this->_objSettings = $this->_array_to_object($arr_file_content);
        } catch (Exception $e) {
            echo "Could not load the settings: " . $e->getMessage() . "\n";
        }
    }

    private function _array_to_object($array) {
    $obj = new stdClass;
        foreach($array as $k => $v) {
            if(is_array($v)) {
                $obj->{$k} = $this->_array_to_object($v); //RECURSION
            } else {
                $obj->{$k} = $v;
            }
        }
        return $obj;
    }

    public function getSettings() {
        return $this->_objSettings;
    }
}

?>