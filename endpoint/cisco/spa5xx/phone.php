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
		
		//Yealink support lines 2-6, so let's add them if they're set
        for ($i = 1; $i < 6; $i++) {
			if(($this->lines[$i]['options']['line_key_type'] == "blf") AND ($this->lines[$i]['options']['blf_ext'] != "")) {
				$this->lines[$i]['ext'] = $this->lines[$i]['options']['blf_ext'];
				$this->lines[$i]['secret'] = 'n/a';
				$temp_constr = '{$displayname.line.'.$i.'}';
				if($this->lines[$i]['options']['display_name_line'] == $temp_constr) {
					$this->lines[$i]['options']['display_name_line'] = $this->lines[$i]['options']['blf_ext'];
				} 
				$this->lines[$i]['options']['blf_ext_type'] = "Disabled";
				$this->lines[$i]['options']['share_call_appearance'] = "shared";
				$this->lines[$i]['options']['extended_function'] = "fnc=blf+sd+cp;sub=".$this->lines[$i]['options']['blf_ext']."@".$this->server[1]['ip'];
			} else {
				$this->lines[$i]['options']['blf_ext_type'] = $i;
				//$this->lines[$i]['displayname'] = $this->lines[$i]['options']['display_name_line'];
			}
			$this->lines[$i]['options']['dial_plan'] = $this->options['dial_plan'];
			//limit short name to 6 chars only
			$this->lines[$i]['options']['short_name'] = $this->lines[$i]['options']['display_name_line'];

		}
		
		//{$model}.cfg
		$contents = $this->open_config_file("global.cfg");
		$final[$temp_model.'.cfg'] = $this->parse_config_file($contents, FALSE);
				
		//{$mac}.cfg
		$contents = $this->open_config_file("\$mac.cfg");
		$final['spa'.$this->mac.'.xml'] = $this->parse_config_file($contents, FALSE);
	
		return($final);
	}
}