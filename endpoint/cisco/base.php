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

        $settings['provisioning_path'] = $config_manager->get_current_provisioning_url();
        $this->_set_timezone($settings, $config_manager);
    }

    private function _set_timezone(&$settings, $config_manager) {
        $constants = $config_manager->get_constants();
        $tz = $constants['timezone_lookup'][$settings['timezone']];
        $strip_tz = explode(":", $tz)[0];

        if ($strip_tz < 0) {
            $tmp_num = substr($strip_tz, 1);
            if (strlen($tmp_num) == 1)
                
        }
    }
}

?>