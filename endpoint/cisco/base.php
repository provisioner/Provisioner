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
				exec($this->engine_location." -rx 'sip notify cisco-check-cfg ".$this->lines[1]['ext']."'");
            } elseif($this->family_line == "ata18x") {
				exec("/tftpboot/cfgfmt.linux -t/tftpboot/ptag.dat /tftpboot/ata".strtolower($this->mac).".txt /tftpboot/ata".strtolower($this->mac));
                exec($this->engine_location." -rx 'sip notify cisco-check-cfg ".$this->lines[1]['ext']."'");
			} elseif($this->family_line == "spa") {
				exec($this->engine_location." -rx 'sip notify spa-reboot ".$this->lines[1]['ext']."'");				
			} elseif($this->family_line == "spa5xx") {
				exec($this->engine_location." -rx 'sip notify spa-reboot ".$this->lines[1]['ext']."'");
			}
		}
	}
}
