<?php 

/**
 * Yealink t2x rules File
 *
 * @author Andrew Nagy
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_yealink_t2x_phone extends endpoint_yealink_base {
    public function __construct() {
        parent::__construct();
    }

    function prepareConfig(&$config_manager) {
        parent::prepareConfig($config_manager);
    }
}

?>