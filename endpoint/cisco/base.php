<?php

/**
 * Cisco Base File
 *
 * @author Andrew Nagy
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_base extends endpoint_base {
    function prepareConfig(&$config_manager) {
        parent::prepareConfig($config_manager);

        $this->_set_timezone($config_manager);
        $this->_set_codecs($config_manager);
    }

    // Generating the timezone string
    private function _set_timezone(&$config_manager) {
        $constants = $config_manager->get_constants();
        $settings = $config_manager->get_settings();

        $tz = $constants['timezone_lookup'][$settings['timezone']];
        $strip = explode(":", $tz);
        $left = $strip[0];
        $right = $strip[1];
        $tmp_num = substr($left, 1);

        if ($left < 0)
            $tmp_num < 10 ? $final_tz = 'GMT-0' . $tmp_num : $final_tz = 'GMT-' . $tmp_num;           
        else
            $tmp_num < 10 ? $final_tz = 'GMT+0' . $tmp_num : $final_tz = 'GMT+' . $tmp_num;

        !empty($right) ? $final_tz = $final_tz . ':30' : $final_tz = $final_tz . ':00';

        $settings['timezone'] = $final_tz;

        $config_manager->set_settings($settings);
    }

    // Generating the codec list
    private function _set_codecs(&$config_manager) {
        $settings = $config_manager->get_settings();
        $codecs = $settings['media']['audio']['codecs'];

        for ($i = 0; $i < 3; $i++){
            if (isset($codecs[$i]))
                $settings['codecs'][$i] = $this->_parse_codec_name($codecs[$i]);
        }

        $config_manager->set_settings($settings);
    }

    // Get the Cisco translation from our base value
    private function _parse_codec_name($codec) {
        switch ($codec) {
            case 'PCMU':
                return 'G711u';
            case 'PCMA':
                return 'G711a';
            case 'G722_16':
                return 'G722';
            case 'G722_32':
                return 'G722';

            default:
                return null;
        }
    }
}

?>