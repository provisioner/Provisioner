<?PHP
/**
 * Cisco Base File
 *
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_base extends endpoint_base {
	
	public $brand_name = 'cisco';
	
	function reboot() {
		if(($this->engine == "asterisk") AND ($this->system == "unix")) {
			if($this->family_line == "sip79xx") {
				exec("asterisk -rx 'sip notify cisco-check-cfg ".$this->lines[1]['ext']."'");
			} elseif($this->family_line == "spa") {
				exec("asterisk -rx 'sip notify spa-reboot ".$this->lines[1]['ext']."'");				
			} elseif($this->family_line == "spa5xx") {
				exec("asterisk -rx 'sip notify spa-reboot ".$this->lines[1]['ext']."'");
			}
		}
	}
}