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
    public function __construct() {

    }

    public function prepareConfig(&$config_manager) {
        $settings = $config_manager->get_settings();

        $settings['provisioning_url'] = $config_manager->get_current_provisioning_url();
        
        $config_manager->set_settings($settings);
    }
}

?>