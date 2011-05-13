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
	
	function parse_lines_hook($line) {
		$short_name = substr($this->lines[$line]['displayname'], 0, 8) . "...";
		if((isset($this->lines[$line]['secret'])) && ($this->lines[$line]['secret'] != "")) {
			$this->lines[$line]['options']['dial_plan'] = $this->options['dial_plan'];
		} else {
			$this->lines[$line]['options']['dial_plan'] = "";
		}
		if(isset($this->options['lineops'])) {
			$this->lines[$line]['options']['displaynameline'] = $this->options['lineops'][$line]['displaynameline'];
			$this->lines[$line]['options']['short_name'] = $this->options['lineops'][$line]['displaynameline'];
			if(($this->options['lineops'][$line]['keytype'] == "blf") AND ($this->options['lineops'][$line]['blfext'] != "")) {
				$this->lines[$line]['ext'] = $this->options['lineops'][$line]['blfext'];
				$this->lines[$line]['secret'] = 'n/a';
				$this->lines[$line]['options']['blf_ext_type'] = "Disabled";
				$this->lines[$line]['options']['share_call_appearance'] = "shared";
				$this->lines[$line]['options']['extended_function'] = "fnc=blf+sd+cp;sub=".$this->options['lineops'][$line]['blfext']."@".$this->server[1]['ip'];
			} else {
				if(!isset($this->lines[$line]['secret'])) {
					$this->lines[$line]['ext'] = '';
					$this->lines[$line]['secret'] = '';
					$this->lines[$line]['options']['blf_ext_type'] = "1";
					$this->lines[$line]['options']['share_call_appearance'] = "private";
					$this->lines[$line]['options']['extended_function'] = "";
				} else {
					$this->lines[$line]['options']['blf_ext_type'] = $line;
					$this->lines[$line]['options']['share_call_appearance'] = "private";
					$this->lines[$line]['options']['extended_function'] = "";
				}
			}
		} else {
			$this->lines[$line]['options']['displaynameline'] = $this->lines[$line]['displayname'];
			$this->lines[$line]['options']['short_name'] = $short_name;
			$this->lines[$line]['options']['blf_ext_type'] = "1";
			$this->lines[$line]['options']['share_call_appearance'] = "private";
			$this->lines[$line]['options']['extended_function'] = "";
		}
	}
	
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