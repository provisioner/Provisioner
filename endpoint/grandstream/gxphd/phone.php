<?php
/**
 * Grandstream GXP Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_grandstream_gxphd_phone extends endpoint_grandstream_base {

	public $family_line = 'gxphd';

	function get_gmtoffset($timezone) {
		$timezone = str_replace(":", ".", $timezone);
                $timezone = str_replace("30", "5", $timezone);
                if(strrchr($timezone,'+')) {
            		$num = explode("+",$timezone);
                        $num = $num[1];
                        $offset = 720 + ($num * 60);
                } elseif(strrchr($timezone,'-')) {
                        $num = explode("-",$timezone);
                        $num = $num[1];
                        $offset = 720 + ($num * -60);
                }
                return($offset);
        }

        function reboot() {
                if(($this->engine == "asterisk") AND ($this->system == "unix")) {
                        if(!isset($this->engine_location)) {
                                $output = shell_exec("asterisk -rx 'sip show peers like ".$this->lines[1]['ext']."'");
                        } else {
                                $output = shell_exec($this->engine_location. " -rx 'sip show peers like ".$this->lines[1]['ext']."'");
                        }
                        if(preg_match("/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/",$output,$matches)) {
                                $ip = $matches[0];
                                $pass = (isset($this->options['admin_pass']) ? $this->options['admin_pass'] : 'admin');
                                //This is lame. I need to do this in php not over the command line. etc, I AM THE LAME.
                                exec('curl -c cookies.txt -d"P2='.$pass.'&Login=Login&gnkey=0b82" http://'.$ip.'/cgi-bin/dologin');
                                exec("curl -b cookies.txt http://".$ip."/cgi-bin/rs");
                        }
                }
        }

	function generate_config() {		
		if($this->model == 'GXP2110') {
		    $this->options['comment_out'] = '#';
		} else {
		    $this->options['comment_out'] = '';
		} 
		//Grandstream likes lower case letters in its mac address
		$this->mac = strtolower($this->mac);

                // Grandstreams support lines 2-6, so let's add them if they're set
                for ($i = 1; $i < 6; $i++) {
                    $this->lines[$i]['line_active'] = (isset($this->lines[$i]['secret']) ? '1' : '0');
                }

		$contents = $this->open_config_file("\$mac.cfg");
		
		
		switch(strtoupper($this->timezone)) {
			case "GMT-12:00":
				$this->timezone = "0";
				break;
			case "GMT-11:00":
				$this->timezone = "60";
				break;
			case "GMT-10:00":
				$this->timezone = "120";
				break;
			case "GMT-09:00":
				$this->timezone = "180";
				break;
			case "GMT-08:00":
				$this->timezone = "240";
				break;
			case "GMT-07:00":
				$this->timezone = "300";
				break;
			case "GMT-06:00":
				$this->timezone = "360";
				break;
			case "GMT-05:00":
				$this->timezone = "420";
				break;
			case "GMT-04:30":
				$this->timezone = "450";
				break;
			case "GMT-04:00":
				$this->timezone = "480";
				break;
			case "GMT-03:30":
				$this->timezone = "510";
				break;
			case "GMT-03:00":
				$this->timezone = "540";
				break;
			case "GMT-02:00":
				$this->timezone = "600";
				break;
			case "GMT-01:00":
				$this->timezone = "660";
				break;
			case "GMT":
				$this->timezone = "720";
				break;
			case "GMT+01:00":
				$this->timezone = "780";
				break;
			case "GMT+02:00":
				$this->timezone = "840";
				break;
			case "GMT+03:00":
				$this->timezone = "900";
				break;
			case "GMT+03:30":
				$this->timezone = "930";
				break;
			case "GMT+04:00":
				$this->timezone = "960";
				break;
			case "GMT+04:30":
				$this->timezone = "990";
				break;
			case "GMT+05:00":
				$this->timezone = "1020";
				break;
			case "GMT+05:30":
				$this->timezone = "1050";
				break;
			case "GMT+05:45":
				$this->timezone = "1065";
				break;
			case "GMT+06:00":
				$this->timezone = "1080";
				break;
			case "GMT+06:30":
				$this->timezone = "1110";
				break;
			case "GMT+07:00":
				$this->timezone = "1140";
				break;
			case "GMT+08:00":
				$this->timezone = "1200";
				break;
			case "GMT+09:00":
				$this->timezone = "1260";
				break;
			case "GMT+09:30":
				$this->timezone = "1290";
				break;
			case "GMT+10:00":
				$this->timezone = "1320";
				break;
			case "GMT+11:00":
				$this->timezone = "1380";
				break;
			case "GMT+12:00":
				$this->timezone = "1440";
				break;
			case "GMT+13:00":
				$this->timezone = "1500";
				break;
		}
		
		$final[$this->mac.".cfg"] = $this->parse_config_file($contents);
		
		$final = $this->create_encrypted_file($final);
		
		return($final);
	}
	
}
