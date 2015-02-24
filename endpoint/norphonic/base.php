<?PHP
/**
 * Norphonic Base File
 *
 * @author Andrew Nagy
 * @modified for norphonic phones by Thord Matre
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_norphonic_base extends endpoint_base {
	
	public $brand_name = 'norphonic';
	
	function prepare_for_generateconfig() {
		//Traphone likes upper case letters in its mac address
		$this->mac = strtoupper($this->mac);
		parent::prepare_for_generateconfig();
		$this->config_file_replacements['$mac']=strtoupper($this->mac);
		$this->config_file_replacements['$model']=strtoupper($this->model);
	}
    function reboot() {
        if (($this->engine == "asterisk") AND ($this->system == "unix")) {
            if ($this->family_line == "traphone") {
                exec($this->engine_location . " -rx 'sip show peers like " . $this->settings['line'][0]['username'] . "'", $output);
                if (preg_match("/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/", $output[1], $matches)) {
                    $ip = $matches[0];
                    //This is lame. I need to do this in php not over the command line. etc, I AM THE LAME.
                    exec("curl -u phoneadmin:blank http://" . $ip . "/cgi-bin/syscmd.cgi?sys_restart=Restart+phone+software");
                }
            }
        }
    }

}
