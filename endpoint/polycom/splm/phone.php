<?php
/**
 * Phone Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_polycom_splm_phone extends endpoint_polycom_base {

	public $family_line = 'splm';	
		
	function generate_config() {			
		//Polycom likes lower case letters in its mac address
		$this->mac = strtolower($this->mac);

		$contents = $this->open_config_file('server.cfg');
		$final['server_312.cfg'] = $this->parse_config_file($contents, FALSE);
		$file_list = 'server_312.cfg, ';
		
		$contents = $this->open_config_file('{$mac}_reg.cfg');
		$final[$this->mac.'_reg.cfg'] = $this->parse_config_file($contents,FALSE);
		$file_list = $this->mac.'_reg.cfg, ';
		
		$contents = $this->open_config_file('phone1.cfg');
		$final['phone1_312.cfg'] = $this->parse_config_file($contents, FALSE);
		$file_list .= ' phone1_312.cfg, ';
		
		$contents = $this->open_config_file('sip.cfg');
		$final['sip_312.cfg'] = $this->parse_config_file($contents, FALSE);
		$file_list .= ' sip_312.cfg';
				
		$this->options['createdFiles'] = $file_list;
		
		$contents = $this->open_config_file('000000000000.cfg');
		$final['000000000000.cfg'] = $this->parse_config_file($contents, FALSE);
		
		//Old School
		$contents = $this->open_config_file('{$mac}.cfg');
		$final[$this->mac.'.cfg'] = $this->parse_config_file($contents, FALSE);
		
		$this->directory_structure = array("logs","overrides","contacts","licenses");
		
		$this->copy_files = array("SoundPointIPLocalization","SoundPointIPWelcome.wav");
		
		$contents = $this->open_config_file('000000000000-directory.xml');
		$final['contacts/000000000000-directory.xml'] = $contents;
		
		$final['logs/'.$this->mac.'-boot.log'] = "";
		$final['logs/'.$this->mac.'-app.log'] = "";
		
		$this->protected_files = array('overrides/'.$this->mac.'-phone.cfg', 'logs/'.$this->mac.'-boot.log', 'logs/'.$this->mac.'-app.log', 'SoundPointIPLocalization');
		
		$contents = $this->open_config_file('{$mac}-phone.cfg');
		$final['overrides/'.$this->mac.'-phone.cfg'] = $this->parse_config_file($contents, FALSE);
				
		return($final);	
	}

}
