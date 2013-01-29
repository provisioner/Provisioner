<?php
/**
 * Cisco SIP 7900 Phone File
 **
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_sip79xx_phone extends endpoint_cisco_base {
	function prepareConfig($settings) {
        parent::prepareConfig($settings);

        return $settings;
    }
}
