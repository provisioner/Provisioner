<?php
require(PHONE_MODULES_PATH.'polycom/functions.inc');
class spom extends polycom {
	function debug() {
		global $global_cfg;
		print_r($global_cfg);
	}
	
	function generate_config($id) {	
		global $global_cfg, $endpoint;
		
		$phone_info = $endpoint->get_phone_info($id);
		
		#Polycom likes lower case letters in its mac address
		$phone_info['mac'] = strtolower($phone_info['mac']);
		
		//Determine is this is a custom gui config or from a template
		if($phone_info['custom_cfg_template'] > 0) {
			$custom_cfg_data = unserialize($phone_info['template_data']['custom_cfg_data']);
			$config_files_override = unserialize($phone_info['template_data']['config_files_override']);
		} else {
			$custom_cfg_data = unserialize($phone_info['custom_cfg_data']);
			$config_files_override = unserialize($phone_info['config_files_override']);
		}
		
		//server.cfg
		$contents = $endpoint->open_config_file("server.cfg",$phone_info,$config_files_override);
		
		$contents=str_replace('{$srvip}', $global_cfg['srvip'], $contents);
		$contents=str_replace('{$gmtoff}',$global_cfg['gmtoff'],$contents);

		$endpoint->write_config_file("spom_server.cfg",$phone_info,$contents,$custom_cfg_data);


		//sip.cfg
		$contents = $endpoint->open_config_file("sip.cfg",$phone_info,$config_files_override);
		$endpoint->write_config_file("spom_sip.cfg",$phone_info,$contents,$custom_cfg_data);

		
		//sip_custom.cfg
		$contents = $endpoint->open_config_file("sip.cfg",$phone_info,$config_files_override);
		$endpoint->write_config_file($phone_info['mac']."_".$phone_info['ext']."_sip_custom.cfg",$phone_info,$contents,$custom_cfg_data);
		
		//write out mac.cfg
		$contents = $endpoint->open_config_file("\$mac.cfg",$phone_info,$config_files_override);
		$contents=str_replace('{$ext}', $phone_info['ext'],$contents);
		$contents=str_replace('{$mac}',$phone_info['mac'], $contents);
		$endpoint->write_config_file($phone_info['mac'].".cfg",$phone_info,$contents,$custom_cfg_data);

		
		$outfile=$global_cfg['config_location']."".$phone_info['mac']."_".$phone_info['ext']."_custom.cfg";
		if (!file_exists($outfile)) {
			$contents = $endpoint->open_config_file("\$mac_custom.cfg",$phone_info,$config_files_override);
			$endpoint->write_config_file($phone_info['mac']."_custom.cfg",$phone_info,$contents,$custom_cfg_data);
		}
		
		//write out ext.cfg
		$contents = $endpoint->open_config_file("\$ext.cfg",$phone_info,$config_files_override);
		$contents=str_replace('{$displayname}', $phone_info['description'],$contents);
		$contents=str_replace('{$ext}',$phone_info['ext'], $contents);
		$contents=str_replace('{$pass}',$phone_info['secret'], $contents);
		$contents=str_replace('{$mac}',$phone_info['mac'], $contents);
		$endpoint->write_config_file($phone_info['ext'].".cfg",$phone_info,$contents,$custom_cfg_data);
	
		
		$outfile=$global_cfg['config_location']."polycom/logs/".$phone_info['mac']."-app.log";
		if (!file_exists($outfile)) {
			mkdir($global_cfg['config_location']."polycom", 0777);
			mkdir($global_cfg['config_location']."polycom/logs", 0777);
			$line = "";
			$wfh=fopen($outfile,'w');
			fwrite($wfh,$line);
			fclose($wfh);
			chmod($outfile, 0777);
		}
		
		$outfile=$global_cfg['config_location']."polycom/logs/".$phone_info['mac']."-boot.log";
		if (!file_exists($outfile)) {
			$line = "";
			$wfh=fopen($outfile,'w');
			fwrite($wfh,$line);
			fclose($wfh);
			chmod($outfile, 0777);
		}
			
		$this->reboot($id);	
	}
	
	function delete_config($id) {
		global $global_cfg;
		
		$sql = 'SELECT mac FROM endpointman_mac_list WHERE id = '.$id;
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		#Grandstream likes lower case letters in its mac address
		$mac = strtolower($row['mac']);
		$outfile=$global_cfg['config_location']."cfg/" . $mac . ".txt";
		unlink($outfile);
	}
}
?>