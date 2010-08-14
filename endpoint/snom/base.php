<?PHP
/**
 * Grandstream Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_snom_base extends endpoint_base {
	public $brand_name = 'snom';
	
	function reboot() {
		if(($this->engine == "asterisk") AND ($this->system == "unix")) {
			exec("asterisk -rx 'sip notify reboot-snom ".$this->lines[1]['ext']."'");
		}
	}
}