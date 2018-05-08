<?php

/**
 * Yealink Modules Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_yealinkv80_t2x_phone extends endpoint_yealinkv80_base {

    public $family_line = 't2x';

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



        if (isset($line_data['transport'])) {
            switch ($line_data['transport']) {
                case "UDP":
                    $line_data['transport'] = 0;
                    break;
                case "TCP":
                    $line_data['transport'] = 1;
                    break;
                case "TLS":
                    $line_data['transport'] = 2;
                    break;
                case "DNSSRV":
                    $line_data['transport'] = 3;
                    break;
                default:
                    $line_data['transport'] = 0;
                    break;
            }
        } else {
            $line_data['transport'] = 0;
        }

        return($line_data);
    }

    function prepare_for_generateconfig() {
        # This contains the last 2 digits of y0000000000xx.cfg, for each model.
        $model_suffixes = array('T28' => '00', 'T26' => '04', 'T22' => '05', 'T21' => '34', 'T20' => '07', 'T19' => '31');
        //Yealink likes lower case letters in its mac address
        $this->mac = strtolower($this->mac);
        $this->config_file_replacements['$suffix'] = $model_suffixes[$this->model];
        parent::prepare_for_generateconfig();

		//Setup password if not set
		if (!isset($this->settings['adminpw']) OR empty($this->settings['adminpw'])) {
		//	$this->settings['adminpw'] = substr(strrev(md5(filemtime(__FILE__).date("j"))),0,8);
		$this->settings['adminpw'] = 'admin';
		}

        //Set softkeys or defaults
        if (isset($this->settings['loops']['softkey'])) {
            foreach ($this->settings['loops']['softkey'] as $key => $data) {
                //HIstory, Dir, DND, and Menu
                if ($this->settings['loops']['softkey'][$key]['type'] == '0') {
                    unset($this->settings['loops']['softkey'][$key]);
                }
            }
        } else {
            $this->settings['loops']['softkey'][1]['type'] = 28;
            //$this->settings['loops']['softkey'][1]['label'] = "Journal";
            $this->settings['loops']['softkey'][2]['type'] = 61;
            //$this->settings['loops']['softkey'][2]['label'] = "Annuaire";
            $this->settings['loops']['softkey'][3]['type'] = 5;
            //$this->settings['loops']['softkey'][3]['label'] = "DND";
            $this->settings['loops']['softkey'][4]['type'] = 30;
            //$this->settings['loops']['softkey'][4]['label'] = "Menu";
        }

        if (isset($this->settings['loops']['remotephonebook'])) {
            foreach ($this->settings['loops']['remotephonebook'] as $key => $data) {
                if ($this->settings['loops']['remotephonebook'][$key]['url'] == '') {
                    unset($this->settings['loops']['remotephonebook'][$key]);
                }
            }
        }
        
		
        //Set line key defaults
		if (isset($this->settings['loops']['linekey'])) {
            foreach ($this->settings['loops']['linekey'] as $key => $data) {
			if ($this->settings['loops']['linekey'][$key]['type'] == '0') {
                unset($this->settings['loops']['linekey'][$key]);
			} elseif (($key >= 1) && ($key <= 6)) {
                   $this->settings['loops']['linekey'][$key] = $this->settings['loops']['linekey'][$key];
                }               	
            }
        }
		
        $s = $this->max_lines;
        for ($i = 1; $i <= $s; $i++) {
            if (!isset($this->settings['loops']['linekey'][$i])) {
                $this->settings['loops']['linekey'][$i] = array(
                    "mode" => "blf",
                    "type" => 15,
					"line" => 1
                );
            } elseif($this->settings['loops']['linekey'][$i]['type'] == '16') {
                $this->settings['loops']['linekey'][$i]['pickup_value'] = $this->settings['call_pickup'];
                $this->settings['loops']['linekey'][$i]['line'] = $this->settings['loops']['linekey'][$i]['line'] != '0' ? $this->settings['loops']['linekey'][$i]['line'] - 1 : $this->settings['loops']['linekey'][$i]['line'];
            }
        }

        if (isset($this->settings['loops']['sdext38'])) {
            foreach ($this->settings['loops']['sdext38'] as $key => $data) {
                if ($this->settings['loops']['sdext38'][$key]['type'] == '16') {
                    $this->settings['loops']['sdext38'][$key]['pickup_value'] = $this->settings['call_pickup'];
                } elseif ($this->settings['loops']['sdext38'][$key]['type'] == '0') {
                    unset($this->settings['loops']['sdext38'][$key]);
                } else {
                    $this->settings['loops']['sdext38'][$key]['pickup_value'] = '**';
                }
            }
        }

        if (isset($this->settings['loops']['memkey'])) {
            foreach ($this->settings['loops']['memkey'] as $key => $data) {
                if ($this->settings['loops']['memkey'][$key]['type'] == '16') {
                    $this->settings['loops']['memkey'][$key]['pickup_value'] = $this->settings['call_pickup'];
                } elseif ($this->settings['loops']['memkey'][$key]['type'] == '0') {
                    unset($this->settings['loops']['memkey'][$key]);
                } else {
                    $this->settings['loops']['memkey'][$key]['pickup_value'] = '**';
                }
            }
        }

        if (isset($this->settings['loops']['memkey2'])) {
            foreach ($this->settings['loops']['memkey2'] as $key => $data) {
                if ($this->settings['loops']['memkey2'][$key]['type'] == '16') {
                    $this->settings['loops']['memkey2'][$key]['pickup_value'] = $this->settings['call_pickup'];
                } elseif ($this->settings['loops']['memkey2'][$key]['type'] == '0') {
                    unset($this->settings['loops']['memkey2'][$key]);
                } else {
                    $this->settings['loops']['memkey2'][$key]['pickup_value'] = '**';
                }
            }
        }
    }

}
