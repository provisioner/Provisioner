<?php
/**
 * Snom 300, 320, 360, 370, 820 & M3 Provisioning System
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_snom_3xx820m3_phone extends endpoint_snom_base {

	public $family_line = '3xx820m3';
	
	function generate_config() {	
		#SNOM likes UPPER case letters in its mac address
		$this->mac = strtoupper($this->mac);		
		
		//snom(model).htm
		$contents = $this->open_config_file("snom\$model.htm");
		$final["snom".$this->model.".htm"] = $this->parse_config_file($contents, FALSE);				

		//snom(model)-(mac).htm
		$contents = $this->open_config_file("snom\$model-\$mac.htm");
		$final["snom".$this->model."-".$this->mac.".htm"] = $this->parse_config_file($contents, FALSE);				
		
		//general.xml
		$contents = $this->open_config_file("general.xml");
		$final["general.xml"] = $this->parse_config_file($contents, FALSE);		

		//write out general_custom.xml
		$contents = $this->open_config_file("general_custom.xml");	
		$final["general_custom.xml"] = $this->parse_config_file($contents, FALSE);

		//write out (mac)_firmware.xml
		$contents = $this->open_config_file("\$mac_firmware.xml");
		$final[$this->mac."_firmware.xml"] = $this->parse_config_file($contents, FALSE);
		
		$this->protected_files = array('general_custom.xml');
		
		return($final);
	}
}
