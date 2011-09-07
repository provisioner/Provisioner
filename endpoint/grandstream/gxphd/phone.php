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

	function prepare_for_generateconfig() {
		parent::prepare_for_generateconfig();
		if($this->model == 'GXP2110') {
		    $this->options['comment_out'] = '#';
		} else {
		    $this->options['comment_out'] = '';
		} 
                // Grandstreams support lines 2-6, so let's add them if they're set
                for ($i = 1; $i < 6; $i++) {
                    $this->lines[$i]['line_active'] = (isset($this->lines[$i]['secret']) ? '1' : '0');
                }
	}
		
}
