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

		//If no 'domain' is set then just name default for cfg file
        $this->options['domain'] = (isset($this->options['domain']) ? $this->options['domain'] : 'default');

		$contents = $this->open_config_file('{$domain}.cfg');
		$final['spipm_'.$this->options['domain'].'.cfg'] = $this->parse_config_file($contents, FALSE);
		$file_list = 'spipm_'.$this->options['domain'].'.cfg, ';
		
		$contents = $this->open_config_file('phone.cfg');
		$final['phone.cfg'] = $this->parse_config_file($contents, FALSE);
		//$file_list .= ' phone.cfg,';
		
		$contents = $this->open_config_file('{$mac}-phone.cfg');
		$final[$this->mac.'-phone.cfg'] = $this->parse_config_file($contents, FALSE);
		
		$contents = $this->open_config_file('phone1.cfg');
		$final['phone1.cfg'] = $this->parse_config_file($contents, FALSE);
		
		$contents = $this->open_config_file('reg_{$line}.cfg');
	
		foreach($this->lines as $key => $data) {
			if(isset($this->lines[$key]['secret'])) {
				$final['reg_'.$this->lines[$key]['ext'].'.cfg'] = $this->parse_config_file($contents,FALSE,NULL,$key);
				$file_list .= ' reg_'.$this->lines[$key]['ext'].'.cfg,';
			}
		}

		$contents = $this->open_config_file('sip.cfg');
		$final['spipm_sip.cfg'] = $this->parse_config_file($contents, FALSE);
		
		$file_list .= ' spipm_sip.cfg';
				
		$this->options['createdFiles'] = $file_list;
		
		$contents = $this->open_config_file('{$mac}.cfg');
		$final[$this->mac.'.cfg'] = $this->parse_config_file($contents, FALSE);
				
		return($final);	
	}


}
