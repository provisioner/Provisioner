<?PHP

/**
 * Grandstream Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_grandstream_base extends endpoint_base {

    public $brand_name = 'grandstream';

    function reboot() {
        if (($this->engine == "asterisk") AND ($this->system == "unix")) {
            exec($this->engine_location . " -rx 'sip show peers like " . $this->settings['line'][0]['username'] . "'", $output);
            if (preg_match("/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/", $output[1], $matches)) {
                $ip = $matches[0];
                $pass = (isset($this->settings['admin_pass']) ? $this->settings['admin_pass'] : 'admin');

                if (function_exists('curl_init')) {
                    $ckfile = tempnam($this->sys_get_temp_dir(), "GSCURLCOOKIE");
                    $ch = curl_init('http://' . $ip . '/dologin.htm');
                    curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);

                    $data = array(
                        'P2' => $pass,
                        'Login' => 'Login',
                        'gnkey' => '0b82'
                    );

                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    $output = curl_exec($ch);
                    $info = curl_getinfo($ch);
                    curl_close($ch);

                    $ch = curl_init("http://" . $ip . "/rs.htm");
                    curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $output = curl_exec($ch);
                    curl_close($ch);
                }
            }
        }
    }

    function create_encrypted_file($list) {
        $temporary_directory_location = function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : '/tmp';

        foreach ($list as $key => $data) {
            file_put_contents($temporary_directory_location . "/" . $key, $data);

            if (file_exists("/usr/src/GS_CFG_GEN/bin/encode.sh")) {
                exec("/usr/src/GS_CFG_GEN/bin/encode.sh " . $this->mac . " " . $temporary_directory_location . "/" . $this->mac . ".cfg " . $temporary_directory_location . "/cfg" . $this->mac);
                $contents = file_get_contents($temporary_directory_location . "/cfg" . $this->mac);
                unlink($temporary_directory_location . "/cfg" . $this->mac);
            } else {
                $params = $this->parse_gs_config($temporary_directory_location . "/" . $key);
                $contents = $this->gs_config_out($this->mac, $params);
            }

            $files["cfg" . $this->mac] = $contents;
            unlink($temporary_directory_location . "/" . $key);
        }
        return($files);
    }

    function prepare_for_generateconfig() {
        //Grandstream likes lower case letters in its mac address
        $this->mac = strtolower($this->mac);
        parent::prepare_for_generateconfig();
        $this->options['gs_timezone'] = 720 + $this->timezone['gmtoffset'] / 60;
    }

    function generate_file($file, $extradata, $ignoredynamicmapping=FALSE, $prepare=FALSE) {
        $data = parent::generate_file($file, $extradata, $ignoredynamicmapping);
        if ($ignoredynamicmapping == FALSE) {
            $data = array_values($this->create_encrypted_file(array($this->mac . ".cfg" => $data)));
            $data = $data[0];
        }
        return $data;
    }

    //Functions below were fixed by Schmoozecom.
    function parse_gs_config($filename) {
        if (!($f = @fopen($filename, "r"))) {
            echo ("Unable to open " . $filename . "\n");
            return FALSE;
        }
        while ($str = fgets($f)) {
            $url_encode = false;
    	if (($pos = strpos($str, "#")) !== FALSE) {
                $str = substr($str, 0, $pos);
            }
    	if (($pos = strpos($str, '+')) !== FALSE) {
    		$url_encode = true;
    	}
            if (strlen($str)) {
                if (preg_match("/(.+)=(.*)/", $str, $matches)) {
                    if ($url_encode === true) {
    			$params[trim($matches[1])] = urlencode(trim($matches[2]));
    		} else {
    			$params[trim($matches[1])] = trim($matches[2]);
    		}
                }
            }
        }
        fclose($f);
        return $params;
    }

    // MAC : 12 hex digits string
    // $params : array ("P01" => "something", ...)
    function gs_config_out($mac, $params) {
        $prev = 0;
        //if (!preg_match ("/^[0-9a-fA-F]{12}$/", $mac))
        //	return FALSE;
        $params["gnkey"] = "0b82";
        $str = "";
        foreach ($params as $key => $val) {
            if ($prev)
                $str .= "&";
            else
                $prev = 1;
            $str .= $key . "=" . $val;
        }
        if (strlen($str) & 1)
            $str .= chr(0);
        // Insert the beginning
        $new_str = chr(0) . chr(0) . chr((16 + strlen($str)) / 2 >> 8 & 0xff) . chr((16 + strlen($str)) / 2 & 0xff) . chr(0) . chr(0);
        // Insert the MAC address
        for ($i = 0; $i < 6; $i++) {
            $new_str .= chr(hexdec(substr($mac, $i * 2, 2)));
        }
        // Insert the end of the first line
        $new_str .= chr(13) . chr(10) . chr(13) . chr(10) . $str;
        // Basic checksum
        $k = 0;
        for ($i = 0; $i < strlen($new_str) / 2; $i++) {
            $k += ord($new_str[$i * 2]) << 8 & 0xff00;
            $k += ord($new_str[$i * 2 + 1]) & 0xff;
            $k &= 0xffff;
        }
        $k = 0x10000 - $k;
        $new_str[4] = chr($k >> 8 & 0xff);
        $new_str[5] = chr($k & 0xff);
        return $new_str;
    }

}
