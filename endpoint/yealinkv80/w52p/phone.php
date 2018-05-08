<?php
/**
 * Yealink Modules Phone File
 *
 * @author Jcattan
 * @license MPL / GPLv2 / LGPL
 * @package Auruspbx
 */
class endpoint_yealinkv80_w52p_phone extends endpoint_yealinkv80_base {

    public $family_line = 'w52p';
	public $dynamic_mapping = array(
		'$mac.cfg'=>array('$mac.cfg','y0000000000$suffix.cfg'),
		'y0000000000$suffix.cfg'=>'#This File is intentionally left blank'
	);
   function parse_lines_hook($line_data, $line_total) {
        $line_data['line_active'] = 1;
        $line_data['line_m1'] = $line_data['line'];
        $line_data['enable_stun'] = 0;
        $line_data['voicemail_number'] = '*97';
        $line_data['custom_ringtone'] = isset($this->settings['custom_ringtone']) ? $this->settings['custom_ringtone'] : 'Ring1.wav';
        $line_data['sip_server_override'] = isset($this->settings['sip_server_override']) ? $this->settings['sip_server_override'] : '{$server_host}';
        $line_data['manual_use_outbound_proxy'] = isset($this->settings['manual_use_outbound_proxy']) ? $this->settings['manual_use_outbound_proxy'] : 0;
        $line_data['manual_outbound_proxy_server'] = isset($this->settings['manual_outbound_proxy_server']) ? $this->settings['manual_outbound_proxy_server'] : '{$server_host}';
        $line_data['manual_outbound_port'] = isset($this->settings['manual_outbound_port']) ? $this->settings['manual_outbound_port'] : '{$server_port}';
    
		$line_data['outbound_proxy_host'] = isset($line_data['outbound_proxy_host']) ? $line_data['outbound_proxy_host'] : $line_data['server_host'];
        $line_data['outbound_proxy_port'] = isset($line_data['outbound_proxy_port']) ? $line_data['outbound_proxy_port'] : $line_data['server_port'];


        return($line_data);
    }

	function prepare_for_generateconfig() {
		# This contains the last 2 digits of y0000000000xx.cfg, for each model.
		$model_suffixes=array('W52P'=>'25');
		//Yealink likes lower case letters in its mac address
         $this->mac = strtolower($this->mac);
        $this->config_file_replacements['$suffix'] = $model_suffixes[$this->model];
        parent::prepare_for_generateconfig();

		//Setup password if not set
		if (!isset($this->settings['adminpw']) OR empty($this->settings['adminpw'])) {
			$this->settings['adminpw'] = substr(strrev(md5(filemtime(__FILE__).date("j"))),0,8);
		}

         
        //Set line key defaults
        $s = $this->max_lines;
        for ($i = 1; $i <= $s; $i++) {
            if (!isset($this->settings['loops']['linekey'][$i])) {
                $this->settings['loops']['linekey'][$i] = array(
                    "mode" => "blf",
                    "type" => 15,
                    "line" => 0,
		    
            );
            } elseif($this->settings['loops']['linekey'][$i]['type'] == '16') {
                $this->settings['loops']['linekey'][$i]['line'] = $this->settings['loops']['linekey'][$i]['line'] != '0' ? $this->settings['loops']['linekey'][$i]['line'] : $this->settings['loops']['linekey'][$i]['line'];
		$this->settings['loops']['linekey'][$i]['pickup_value'] = $this->settings['call_pickup'];
            }
        }


    }

}
?>