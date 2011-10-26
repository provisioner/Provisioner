<?php
/**
 * Phone Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_polycom_ptx151_phone extends endpoint_polycom_base {

	public $family_line = 'ptx151';	
		
	function generate_config() {			
		//Polycom likes lower case letters in its mac address
		$this->mac = strtolower($this->mac);

		//do stuff here
		//$contents = $this->open_config_file('server.cfg');
		//$final['server_325.cfg'] = $this->parse_config_file($contents, FALSE);
				
		return($final);
	}
}