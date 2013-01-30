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
    function prepareConfig(&$settings, $config_manager) {
        parent::prepareConfig($settings, $config_manager);

        // for $model.cfg
        $settings['provisioning_path'] = $config_manager->get_current_provisioning_address();

        return $settings;
    }
}

?>