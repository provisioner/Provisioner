<?php
require(PHONE_MODULES_PATH.'aastra/functions.inc');
class aap9_6 extends aastra {
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
	
	function display_options(){
		$display = '<center><input name="aastra_scripts" type="submit" id="aastra_scripts" value="Setup Default Configuration file for Aastra Scripts" /></center>';
		return($display);
	}
	
	function process_options(){
		if(isset($_REQUEST['aastra_scripts'])) {
			$script_data = "# XML Configuration\n".
			"action uri incoming: http://{\$srvip}:9080/aastra/asterisk/incoming.php?number=$\$REMOTENUMBER$$&name=$\$INCOMINGNAME$$&user={\$ext}\n".
			"action uri outgoing: http://{\$srvip}:9080/aastra/asterisk/outgoing.php?number=$\$REMOTENUMBER$$\n".
			"action uri onhook: http://{\$srvip}:9080/aastra/asterisk/onhook.php?number=$\$REMOTENUMBER$$&name=$\$INCOMINGNAME$$&user={\$ext}\n".
			"action uri registered: http://{\$srvip}:9080/aastra/asterisk/sync.php?action=register&user={\$ext}\n".
			"action uri poll: http://{\$srvip}:9080/aastra/asterisk/sync.php?action=check&user={\$ext}\n".
			"action uri poll interval: 1800\n".
			"sip xml notify event: 1\n".
			"action uri xml sip notify: http://{\$srvip}:9080/aastra/asterisk/sync.php?action=notify&user={\$ext}\n".
			"xml application URI: http://{\$srvip}:9080/aastra/menu/mymenu.php?menu_source=menu1&menu_user={\$ext}&user={\$ext}\n".
			"xml application title: Applications\n".
			"xml get timeout: 30\n".
			"sip intercom type: 2\n".
			"sip intercom prefix code: *80\n".
			"sip intercom line: 1\n".
			"sprecode: asterisk;70\n".
			"sip allow auto answer: 1\n".
			"sip intercom mute mic: 0\n".
			"directed call pickup: 1\n".
			"directed call pickup prefix: **\n".
			"auto offhook: 0\n".
			"bl on time: 30\n";
			$wfh=fopen(PHONE_MODULES_PATH."aastra/aap9_6/\$mac.cfg",'rb');
			$contents = fread($wfh, filesize(PHONE_MODULES_PATH."aastra/aap9_6/\$mac.cfg"));
			fclose($wfh);
			
			$contents = str_replace("#xml scripts start",$script_data,$contents);
			
			$wfh=fopen(PHONE_MODULES_PATH."aastra/aap9_6/\$mac.cfg",'w');
			fwrite($wfh,$contents);
			fclose($wfh);
			
			$display = "<center>Modification Complete!</center>";
		}
		return($display);
	}
}
?>