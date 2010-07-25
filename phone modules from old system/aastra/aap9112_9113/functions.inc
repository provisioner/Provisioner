<?php
require(PHONE_MODULES_PATH.'aastra/functions.inc');
class aap9112_9113 extends aastra {
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
		
		//mac.cfg
		$contents = $endpoint->open_config_file("\$mac.cfg",$phone_info,$config_files_override);
				
		$contents=str_replace('{$ext}', $phone_info['ext'], $contents);
		$contents=str_replace('{$pass}',$phone_info['secret'], $contents);
		$contents=str_replace('{$displayname}',$phone_info['description'],$contents);

		$endpoint->write_config_file($phone_info['mac'].".cfg",$phone_info,$contents,$custom_cfg_data);
		
		//aastra.cfg
		$contents = $endpoint->open_config_file("aastra.cfg",$phone_info,$config_files_override);

		$contents=str_replace('{$srvip}', $global_cfg['srvip'], $contents);

		$endpoint->write_config_file("aastra.cfg",$phone_info,$contents,$custom_cfg_data);


		$this->reboot($id);
		
	}
	
	function delete_config($id) {
		global $global_cfg;
		
		$sql = 'SELECT mac FROM endpointman_mac_list WHERE id = '.$id;
		$result=mysql_query($sql);
		$phone_info=mysql_fetch_array($result);
		$mac = $phone_info['mac'];
		$outfile=$global_cfg['config_location']."cfg/" . $mac . ".txt";
		unlink($outfile);
	}
}
?>