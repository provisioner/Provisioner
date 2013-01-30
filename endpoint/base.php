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
    public function prepareConfig(&$settings, $config_manager) {
        return $settings;
    }
}

?>