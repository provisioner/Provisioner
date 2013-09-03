<?php 

/**
 * AudioCodes Base File
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_audiocodes_base extends endpoint_base {
    public function __construct(&$config_manager) {
        parent::__construct($config_manager);
    }

    function prepareConfig() {
        parent::prepareConfig();

        $this->_set_timezone();
    }

    // Generating the timezone string
    private function _set_timezone() {
        $constants = $this->config_manager->get_constants();
        $settings = $this->config_manager->get_settings();

        $tz = $constants['timezone_lookup'][$settings['timezone']];
        $strip = explode(":", $tz);
        $left = $strip[0];
        isset($strip[1]) ? $right = $strip[1] : $right = null;
        $tmp_num = substr($left, 1);

        if ($left < 0)
            $tmp_num < 10 ? $final_tz = '-0' . $tmp_num : $final_tz = '-' . $tmp_num;           
        else
            $tmp_num < 10 ? $final_tz = '+0' . $tmp_num : $final_tz = '+' . $tmp_num;

        $right != null ? $final_tz = $final_tz . ':30' : $final_tz = $final_tz . ':00';

        $settings['timezone'] = $final_tz;

        $this->config_manager->set_settings($settings);
    }
    
    public function setFilename($strFilename) {
        $settings = $this->config_manager->get_settings();

        $model = $this->config_manager->get_model();
        
        // AudioCodes likes lower case letters in its mac address
        $strFilename = preg_replace('/\$mac/', strtolower($this->config_manager->get_mac_address()), $strFilename);

        return $strFilename;
    }
}