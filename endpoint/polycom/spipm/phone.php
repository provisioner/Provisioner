<?php
/**
 * Phone Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_polycom_spipm_phone extends endpoint_polycom_base {

	public static $family_line = 'spipm';	
		
	function generate_config() {			
		//Polycom likes lower case letters in its mac address
		$this->mac = strtolower($this->mac);

		$contents = $this->open_config_file('{$domain}.cfg');
		$final['{$domain}.cfg'] = $this->parse_config_file($contents, FALSE);
		$file_list = '{$domain}.cfg';
		

		
		$contents = $this->open_config_file('phone.cfg');
		$final['phone.cfg'] = $this->parse_config_file($contents, FALSE);
		$file_list .= ' phone.cfg';
		
		$contents = $this->open_config_file('phone1.cfg');
		$final['phone1.cfg'] = $this->parse_config_file($contents, FALSE);
		$file_list .= ' phone1.cfg';

		
		$contents = $this->open_config_file('reg_{$line}.cfg');
	
		foreach($this->secret['line'] as $key => $data) {
			if(isset($this->secret['line'][$key])) {
				$final['reg_'.$this->ext['line'][$key].'.cfg'] = $this->parse_config_file($contents,FALSE,NULL,$key);
				$file_list .= ' reg_'.$this->ext['line'][$key].'.cfg';
			}
		}
		

		$contents = $this->open_config_file('sip.cfg');
		$final['sip.cfg'] = $this->parse_config_file($contents, FALSE);
		
		$file_list .= ' sip.cfg';
		
		
		$this->xml_variables['line']['global'] = $this->array_merge_check($this->xml_variables['line']['global'],array("createdFiles" => array("value" => $file_list)));
		
		$contents = $this->open_config_file('{$mac}.cfg');
		$final[$this->mac.'.cfg'] = $this->parse_config_file($contents, FALSE);
		
		return($final);	
	}

}
