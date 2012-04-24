<?php

/**
 * Yealink Modules Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cloudtc_glass_phone extends endpoint_cloudtc_base {

    public $family_line = 'glass';

    function parse_lines_hook($line_data, $line_total) {
		$line_data['server_xml_value'] = (isset($line_data['use_outbound_proxy']) && $line_data['use_outbound_proxy']) ? $line_data['outbound_host'] : $line_data['server_host'];
        return($line_data);
    }

    function prepare_for_generateconfig() {
        $this->mac = strtolower($this->mac);
		parent::prepare_for_generateconfig();
		
	}

}