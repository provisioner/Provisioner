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

		$this->en_htmlspecialchars = FALSE;
		
		if(isset($this->options['softkey'])) {
			foreach($this->options['softkey'] as $key => $data) {
				if ($this->options['softkey'][$key]['type'] == 'empty') {
					unset($this->options['softkey'][$key]);
				} 
			}
		}
		
		if(isset($this->options['topsoftkey'])) {
			foreach($this->options['topsoftkey'] as $key => $data) {
				if ($this->options['topsoftkey'][$key]['type'] == 'empty') {
					unset($this->options['topsoftkey'][$key]);
				} 
			}
		}
		
		if(isset($this->options['prgkey'])) {
			foreach($this->options['prgkey'] as $key => $data) {
				if ($this->options['prgkey'][$key]['type'] == 'empty') {
					unset($this->options['prgkey'][$key]);
				} 
			}
		}
		
		if(isset($this->options['expmod1'])) {
			foreach($this->options['expmod1'] as $key => $data) {
				if ($this->options['expmod1'][$key]['type'] == 'empty') {
					unset($this->options['expmod1'][$key]);
				} 
			}
		}
		
		if(isset($this->options['expmod2'])) {
			foreach($this->options['expmod2'] as $key => $data) {
				if ($this->options['expmod2'][$key]['type'] == 'empty') {
					unset($this->options['expmod2'][$key]);
				} 
			}
		}
		
		if(isset($this->options['expmod3'])) {
			foreach($this->options['expmod3'] as $key => $data) {
				if ($this->options['expmod3'][$key]['type'] == 'empty') {
					unset($this->options['expmod3'][$key]);
				} 
			}
		}
		
		if(isset($this->options['featkeys'])) {
			foreach($this->options['featkeys'] as $key => $data) {
				if ($this->options['featkeys'][$key]['enable'] == '0') {
					unset($this->options['featkeys'][$key]);
				} 
			}
		}
		
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