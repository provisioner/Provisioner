<?php
/**
 * Phone Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_polycom_spipm_phone extends endpoint_polycom_base {

	public $family_line = 'spipm';	
		
	function generate_config() {			
		//Polycom likes lower case letters in its mac address
		$this->mac = strtolower($this->mac);
		
        for ($i = 1; $i < 10; $i++) {
			if(isset($this->lines[$i]['secret'])) {
            	$this->lines[$i]['options']['digitmap'] = (isset($this->options['digitmap']) ? $this->options['digitmap'] : NULL);
            	$this->lines[$i]['options']['digitmaptimeout'] = (isset($this->options['digitmaptimeout']) ? $this->options['digitmaptimeout'] : NULL);
            	$this->lines[$i]['options']['microbrowser_main_home'] = (isset($this->options['microbrowser_main_home']) ? $this->options['microbrowser_main_home'] : NULL);
            	$this->lines[$i]['options']['idle_display'] = (isset($this->options['idle_display']) ? $this->options['idle_display'] : NULL);
            	$this->lines[$i]['options']['idle_display_refresh'] = (isset($this->options['idle_display_refresh']) ? $this->options['idle_display_refresh'] : NULL);
			}
        }
		
		$contents = $this->open_config_file('{$mac}_reg.cfg');
		$final[$this->mac.'_reg.cfg'] = $this->parse_config_file($contents,FALSE);
		$file_list = $this->mac.'_reg.cfg, ';
		
		$contents = $this->open_config_file('sip.cfg');
		$final['sip.cfg'] = $this->parse_config_file($contents, FALSE);
		$file_list .= ' sip.cfg';
				
		$this->options['createdFiles'] = $file_list;
		
		//Old School
		$contents = $this->open_config_file('{$mac}.cfg');
		$final[$this->mac.'.cfg'] = $this->parse_config_file($contents, FALSE);
		
		$this->directory_structure = array("logs","overrides","contacts","licenses","SoundPointIPLocalization");
		
		$this->copy_files = array("SoundPointIPLocalization","SoundPointIPWelcome.wav","LoudRing.wav");
		
		$contents = $this->open_config_file('000000000000-directory.xml');
		$final['contacts/000000000000-directory.xml'] = $contents;
		
		$final['logs/'.$this->mac.'-boot.log'] = "";
		$final['logs/'.$this->mac.'-app.log'] = "";
		
		$this->protected_files = array('overrides/'.$this->mac.'-phone.cfg', 'logs/'.$this->mac.'-boot.log', 'logs/'.$this->mac.'-app.log','SoundPointIPLocalization');
		
		$contents = $this->open_config_file('{$mac}-phone.cfg');
		$final['overrides/'.$this->mac.'-phone.cfg'] = $this->parse_config_file($contents, FALSE);
				
		return($final);
	}


}
