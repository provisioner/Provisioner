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
	public $protected_files='general_custom.xml';

	function prepare_for_generateconfig() {
		parent::prepare_for_generateconfig();
		$this->mac = strtoupper($this->mac);
	}
	
	function reboot() {
		if(($this->engine == "asterisk") AND ($this->system == "unix")) {
			exec($this->engine_location." -rx 'sip notify reboot-snom ".$this->lines[1]['ext']."'");
		}
	}
}
