<?php

/**
 * Base File
 *
 * @author Andrew Nagy
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */

abstract class endpoint_base {
    protected $config_manager;

    public function __construct(&$config_manager) {
        $this->config_manager = $config_manager;
    }

    public function prepareConfig() {
        $settings = $this->config_manager->get_settings();

        $settings['provisioning_url'] = $this->config_manager->get_current_provisioning_url();
        
        $this->config_manager->set_settings($settings);
    }
}

?>