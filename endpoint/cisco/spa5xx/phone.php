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
	
	function parse_lines_hook($line) {
            
		if (strlen($this->lines[$line]['displayname']) > 12) {
			$short_name = substr($this->lines[$line]['displayname'], 0, 8) . "...";
		} else {
			$short_name = $this->lines[$line]['displayname'];
		}
		if((isset($this->lines[$line]['secret'])) && ($this->lines[$line]['secret'] != "")) {
			$this->lines[$line]['options']['dial_plan'] = $this->options['dial_plan'];
		} else {
			$this->lines[$line]['options']['dial_plan'] = "";
		}
		if(isset($this->options['lineops'][$line])) {
			$this->lines[$line]['options']['displaynameline'] = $this->options['lineops'][$line]['displaynameline'];
			$this->lines[$line]['options']['short_name'] = $short_name;
			if(($this->options['lineops'][$line]['keytype'] == "blf") AND ($this->options['lineops'][$line]['blfext'] != "")) {
				$this->lines[$line]['ext'] = $this->options['lineops'][$line]['blfext'];
				$this->lines[$line]['secret'] = 'n/a';
				$this->lines[$line]['options']['blf_ext_type'] = "Disabled";
				$this->lines[$line]['options']['share_call_appearance'] = "shared";
				$this->lines[$line]['options']['extended_function'] = "fnc=blf+sd+cp;sub=".$this->options['lineops'][$line]['blfext']."@".$this->server[1]['ip'];
			} else {
				if(!isset($this->lines[$line]['secret'])) {
                                        $this->lines[$line]['options']['displaynameline'] = $this->lines[1]['options']['displaynameline'];
                                        $this->lines[$line]['options']['short_name'] = $this->lines[1]['options']['short_name'];
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
	
	function prepare_for_generateconfig() {
		parent::prepare_for_generateconfig();
		
		if(isset($this->options['unit1'])) {
			foreach($this->options['unit1'] as $key => $data) {
				if ($this->options['unit1'][$key]['data'] == '') {
					unset($this->options['unit1'][$key]);
				}
				if(($this->options['unit1'][$key]['data'] != '') && ($this->options['unit1'][$key]['keytype'] == 'blf')) {
					$temp_ext = $this->options['unit1'][$key]['data'];
					$this->options['unit1'][$key]['data'] = "fnc=blf+sd+cp;sub=".$temp_ext."@".$this->server[1]['ip'];
				}
			}
		}
		
		if(isset($this->options['unit2'])) {
			foreach($this->options['unit2'] as $key => $data) {
				if ($this->options['unit2'][$key]['data'] == '') {
					unset($this->options['unit2'][$key]);
				}
				if(($this->options['unit2'][$key]['data'] != '') && ($this->options['unit2'][$key]['keytype'] == 'blf')) {
					$temp_ext = $this->options['unit2'][$key]['data'];
					$this->options['unit2'][$key]['data'] = "fnc=blf+sd+cp;sub=".$temp_ext."@".$this->server[1]['ip'];
				}
			}
		}
		
	}
}
