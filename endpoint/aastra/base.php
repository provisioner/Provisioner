<?PHP
/**
 * Aastra Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_aastra_base extends endpoint_base {
	
	public $brand_name = 'aastra';

	function reboot() {
		if(($this->engine == "asterisk") AND ($this->system == "unix")) {
			exec("asterisk -rx 'sip notify aastra-check-cfg ".$this->lines[1]['ext']."'");
		}
	}
	
}