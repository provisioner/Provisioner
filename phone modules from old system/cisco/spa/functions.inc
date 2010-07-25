<?php
require(PHONE_MODULES_PATH.'cisco/functions.inc');
class spa extends cisco {
	function generate_config($id) {
		global $global_cfg, $endpoint;
		
		//Get Phone Information
		$phone_info = $endpoint->get_phone_info($id);
		
		//spa likes lower case letters in its mac address
		$phone_info['mac'] = strtolower($phone_info['mac']);
		$phone_info['model'] = strtolower($phone_info['model']);
		
		//Determine is this is a custom gui config or from a template
		if($phone_info['custom_cfg_template'] > 0) {
			$custom_cfg_data = unserialize($phone_info['template_data']['custom_cfg_data']);
			$config_files_override = unserialize($phone_info['template_data']['config_files_override']);
		} else {
			$custom_cfg_data = unserialize($phone_info['custom_cfg_data']);
			$config_files_override = unserialize($phone_info['config_files_override']);
		}
		
		$contents = $endpoint->open_config_file("global.cfg",$phone_info,$config_files_override);
		
		//Set Static variables 
		$contents=str_replace('{$timezone}',$global_cfg['gmthr'], $contents);
		
		mkdir($global_cfg['config_location']."cfg", 0777);
		$endpoint->write_config_file($phone_info['model'] . ".cfg",$phone_info,$contents,$custom_cfg_data);
		
		
		$contents = $endpoint->open_config_file("\$mac.cfg",$phone_info,$config_files_override);
		
		//Set Static variables 
		$contents=str_replace('{$ext}', $phone_info['ext'],$contents);
		$contents=str_replace('{$pass}',$phone_info['secret'], $contents);
		$contents=str_replace('{$srvip}', $global_cfg['srvip'], $contents);
		$contents=str_replace('{$timezone}',$global_cfg['gmthr'], $contents);
		$contents=str_replace('{$displayname}',$phone_info['description'], $contents);
		
		mkdir($global_cfg['config_location']."cfg", 0777);
		$endpoint->write_config_file("spa" . $phone_info['mac'] . ".cfg",$phone_info,$contents,$custom_cfg_data);
		
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
		$outfile=$global_cfg['config_location']."spa" . $mac . ".cfg";
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