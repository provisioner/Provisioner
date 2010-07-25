<?php
require(PHONE_MODULES_PATH.'cisco/functions.inc');
class sip_79xx extends cisco {
	function generate_config($id) {
		global $global_cfg, $endpoint;
		
		//Get Phone Information
		$phone_info = $endpoint->get_phone_info($id);
		
		//Cisco likes lower case letters in its mac address
		$phone_info['mac'] = strtoupper($phone_info['mac']);
		//Cisco time offset is in minutes, our global variable is in seconds
		$global_cfg['gmtoff'] = $global_cfg['gmtoff']/60;
		
		//Determine is this is a custom gui config or from a template
		if($phone_info['custom_cfg_template'] > 0) {
			$custom_cfg_data = unserialize($phone_info['template_data']['custom_cfg_data']);
			$config_files_override = unserialize($phone_info['template_data']['config_files_override']);
		} else {
			$custom_cfg_data = unserialize($phone_info['custom_cfg_data']);
			$config_files_override = unserialize($phone_info['config_files_override']);
		}
		

		//SEP{MAC}.cnf.xml
		$contents = $endpoint->open_config_file("SEP\$mac.cnf.xml",$phone_info,$config_files_override);
		$endpoint->write_config_file("SEP" . $phone_info['mac'] . ".cnf.xml",$phone_info,$contents,$custom_cfg_data);
		
		
		//SIP{MAC}.cnf
		$contents = $endpoint->open_config_file("SIP\$mac.cnf",$phone_info,$config_files_override);

		$contents=str_replace('{$ext}', $phone_info['ext'],$contents);
		$contents=str_replace('{$pass}',$phone_info['secret'], $contents);
		$contents=str_replace('{$srvip}', $global_cfg['srvip'], $contents);
		$contents=str_replace('{$gmtoff}',$global_cfg['gmtoff'], $contents);
		$contents=str_replace('{$displayname}',$phone_info['description'], $contents);

		$endpoint->write_config_file("SIP" . $phone_info['mac'] . ".cnf",$phone_info,$contents,$custom_cfg_data);
		
		//SIPDefault.cnf
		$contents = $endpoint->open_config_file("SIPDefault.cnf",$phone_info,$config_files_override);
		$contents=str_replace('{$srvip}', $global_cfg['srvip'], $contents);
		$endpoint->write_config_file("SIPDefault.cnf",$phone_info,$contents,$custom_cfg_data);

		//XMLDefault.cnf.xml
		$contents = $endpoint->open_config_file("XMLDefault.cnf.xml",$phone_info,$config_files_override);
		$endpoint->write_config_file("XMLDefault.cnf.xml",$phone_info,$contents,$custom_cfg_data);
		
		//RINGLIST.DAT
		$contents = $endpoint->open_config_file("RINGLIST.DAT",$phone_info,$config_files_override);
		$endpoint->write_config_file("RINGLIST.DAT",$phone_info,$contents,$custom_cfg_data);
		
		//ringlist.xml
		$contents = $endpoint->open_config_file("ringlist.xml",$phone_info,$config_files_override);
		$endpoint->write_config_file("ringlist.xml",$phone_info,$contents,$custom_cfg_data);
		
		//OS79XX.TXT
		$contents = $endpoint->open_config_file("OS79XX.TXT",$phone_info,$config_files_override);
		$endpoint->write_config_file("OS79XX.TXT",$phone_info,$contents,$custom_cfg_data);
		
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