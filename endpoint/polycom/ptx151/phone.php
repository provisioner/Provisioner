<?php
/**
 * Phone Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 **/
class endpoint_polycom_ptx151_phone extends endpoint_polycom_base {
    function prepareConfig($settings, $config_manager) {
        parent::prepareConfig($settings, $config_manager);

        return $settings;
    }
}
