<?PHP

/**
 * Cisco Base File
 *
 *
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
        parent::prepare_for_generateconfig();
        $this->config_file_replacements['$mac'] = strtolower($this->mac);
        $this->config_file_replacements['$model'] = str_replace('SPA', 'spa', strtoupper($this->model));
    }

    function reboot() {
        if (($this->engine == "asterisk") AND ($this->system == "unix")) {
            if ($this->family_line == "sip79xx") {
                exec($this->engine_location . " -rx 'sip notify cisco-check-cfg " . $this->settings['line'][0]['username'] . "'");
            } elseif ($this->family_line == "ata18x") {
                exec("/tftpboot/cfgfmt.linux -t/tftpboot/ptag.dat /tftpboot/ata" . strtolower($this->mac) . ".txt /tftpboot/ata" . strtolower($this->mac));
                exec($this->engine_location . " -rx 'sip notify cisco-check-cfg " . $this->settings['line'][0]['username'] . "'");
            } elseif ($this->family_line == "spa") {
                exec($this->engine_location . " -rx 'sip notify spa-reboot " . $this->settings['line'][0]['username'] . "'");
            } elseif ($this->family_line == "spa5xx") {
                exec($this->engine_location . " -rx 'sip notify spa-reboot " . $this->settings['line'][0]['username'] . "'");
            } elseif ($this->family_line == "linksysata") {
                exec($this->engine_location . " -rx 'sip notify spa-reboot " . $this->settings['line'][0]['username'] . "'");
            }
        }
    }

}
