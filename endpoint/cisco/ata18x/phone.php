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
	
	function prepare_for_generateconfig() {
		parent::prepare_for_generateconfig();
		if(strlen($this->lines[1]['displayname']) > 12) {
			$name = explode(" ", $this->lines[1]['displayname']);
			$this->lines[1]['displayname'] = substr($name[0],0,12);
		}
		//Cisco time offset is in minutes, our global variable is in seconds
		//$this->timezone = $global_cfg['gmtoff']/60;
	}
}
