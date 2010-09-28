<?php
/**
 * Cisco SPA Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_spa_phone extends endpoint_cisco_base {
	
	public $family_line = 'spa';
	
	function generate_config() {
		//spa likes lower case letters in its mac address
		$this->mac = strtolower($this->mac);
		$this->model = strtolower($this->model);
		
		//{$model}.cfg
		$contents = $this->open_config_file("global.cfg");
		$final[$this->model.'.cfg'] = $this->parse_config_file($contents, FALSE);
				
		//{$mac}.cfg
		$contents = $this->open_config_file("\$mac.cfg");
		$final['spa'.$this->mac.'.cfg'] = $this->parse_config_file($contents, FALSE);
	
		return($final);
	}
}