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
	public $en_htmlspecialchars = FALSE;
	public $dynamic_mapping = array(
		'$mac.cfg'=>array('$mac.cfg','aastra.cfg'),
		'aastra.cfg'=>'#This File is intentionally left blank'
	);

	function generate_file($file,$extradata,$ignoredynamicmapping=FALSE) {
		$config=parent::generate_config($file,$extradata,$ignoredynamicmapping);
		if (($extradata=='$mac.cfg') && ($ignoredynamicmapping===FALSE) && ($this->enable_encryption)) {
			$this->enable_encryption();
			$config=$this->encrypt_files(array('$mac.cfg'=>$config));
			return $config['$mac.cfg'];
		} else {
			$this->disable_encryption();
			return $config;
		}
	}
	
	function prepare_for_generateconfig() {
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
		
		if(!isset($this->options['provisioning_server'])) {
			$this->options['provisioning_server'] = $this->server[1]['ip'];
		}
		
		if(!isset($this->options['provisioning_path'])) {
			$this->options['provisioning_path'] = '';
		}
				
		switch($this->provisioning_type) {
			case "tftp":
				$this->options['provisioning_protocol'] = 'TFTP';
				break;
			case "http":
				$this->options['provisioning_protocol'] = 'HTTP';
				break;
			case "https":
				$this->options['provisioning_protocol'] = 'HTTPS';
				break;
		}		
				
	}
}
?>
