<?php
/**
 * Cisco SIP 7900 Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_sip79x1G_phone extends endpoint_cisco_base {
	
	public $family_line = 'sip79x1G';
	function prepare_for_generateconfig() {
		parent::prepare_for_generateconfig();
		$this->config_file_replacements['$mac']=strtoupper($this->mac);
		foreach ($this->lines AS &$line) {
			if (array_key_exists('displayname',$line) && (strlen($line['displayname']) > 12)) {
				$name = explode(" ", $line['displayname']);
				$line['displayname'] = substr($name[0],0,12);
			}
		}
		//Cisco time offset is in minutes, our global variable is in seconds
		//$this->timezone = $global_cfg['gmtoff']/60;
	}
	
}
