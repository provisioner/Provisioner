<?php
/**
 * Cisco SPA Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_spa5xx_phone extends endpoint_cisco_base {
	
	public $family_line = 'spa5xx';
	
	function generate_config() {
		//spa likes lower case letters in its mac address
		$this->mac = strtolower($this->mac);
		$temp_model = strtoupper($this->model);
		$temp_model = str_replace("SPA", "spa", $temp_model);
		
		//{$model}.cfg
		$contents = $this->open_config_file("global.cfg");
		$final[$temp_model.'.cfg'] = $this->parse_config_file($contents, FALSE);
				
		//{$mac}.cfg
		$contents = $this->open_config_file("\$mac.cfg");
		$final['spa'.$this->mac.'.xml'] = $this->parse_config_file($contents, FALSE);
	
		return($final);
	}
}