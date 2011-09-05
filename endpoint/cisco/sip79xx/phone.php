<?php
/**
 * Cisco SIP 7900 Phone File
 **
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_sip79xx_phone extends endpoint_cisco_base {
	
	public $family_line = 'sip79xx';
	function prepare_for_generateconfig() {
		parent::prepare_for_generateconfig();
		$this->config_file_replacements['$mac']=strtoupper($this->mac);
		if(strlen($this->lines[1]['displayname']) > 12) {
			$name = explode(" ", $this->lines[1]['displayname']);
			$this->lines[1]['displayname'] = substr($name[0],0,12);
		}
		//Cisco time offset is in minutes, our global variable is in seconds
		//$this->timezone = $global_cfg['gmtoff']/60;
	}
}
