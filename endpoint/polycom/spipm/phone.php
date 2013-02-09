<?php

/**
 * Phone Base File
 *
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_polycom_spipm_phone extends endpoint_polycom_base {
    public function __construct() {
        parent::__construct();
    }

    function prepareConfig($settings, $config_manager) {
        parent::prepareConfig($settings, $config_manager);
        $this->encode_config($settings, $config_manager);
        
        return $settings;
    }
}
