<?php

/**
 * Cisco SIP ATA-18x Phone File
 *
 * @author Andrew Miffleton
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_ata18x_phone extends endpoint_cisco_base {
	function prepareConfig($settings, $config_manager) {
        parent::prepareConfig($settings, $config_manager);

        return $settings;
    }
}

?>