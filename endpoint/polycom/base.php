<?PHP
/**
 * Polycom Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */

abstract class endpoint_polycom_base extends endpoint_base {
	
	public $brand_name = 'polycom';
	
	function reboot() {
		if(($this->engine == "asterisk") AND ($this->system == "unix")) {
			exec("asterisk -rx 'sip notify polycom-check-cfg ".$this->lines[1]['ext']."'");
		}
	}
}
