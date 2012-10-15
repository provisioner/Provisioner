<?php

/**
 * Cisco SPA Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_spa5xx_phone extends endpoint_cisco_base {

    public $family_line = 'spa5xx';
	protected $use_system_dst = FALSE;

    function parse_lines_hook($line_data,$line_total) {
        $line = $line_data['line'];

        $line_data['displayname'] = isset($line_data['displayname']) ? $line_data['displayname'] : '';

        $short_name = (strlen($line_data['displayname']) > 12) ? substr($line_data['displayname'], 0, 8) . "..." : $line_data['displayname'];

        $line_data['use_dns_srv'] = (isset($line_data['transport']) && ($line_data['transport'] == "DNSSRV")) ? 'Yes' : 'No';
        
        $line_data['dial_plan'] = ((isset($line_data['secret'])) && ($line_data['secret'] != "") && (isset($this->settings['dial_plan']))) ? htmlentities($this->settings['dial_plan']) : "";

        if (isset($this->settings['loops']['lineops'][$line])) {

            $line_data['displaynameline'] = isset($this->settings['loops']['lineops'][$line]['displaynameline']) ? str_replace('{$count}', $line_data['line'], $this->settings['loops']['lineops'][$line]['displaynameline']) : '';
            $short_name = $line_data['displaynameline'];

            $line_data['short_name'] = str_replace('{$count}', $line_data['line'], $short_name);

            if (($this->settings['loops']['lineops'][$line]['keytype'] == "blf") AND ($this->settings['loops']['lineops'][$line]['blfext'] != "")) {
                $line_data['username'] = $this->settings['loops']['lineops'][$line]['blfext'];
                $line_data['secret'] = 'n/a';
                $line_data['blf_ext_type'] = "Disabled";
                $line_data['share_call_appearance'] = "shared";
                $line_data['extended_function'] = "fnc=blf+sd;sub=";
                $line_data['extended_function'] .= preg_match('/@/i', $this->settings['loops']['lineops'][$line]['blfext']) ? $this->settings['loops']['lineops'][$line]['blfext'] : $this->settings['loops']['lineops'][$line]['blfext'] . "@" . $this->settings['line'][0]['server_host'];
            } elseif (($this->settings['loops']['lineops'][$line]['keytype'] == "xml") AND ($this->settings['loops']['lineops'][$line]['blfext'] != "")) {
                $line_data['username'] = $this->settings['loops']['lineops'][$line]['blfext'];
                $line_data['secret'] = 'n/a';
                $line_data['blf_ext_type'] = "Disabled";
                $line_data['share_call_appearance'] = "shared";
                $line_data['extended_function'] = "fnc=xml;url=" . $this->settings['loops']['lineops'][$line]['blfext'];
            } elseif (($this->settings['loops']['lineops'][$line]['keytype'] == "sd") AND ($this->settings['loops']['lineops'][$line]['blfext'] != "")) {
                $line_data['username'] = $this->settings['loops']['lineops'][$line]['blfext'];
                $line_data['secret'] = 'n/a';
                $line_data['blf_ext_type'] = "Disabled";
                $line_data['share_call_appearance'] = "shared";
                $line_data['extended_function'] = "fnc=sd;sub=";
                $line_data['extended_function'] .= preg_match('/@/i', $this->settings['loops']['lineops'][$line]['blfext']) ? $this->settings['loops']['lineops'][$line]['blfext'] : $this->settings['loops']['lineops'][$line]['blfext'] . "@" . $this->settings['line'][0]['server_host'];
            } elseif ($this->settings['loops']['lineops'][$line]['keytype'] == "disabled") {
                $line_data['blf_ext_type'] = "Disabled";
            } elseif ($this->settings['loops']['lineops'][$line]['keytype'] == "clone") {
                $line_data['blf_ext_type'] = $this->settings['loops']['lineops'][$line]['clonedline'];
            } else {
                if (!isset($line_data['secret'])) {
                    $line_data['displaynameline'] = $this->lines[1]['options']['displaynameline'];
                    $line_data['short_name'] = $this->lines[1]['options']['short_name'];
                    $line_data['username'] = '';
                    $line_data['secret'] = '';
                    $line_data['blf_ext_type'] = "1";
                    $line_data['share_call_appearance'] = "private";
                    $line_data['extended_function'] = "";
                } else {
                    $line_data['blf_ext_type'] = $line;
                    $line_data['share_call_appearance'] = "private";
                    $line_data['extended_function'] = "";
                }
            }
        } else {
            $line_data['displaynameline'] = $line_data['displayname'];
            $line_data['short_name'] = $short_name;
            $line_data['blf_ext_type'] = "1";
            $line_data['share_call_appearance'] = "private";
            $line_data['extended_function'] = "";
        }
        return($line_data);
    }

	function generate_file($filename, $extradata, $ignoredynamicmapping=FALSE, $prepare=FALSE) {
		$data = parent::generate_file($filename, $extradata, $ignoredynamicmapping, $prepare);
		if((isset($this->settings['compress_config'])) && ($extradata == 'spa$mac.xml')) {
			$data = gzencode($data);
		}
		return($data);
	}

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();

        if ($this->settings['network']['connection_type'] == 'STATIC') {
            $this->settings['current_ip'] = $this->settings['network']['ipv4'];
            $this->settings['current_netmask'] = $this->settings['network']['subnet'];
            $this->settings['current_gateway'] = $this->settings['network']['gateway'];
            $this->settings['primary_dns'] = $this->settings['network']['primary_dns'];
            $this->settings['connection_type'] = 'Static IP';
        } else {
            $this->settings['connection_type'] = 'DHCP';
        }
        
        if(isset($this->settings['timeformat'])) {
            $this->settings['timeformat'] = $this->settings['timeformat'] == '12hour' ? '12hr' : '24hr';
        } else {
            $this->settings['timeformat'] = '12hr';
        }
        
        if(isset($this->settings['dateformat'])) {
            switch($this->settings['dateformat']) {
                case "little-endian":
                    $this->settings['dateformat'] = 'day/month';
                    break;
                default;
                    $this->settings['dateformat'] = 'month/day';
                    break;
            }
        } else {
            $this->settings['dateformat'] = 'month/day';
        }

        for ($i = 1; $i <= $this->max_lines; $i++) {
            if ((isset($this->settings['loops']['lineops'])) && ($this->settings['loops']['lineops'][$i]['keytype'] != 'line')) {
                if (!isset($this->settings['line'][$i]['line'])) {
                    $this->settings['line'][$i]['line'] = $i;
                }
            }
        }

        if (isset($this->settings['loops']['unit1'])) {
            foreach ($this->settings['loops']['unit1'] as $key => $data) {
                if ((isset($this->settings['loops']['unit1'][$key]['data'])) AND ($this->settings['loops']['unit1'][$key]['data'] != '')) {
                    if ($this->settings['loops']['unit1'][$key]['keytype'] == 'blf') {
                        $temp_ext = $this->settings['loops']['unit1'][$key]['data'];
                        $this->settings['loops']['unit1'][$key]['data'] = "fnc=blf+sd;sub=";
                        $this->settings['loops']['unit1'][$key]['data'] .= preg_match('/;.*=.*@.*$/i', $temp_ext) ? $temp_ext : $temp_ext . "@" . $this->settings['line'][0]['server_host'];
                    }
                    if ($this->settings['loops']['unit1'][$key]['keytype'] == 'speed') {
                        $temp_ext = $this->settings['loops']['unit1'][$key]['data'];
                        $this->settings['loops']['unit1'][$key]['data'] = "fnc=sd;sub=";
                        $this->settings['loops']['unit1'][$key]['data'] .= preg_match('/;.*=.*@.*$/i', $temp_ext) ? $temp_ext : $temp_ext . "@" . $this->settings['line'][0]['server_host'];
                    }
                    if ($this->settings['loops']['unit1'][$key]['keytype'] == 'xml') {
                        $temp_ext = $this->settings['loops']['unit1'][$key]['data'];
                        $this->settings['loops']['unit1'][$key]['data'] = "fnc=xml;url=" . $temp_ext;
                    }
                }
            }
        }

        if (isset($this->settings['loops']['unit2'])) {
            foreach ($this->settings['loops']['unit2'] as $key => $data) {
                if ((isset($this->settings['loops']['unit2'][$key]['data'])) AND ($this->settings['loops']['unit2'][$key]['data'] != '')) {
                    if ($this->settings['loops']['unit2'][$key]['keytype'] == 'blf') {
                        $temp_ext = $this->settings['loops']['unit2'][$key]['data'];
                        $this->settings['loops']['unit2'][$key]['data'] = "fnc=blf+sd;sub=";
                        $this->settings['loops']['unit2'][$key]['data'] .= preg_match('/;.*=.*@.*$/i', $temp_ext) ? $temp_ext : $temp_ext . "@" . $this->settings['line'][0]['server_host'];
                    }
                    if ($this->settings['loops']['unit2'][$key]['keytype'] == 'speed') {
                        $temp_ext = $this->settings['loops']['unit2'][$key]['data'];
                        $this->settings['loops']['unit2'][$key]['data'] = "fnc=sd;sub=";
                        $this->settings['loops']['unit2'][$key]['data'] .= preg_match('/;.*=.*@.*$/i', $temp_ext) ? $temp_ext : $temp_ext . "@" . $this->settings['line'][0]['server_host'];
                    }
                    if ($this->settings['loops']['unit2'][$key]['keytype'] == 'xml') {
                        $temp_ext = $this->settings['loops']['unit2'][$key]['data'];
                        $this->settings['loops']['unit2'][$key]['data'] = "fnc=xml;url=" . $temp_ext;
                    }
                }
            }
        }
    }

}
