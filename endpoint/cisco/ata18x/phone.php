<?php
/**
 * Cisco SIP ATA-18x Phone File
 **
 * @author Andrew Miffleton
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_ata18x_phone extends endpoint_cisco_base {
	
	public $family_line = 'ata18x';

    	function parse_lines_hook($line_data, $line_total) {
		if(strlen($line_data['displayname']) > 12) {
			$name = explode(" ", $line_data['displayname']);
			$line_data['displayname'] = substr($name[0],0,12);
		}
        	return($line_data);
    	}
	
	function prepare_for_generateconfig() {
		parent::prepare_for_generateconfig();
		//Cisco time offset is in minutes, our global variable is in seconds
		//$this->timezone = $global_cfg['gmtoff']/60;
	}
}
