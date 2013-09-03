<?php 

/**
 * AudioCodes 4xx rules File
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_audiocodes_4xx_phone extends endpoint_audiocodes_base {
    public function __construct(&$config_manager) {
        parent::__construct($config_manager);
    }

    function prepareConfig() {
        parent::prepareConfig();
        $settings = $this->config_manager->get_settings();

        $this->config_manager->set_settings($settings);
    }
}