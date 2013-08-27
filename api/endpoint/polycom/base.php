<?php

/**
 * Polycom Base File
 *
 * @author Andrew Nagy
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
abstract class endpoint_polycom_base extends endpoint_base {
    public function __construct(&$config_manager) {
        parent::__construct($config_manager);
    }

    function prepareConfig() {
        parent::prepareConfig();

        $app_settings = helper_settings::get_instance();

        $this->_set_timezone();

        $settings = $this->config_manager->get_settings();
        if (isset($settings['hotline']) && !empty($settings['hotline'])) {
            $number = $settings['hotline'];

            unset($settings['hotline']);

            $settings['hotline']['number'] = $number;
            $settings['hotline']['enable'] = true;
        }

        if ($this->config_manager->get_request_type() == 'http')
            $settings['directory_url'] = $settings['provisioning_url'] . 'directory/' . $this->config_manager->get_mac_address() . '/000000000000-directory.xml';

        $this->config_manager->set_settings($settings);
    }

    private function _set_timezone() {
        $constants = $this->config_manager->get_constants();
        $settings = $this->config_manager->get_settings();

        $tz = $constants['timezone_lookup'][$settings['timezone']];
        $strip_tz = explode(":", $tz);

        $main_tz = $strip_tz[0] * 60 * 60;
        $sub_tz = 0;
        if (isset($strip_tz[1]))
            $sub_tz = 30 * 60;

        $settings['timezone'] = $main_tz + $sub_tz;

        $this->config_manager->set_settings($settings);
    }

    public function setFilename($strFilename) {
        $settings = $this->config_manager->get_settings();

        if ($strFilename != '000000000000-directory.xml') {
            //Polycoms seems to likes lower case letters in its mac address too
            $strFilename = preg_replace('/\$mac/', strtolower($this->config_manager->get_mac_address()), $strFilename);
        } else {
            $folder = CONFIG_FILES_BASE . '/directory/' . $this->config_manager->get_mac_address();
            if (!file_exists($folder))
                mkdir($folder);
            
            $strFilename = 'directory/' . $this->config_manager->get_mac_address() . '/000000000000-directory.xml';
        }

        return $strFilename;
    }
}

?>