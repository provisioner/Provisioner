<?php
/**
 * Aastra xxxx Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_aastra_aap9xxx6xxx_phone extends endpoint_aastra_base {
	public $family_line = 'aap9xxx6xxx';
	
	function generate_config() {	
		//mac.cfg
		$contents = $this->open_config_file("\$mac.cfg");
		$final[$this->mac.'.cfg'] = $this->parse_config_file($contents, FALSE);


		
		//aastra.cfg
		$contents = $this->open_config_file("aastra.cfg");
		$final['aastra.cfg'] = $this->parse_config_file($contents, FALSE);
		
		return($final);
	}
}
?>