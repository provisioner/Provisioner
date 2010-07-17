<?php
class endpoint_polycom_spipm_phone extends endpoint_polycom_base {

	public static $family_line = 'spipm';	
		
	function generate_config() {			
		//Polycom likes lower case letters in its mac address
		$this->mac = strtolower($this->mac);

		/*
		//server.cfg
		$contents = $this->open_config_file("server.cfg");
		$final['server.cfg'] = $this->parse_config_file($contents, FALSE);
		

		//sip.cfg
		$contents = $this->open_config_file("sip.cfg");
		$final['sip.cfg'] = $this->parse_config_file($contents, FALSE);

		
		//sip_custom.cfg
		$contents = $this->open_config_file("sip_custom.cfg");
		$final['sip_custom.cfg'] = $this->parse_config_file($contents, FALSE);
		
		//write out mac.cfg
		$contents = $this->open_config_file('$mac.cfg');
		$final['$mac.cfg'] = $this->parse_config_file($contents, FALSE);
		
		
		//write out ext.cfg
		$contents = $this->open_config_file('$ext.cfg');
		$final['$ext.cfg'] = $this->parse_config_file($contents, FALSE);
		*/

		$contents = $this->open_config_file('{$domain}.cfg');
		$final['{$domain}.cfg'] = $this->parse_config_file($contents, FALSE);
		
		$contents = $this->open_config_file('{$mac}.cfg');
		$final['{$mac}.cfg'] = $this->parse_config_file($contents, FALSE);
		
		$contents = $this->open_config_file('phone.cfg');
		$final['phone.cfg'] = $this->parse_config_file($contents, FALSE);
		
		
		
		$contents = $this->open_config_file('phone1.cfg');
		$final['phone1.cfg'] = $this->parse_config_file($contents, FALSE);

		
		$contents = $this->open_config_file('reg_{$line}.cfg');
		$final['reg_{$line}.cfg'] = $this->parse_config_file($contents, FALSE);
		

		$contents = $this->open_config_file('sip.cfg');
		$final['sip.cfg'] = $this->parse_config_file($contents, FALSE);

		
		return($final);	
	}

}
