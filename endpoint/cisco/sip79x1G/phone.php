<?php
/**
 * Cisco SIP 79x1g Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_sip79x1g_phone extends endpoint_cisco_base {
	function prepareConfig($settings, $config_manager) {
        parent::prepareConfig($settings, $config_manager);

        return $settings;
    }
}
