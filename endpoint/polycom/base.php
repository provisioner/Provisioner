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
    public function __construct() {
        parent::__construct();
    }

    function prepareConfig(&$config_manager) {
        parent::prepareConfig($config_manager);

        $this->_set_timezone($config_manager);
    }

    private function _set_timezone(&$config_manager) {
        $constants = $config_manager->get_constants();
        $settings = $config_manager->get_settings();

        $tz = $constants['timezone_lookup'][$settings['timezone']];
        $strip_tz = explode(":", $tz);

        $main_tz = $strip_tz[0] * 60 * 60;
        $sub_tz = 0;
        if (isset($strip_tz[1]))
            $sub_tz = 30 * 60;

        $settings['timezone'] = $main_tz + $sub_tz;

        $config_manager->set_settings($settings);
    }
}

?>