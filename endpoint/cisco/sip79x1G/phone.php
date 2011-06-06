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
	
	function generate_config() {

		if(strlen($this->lines[1]['displayname']) > 12) {
			$name = explode(" ", $this->lines[1]['displayname']);
			$this->lines[1]['displayname'] = substr($name[0],0,12);
		}
		//Cisco likes lower case letters in its mac address
		$this->mac = strtoupper($this->mac);
		//Cisco time offset is in minutes, our global variable is in seconds
		//$this->timezone = $global_cfg['gmtoff']/60;

		//SEP{MAC}.cnf.xml
		$contents = $this->open_config_file("SEP\$mac.cnf.xml");
		$final['SEP'.$this->mac.'.cnf.xml'] = $this->parse_config_file($contents, FALSE);
		
		//XMLDefault.cnf.xml
		$contents = $this->open_config_file("XMLDefault.cnf.xml");
		$final['XMLDefault.cnf.xml'] = $this->parse_config_file($contents, FALSE);
		
		//RINGLIST.DAT
		$contents = $this->open_config_file("RINGLIST.DAT");
		$final['RINGLIST.DAT'] = $this->parse_config_file($contents, FALSE);
		
		//ringlist.xml
		$contents = $this->open_config_file("ringlist.xml");
		$final['ringlist.xml'] = $this->parse_config_file($contents, FALSE);
		
		return($final);
	}
}