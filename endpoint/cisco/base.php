<?PHP
/**
 * Cisco Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_base extends endpoint_base {

    public $brand_name = 'cisco';

    function prepare_for_generateconfig() {
        //spa likes lower case letters in its mac address
        $this->mac = strtoupper($this->mac);
		if ($this->family_line == 'sip79x1G' && isset($this->settings['image_name']) && ($this->settings['image_name']!='') && (strpos("loadInformation",$this->settings['image_name'])===FALSE))
			$this->settings['image_name']='<loadInformation>'.$this->settings['image_name'].'</loadInformation>';
        parent::prepare_for_generateconfig();
        $this->config_file_replacements['$mac'] = strtolower($this->mac);
        $this->config_file_replacements['$model'] = str_replace('SPA', 'spa', strtoupper($this->model));
    }

    function reboot() {
        if (($this->engine == "asterisk") && ($this->system == "unix")) {
            if ($this->family_line == "sip99xx") {
                if ($this->settings['line'][0]['tech'] == "pjsip") {
                    exec($this->engine_location . " -rx 'pjsip send notify cisco-restart endpoint " . $this->settings['line'][0]['username'] . "'");
                } else {
                    exec($this->engine_location . " -rx 'sip notify cisco-restart " . $this->settings['line'][0]['username'] . "'");
                }
	   
	    } elseif ($this->family_line == "sip79xx") {
                if ($this->settings['line'][0]['tech'] == "pjsip") {
                    exec($this->engine_location . " -rx 'pjsip send notify cisco-check-cfg endpoint " . $this->settings['line'][0]['username'] . "'");
                } else {
                    exec($this->engine_location . " -rx 'sip notify cisco-check-cfg " . $this->settings['line'][0]['username'] . "'");
                }
            } elseif ($this->family_line == "ata18x") {
                exec("/tftpboot/cfgfmt.linux -t/tftpboot/ptag.dat /tftpboot/ata" . strtolower($this->mac) . ".txt /tftpboot/ata" . strtolower($this->mac));
                if ($this->settings['line'][0]['tech'] == "pjsip") {
                    exec($this->engine_location . " -rx 'pjsip send notify cisco-check-cfg endpoint " . $this->settings['line'][0]['username'] . "'");
                } else {
                    exec($this->engine_location . " -rx 'sip notify cisco-check-cfg " . $this->settings['line'][0]['username'] . "'");
                }
            } elseif ($this->family_line == "spa") {
                if ($this->settings['line'][0]['tech'] == "pjsip") {
                    exec($this->engine_location . " -rx 'pjsip send notify spa-reboot endpoint " . $this->settings['line'][0]['username'] . "'");
                } else {
                    exec($this->engine_location . " -rx 'sip notify spa-reboot " . $this->settings['line'][0]['username'] . "'");
                }
            } elseif ($this->family_line == "spa5xx") {
                if ($this->settings['line'][0]['tech'] == "pjsip") {
                    exec($this->engine_location . " -rx 'pjsip send notify spa-reboot endpoint " . $this->settings['line'][0]['username'] . "'");
                } else {
                    exec($this->engine_location . " -rx 'sip notify spa-reboot " . $this->settings['line'][0]['username'] . "'");
                }
            } elseif ($this->family_line == "linksysata") {
                if ($this->settings['line'][0]['tech'] == "pjsip") {
                    exec($this->engine_location . " -rx 'pjsip send notify spa-reboot endpoint " . $this->settings['line'][0]['username'] . "'");
                } else {
                    exec($this->engine_location . " -rx 'sip notify spa-reboot " . $this->settings['line'][0]['username'] . "'");
                }
            }
        }
    }

}
