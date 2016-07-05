<?php

/**
 * Grandstream GXP Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * 
 */
class endpoint_grandstream_gxpsb_phone extends endpoint_grandstream_base {

    public $family_line = 'gxpsb';

    function parse_lines_hook($line_data, $line_total) {
        $line_data['line_active'] = (isset($line_data['secret']) ? '1' : '0');
        return($line_data);
    }

    function get_gmtoffset($timezone) {
        $timezone = str_replace(":", ".", $timezone);
        $timezone = str_replace("30", "5", $timezone);
        if (strrchr($timezone, '+')) {
            $num = explode("+", $timezone);
            $num = $num[1];
            $offset = 720 + ($num * 60);
        } elseif (strrchr($timezone, '-')) {
            $num = explode("-", $timezone);
            $num = $num[1];
            $offset = 720 + ($num * -60);
        }
        return($offset);
    }

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();

        if (isset($this->settings['dialplan'])) {
            $this->settings['dialplan'] = str_replace("+", "%2B", $this->settings['dialplan']);
        }

    }

    function generate_file($file, $extradata, $ignoredynamicmapping=FALSE, $prepare=FALSE) {
        $data = parent::generate_file($file, $extradata, TRUE);
        return $data;
    }

    function reboot($device_ip = "") {
        if (($this->engine == "asterisk") AND ($this->system == "unix")) {
            if(!$device_ip){
                exec($this->engine_location . " -rx 'sip show peers like " . $this->settings['line'][0]['username'] . "'", $output);
                preg_match("/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/", $output[1], $matches);
                $device_ip = $matches[0];
            }
            
            $pass = (isset($this->options['admin_pass']) ? $this->options['admin_pass'] : 'admin');

            if (function_exists('curl_init')) {
                $ckfile = tempnam($this->sys_get_temp_dir(), "GSCURLCOOKIE");
                $ch = curl_init('http://' . $device_ip . '/cgi-bin/dologin');
                curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);

                /*$data = array(
                    'P2' => $pass,
                    'Login' => 'Login',
                    'gnkey' => '0b82'
                );*/
                $data_login = array(
                    'password' => $pass
                );

                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_login);
                $response_login_json = curl_exec($ch);
                $info_login = curl_getinfo($ch);
                curl_close($ch);
                
                /*print_r($response_login_json);
                print_r($info_login);*/
                
                $response_login = json_decode($response_login_json);
                $login_status = $response_login->response;                
                if($login_status == "success"){
                    $sid = $response_login->body->sid;
                    
                    //$ch = curl_init("http://" . $device_ip . "/cgi-bin/rs");
                    $ch = curl_init("http://" . $device_ip . "/cgi-bin/api-sys_operation");
                    curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $data = array(
                        'request' => 'REBOOT',
                        'sid' => $sid
                    );
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    $response_reboot_json = curl_exec($ch);
                    $info_reboot = curl_getinfo($ch);                    
                    curl_close($ch);
                    
                    $response_reboot = json_decode($response_reboot_json);
                    $reboot_status = $response_reboot->response;                    
                    
                    $answer = $info_reboot;                    
                    $answer["status"] = $reboot_status;
                    if($reboot_status == "success"){      
                        // si fue exitosa la petición, no se utiliza el mensaje
                        $answer["message"] = "";
                    }
                    else{
                        $answer["message"] = $response_reboot_json;
                    }
                }
                else{
                    $answer = $info_login;
                    $answer["status"] = $login_status;
                    $answer["message"] = $response_login_json;
                }
            }
        }
        else{            
            $answer["status"] = "error";
            $answer["url"] = "";
            $answer["http_code"] = "199";
            $answer["message"] = JText::_("REBOOT.MESSAGE.VOIP_ENGINE_OR_SYSTEM_NOT_SUPPORTED");
        }
        
        return $answer;
    }

}
