<?php

/**
 * Cisco SPA Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_linksysata_phone extends endpoint_cisco_base {

    public $family_line = 'linksysata';

    function parse_lines_hook($line_data,$line_total) {
        $line_data['dial_plan'] = ((isset($line_data['secret'])) && ($line_data['secret'] != "") && (isset($this->settings['dial_plan']))) ? htmlentities($this->settings['dial_plan']) : "";

        $line_data['use_dns_srv'] = (isset($line_data['transport']) && ($line_data['transport'] == "DNSSRV")) ? 'Yes' : 'No';
        return($line_data);
    }

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();
        //spa likes lower case letters in its mac address
        $this->mac = strtolower($this->mac);

        if ($this->settings['network']['connection_type'] == 'STATIC') {
            $this->settings['current_ip'] = $this->settings['network']['ipv4'];
            $this->settings['current_netmask'] = $this->settings['network']['subnet'];
            $this->settings['current_gateway'] = $this->settings['network']['gateway'];
            $this->settings['primary_dns'] = $this->settings['network']['primary_dns'];
            $this->settings['connection_type'] = 'Static IP';
        } else {
            $this->settings['connection_type'] = 'DHCP';
        }
    }

}
