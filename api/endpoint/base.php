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
        $app_settings = helper_settings::get_instance();

        if ($this->config_manager->get_request_type() == 'http') {
            if (!empty($app_settings->custom_http_port))
                $settings['provisioning_url'] = 'http://' . $this->config_manager->get_domain() . ':' .  $app_settings->custom_http_port . $app_settings->paths->root;
            else
                $settings['provisioning_url'] = 'http://' . $this->config_manager->get_domain() . $app_settings->paths->root;
        }
        
        $this->config_manager->set_settings($settings);
    }
}