<?php
/**
 * Cisco SIP ATA-18x Phone File
 **
 * @author Andrew Miffleton
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_ata18x_phone extends endpoint_cisco_base {
	function prepareConfig($settings) {
        parent::prepareConfig($settings);

        return $settings;
    }
}