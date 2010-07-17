<?php
require(PHONE_MODULES_PATH.'yealink/functions.inc');
class t2x extends yealink {
	function generate_config($id) {
		global $global_cfg, $endpoint;
		
		//Get Phone Information
		$phone_info = $endpoint->get_phone_info($id);
		
		//Yealink likes lower case letters in its mac address
		$phone_info['mac'] = strtolower($phone_info['mac']);
		//Grandstream time offset is in minutes, our global variable is in seconds
		$global_cfg['gmtoff'] = $global_cfg['gmtoff']/60;
		
		//Determine is this is a custom gui config or from a template
		if($phone_info['custom_cfg_template'] > 0) {
			$custom_cfg_data = unserialize($phone_info['template_data']['custom_cfg_data']);
			$config_files_override = unserialize($phone_info['template_data']['config_files_override']);
		} else {
			$custom_cfg_data = unserialize($phone_info['custom_cfg_data']);
			$config_files_override = unserialize($phone_info['config_files_override']);
		}
		
		//Global 0000000000000000-blah.cfg file (too many zeros man! why?)
		$contents = $endpoint->open_config_file("y000000000000.cfg",$phone_info,$config_files_override);
		
		//We go ahead and build this each time a phone is added/changed the other models have a different number at the end of the file name eg:-
		//T28:y000000000000.cfg
		//T26:y000000000004.cfg
		//T22:y000000000005.cfg
		//T20:y000000000007.cfg
		switch($phone_info['model']) {
			case "T28":
				$endpoint->write_config_file("y000000000000.cfg",$phone_info,$contents,$custom_cfg_data);
				break;
			case "T26":
				$endpoint->write_config_file("y000000000004.cfg",$phone_info,$contents,$custom_cfg_data);
				break;
			case "T22":
				$endpoint->write_config_file("y000000000005.cfg",$phone_info,$contents,$custom_cfg_data);
				break;
			case "T20":
				$endpoint->write_config_file("y000000000007.cfg",$phone_info,$contents,$custom_cfg_data);
				break;
		}
		
		//$mac.cfg file
		$contents = $endpoint->open_config_file("\$mac.cfg",$phone_info,$config_files_override);
		$contents=str_replace('{$pass}', $phone_info['secret'], $contents);
		$endpoint->write_config_file($phone_info['mac'].".cfg",$phone_info,$contents,$custom_cfg_data);
		
		//Reboot the phone
		$this->reboot($id);
		
	}
	
	function delete_config($id) {
		global $global_cfg;
		
		$sql = 'SELECT mac FROM endpointman_mac_list WHERE id = '.$id;
		$result=mysql_query($sql);
		$phone_info=mysql_fetch_array($result);
		#Grandstream likes lower case letters in its mac address
		$mac = strtolower($phone_info['mac']);
		$outfile=$global_cfg['config_location']."cfg/" . $mac . ".txt";
		unlink($outfile);
	}
	
	function display_options(){
		$display = "";
		return($display);
	}
	
	function process_options(){
		$display = "";
		return($display);
	}
}
?>