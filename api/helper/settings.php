<?php 

/**
 * Represent the class that will manage the provisioner's settings
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

class helper_settings {
    private $_objSettings = null;
    private $_file;

    public static function get_instance($filepath = 'config.json') {
        $objSettings = new helper_settings(PROVISIONER_BASE . $filepath);
        return $objSettings->get_settings();
    }

    public function __construct($filepath = 'config.json') {
        $this->_file = $filepath;
        $this->_objSettings = json_decode(file_get_contents($this->_file));

        $error = $this->_json_error();
        if ($error) {
            echo $error;
            exit();
        }
    }

    public function get_settings() {
        return $this->_objSettings;
    }

    public function set_settings($settings) {
        $this->_objSettings = $settings;
    }

    public function write() {
        file_put_contents($this->_file, json_encode($this->_objSettings, JSON_PRETTY_PRINT));
    }

    private function _json_error() {
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

?>