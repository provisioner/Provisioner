<?php
/**
 * Phone Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_polycom_firmware325_phone extends endpoint_polycom_base {

	public $family_line = 'firmware325';	
	function prepare_for_generateconfig() {
		parent::prepare_for_generateconfig();
		
		for ($i = 1; $i < 10; $i++) {
			if(isset($this->lines[$i]['secret'])) {
				$this->lines[$i]['options']['digitmap'] = (isset($this->options['digitmap']) ? $this->options['digitmap'] : '');
				$this->lines[$i]['options']['digitmaptimeout'] = (isset($this->options['digitmaptimeout']) ? $this->options['digitmaptimeout'] : '');
				$this->lines[$i]['options']['microbrowser_main_home'] = (isset($this->options['microbrowser_main_home']) ? $this->options['microbrowser_main_home'] : '');
				$this->lines[$i]['options']['idle_display'] = (isset($this->options['idle_display']) ? $this->options['idle_display'] : '');
				$this->lines[$i]['options']['idle_display_refresh'] = (isset($this->options['idle_display_refresh']) ? $this->options['idle_display_refresh'] : '');
			}
		}

		$this->options['createdFiles'] = 'server_325.cfg, ' . $this->mac.'_reg.cfg, phone1_325.cfg, sip_325.cfg';
		
		$this->directory_structure = array("logs","overrides","contacts","licenses","SoundPointIPLocalization");
		
		$this->copy_files = array("SoundPointIPLocalization","SoundPointIPWelcome.wav","LoudRing.wav");
		
		$this->protected_files = array('overrides/'.$this->mac.'-phone.cfg', 'logs/'.$this->mac.'-boot.log', 'logs/'.$this->mac.'-app.log','SoundPointIPLocalization');
	}
}
