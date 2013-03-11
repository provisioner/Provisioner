<?php

/**
 * Snom Base File
 *
 * @author Jort
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_snom_base extends endpoint_base {
    public function __construct(&$config_manager) {
        parent::__construct($config_manager);
    }

    function prepareConfig() {
        parent::prepareConfig();
    }
}

?>