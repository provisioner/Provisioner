<?php 

/**
 * Yealink Base File
 *
 * @author Andrew Nagy
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_yealink_base extends endpoint_base {
    function prepareConfig(&$settings, $config_manager) {
        parent::prepareConfig($settings, $config_manager);

        $constants = $config_manager->get_constants();
        $settings['timezone'] = $constants['timezone_lookup'][$settings['timezone']];
    }
}

?>