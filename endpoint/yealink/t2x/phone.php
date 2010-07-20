<?php
class endpoint_yealink_t2x_phone extends endpoint_polycom_base {

	public static $family_line = 't2x';	
		
	function generate_config() {
		//Yealink likes lower case letters in its mac address
		$this->mac = strtolower($this->mac);
		
		
		//Global 0000000000000000-blah.cfg file (too many zeros man! why?)
		$contents = $this->open_config_file("y000000000000.cfg");
		
		//We go ahead and build this each time a phone is added/changed the other models have a different number at the end of the file name eg:-
		//T28:y000000000000.cfg
		//T26:y000000000004.cfg
		//T22:y000000000005.cfg
		//T20:y000000000007.cfg
		switch($this->model) {
			case "T28":
				$final['y000000000000.cfg'] = $this->parse_config_file($contents, FALSE);
				break;
			case "T26":
				$final['y000000000004.cfg'] = $this->parse_config_file($contents, FALSE);
				break;
			case "T22":
				$final['y000000000005.cfg'] = $this->parse_config_file($contents, FALSE);
				break;
			case "T20":
				$final['y000000000007.cfg'] = $this->parse_config_file($contents, FALSE);
				break;
		}
		
		//$mac.cfg file
		$contents = $endpoint->open_config_file("\$mac.cfg");
		$final['{$mac}.cfg'] = $this->parse_config_file($contents, FALSE);		
	}
}
?>