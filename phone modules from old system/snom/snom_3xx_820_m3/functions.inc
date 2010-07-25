<?php
require(PHONE_MODULES_PATH.'snom/functions.inc');
class snom_3xx_820_m3 extends snom {
	function debug() {
		global $global_cfg;
		print_r($global_cfg);
	}
	
	function generate_config($id) {	
		global $global_cfg, $endpoint;
		
		$phone_info = $endpoint->get_phone_info($id);
		
		//Determine is this is a custom gui config or from a template
		if($phone_info['custom_cfg_template'] > 0) {
			$custom_cfg_data = unserialize($phone_info['template_data']['custom_cfg_data']);
			$config_files_override = unserialize($phone_info['template_data']['config_files_override']);
		} else {
			$custom_cfg_data = unserialize($phone_info['custom_cfg_data']);
			$config_files_override = unserialize($phone_info['config_files_override']);
		}
		
		#SNOM likes lower case letters in its mac address
		$phone_info['mac'] = strtoupper($phone_info['mac']);
		
		
		//$mac_firmware.xml
		$contents = $endpoint->open_config_file("\$mac_firmware.xml",$phone_info,$config_files_override);
		$endpoint->write_config_file($phone_info['mac']."_firmware.xml",$phone_info,$contents,$custom_cfg_data);
		
		//snom(model).htm
		$contents = $endpoint->open_config_file("snom\$model.htm",$phone_info,$config_files_override);
		$contents=str_replace('{$srvip}',$global_cfg['srvip'],$contents);
		$endpoint->write_config_file("snom".$phone_info['model'].".htm",$phone_info,$contents,$custom_cfg_data);

		//snom(model)-(mac).htm
		$contents = $endpoint->open_config_file("snom\$model-\$mac.htm",$phone_info,$config_files_override);
		$contents=str_replace('{$srvip}',$global_cfg['srvip'],$contents);
		$contents=str_replace('{$mac}',$phone_info['mac'],$contents);
		$contents=str_replace('{$ext}',$phone_info['ext'],$contents);
		$endpoint->write_config_file("snom".$phone_info['model']."-".$phone_info['mac'].".htm",$phone_info,$contents,$custom_cfg_data);

		
		//general.xml
		$contents = $endpoint->open_config_file("general.xml",$phone_info,$config_files_override);
		$contents=str_replace('{$gmtoff}',$global_cfg['gmtoff'],$contents);
		$contents=str_replace('{$srvip}',$global_cfg['srvip'],$contents);
		$contents=str_replace('{$tz}',$global_cfg['tz'],$contents);
		$endpoint->write_config_file("general.xml",$phone_info,$contents,$custom_cfg_data);

		
		//write out mac.xml
		$contents = $endpoint->open_config_file("/\$mac.xml",$phone_info,$config_files_override);
		$contents=str_replace('{$ext}', $phone_info['ext'],$contents);
		$contents=str_replace('{$pass}',$phone_info['secret'], $contents);
		$contents=str_replace('{$srvip}', $global_cfg['srvip'], $contents);
		$contents=str_replace('{$displayname}',$phone_info['description'],$contents);	
		$endpoint->write_config_file($phone_info['mac'].".xml",$phone_info,$contents,$custom_cfg_data);
		
		
		//write out mac_ext_custom_xml
		$outfile=$global_cfg['config_location'].$phone_info['mac']."_".$phone_info['ext']."_custom.xml";
		if (!file_exists($outfile)) {
			$contents = $endpoint->open_config_file("\$mac_\$ext_custom.xml",$phone_info,$config_files_override);
			$endpoint->write_config_file($phone_info['mac']."_".$phone_info['ext']."_custom.xml",$phone_info,$contents,$custom_cfg_data);
		}
		
		$outfile=$global_cfg['config_location']."general_custom.xml";
		if (!file_exists($outfile)) {
			$contents = $endpoint->open_config_file("general_custom.xml",$phone_info,$config_files_override);
			$endpoint->write_config_file("general_custom.xml",$phone_info,$contents,$custom_cfg_data);
		}
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
}
?>