<?php
/**
 * Cisco SIP 7900 Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_sip79xx_phone extends endpoint_cisco_base {
	
	public $family_line = 'sip79xx';
	
	function generate_config() {
			
		//Cisco likes lower case letters in its mac address
		$this->mac = strtoupper($this->mac);
		//Cisco time offset is in minutes, our global variable is in seconds
		//$this->timezone = $global_cfg['gmtoff']/60;

		//SEP{MAC}.cnf.xml
		$contents = $this->open_config_file("SEP\$mac.cnf.xml");
		$final['SEP'.$this->mac.'.cnf.xml'] = $this->parse_config_file($contents, FALSE);
		
		//SIP{MAC}.cnf
		$contents = $this->open_config_file("SIP\$mac.cnf");
		$final['SIP'.$this->mac.'.cnf'] = $this->parse_config_file($contents, FALSE);
		
		//SIPDefault.cnf
		$contents = $this->open_config_file("SIPDefault.cnf");
		$final['SIPDefault.cnf'] = $this->parse_config_file($contents, FALSE);
		
		//XMLDefault.cnf.xml
		$contents = $this->open_config_file("XMLDefault.cnf.xml");
		$final['XMLDefault.cnf.xml'] = $this->parse_config_file($contents, FALSE);
		
		//RINGLIST.DAT
		$contents = $this->open_config_file("RINGLIST.DAT");
		$final['RINGLIST.DAT'] = $this->parse_config_file($contents, FALSE);
		
		//ringlist.xml
		$contents = $this->open_config_file("ringlist.xml");
		$final['ringlist.xml'] = $this->parse_config_file($contents, FALSE);
		
		//OS79XX.TXT
		$contents = $this->open_config_file("OS79XX.TXT");
		$final['OS79XX.TXT'] = $this->parse_config_file($contents, FALSE);
		
		return($final);
	}
}