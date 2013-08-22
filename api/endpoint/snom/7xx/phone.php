<?php

/**
 * Snom 7xx Provisioning System
 *
 * @author Andrew Nagy & Jort
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_snom_7xx_phone extends endpoint_snom_base {
    public function __construct(&$config_manager) {
        parent::__construct($config_manager);
    }

    function prepareConfig() {
        parent::prepareConfig();

        $this->set_timezone();
        $this->set_codecs();
    }

    function set_timezone() {
        $constants = $this->config_manager->get_constants();
        $settings = $this->config_manager->get_settings();

        $settings['timezone'] = 'USA' . $constants['timezone_lookup'][$settings['timezone']];

        $this->config_manager->set_settings($settings);
    }

    function set_codecs() {
        $settings = $this->config_manager->get_settings();
        $final_codecs_list = "";

        $codecs_list = $settings['media']['audio']['codecs'];
        for ($i=0; $i < count($codecs_list); $i++) { 
            if ($i == count($codecs_list) - 1)
                $final_codecs_list = $final_codecs_list . strtolower($codecs_list[$i]);
            else
                $final_codecs_list = $final_codecs_list . strtolower($codecs_list[$i]) . ',';
        }

        $settings['codecs'] = $final_codecs_list;

        $this->config_manager->set_settings($settings);
    }
}

?>
