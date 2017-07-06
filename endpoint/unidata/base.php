<?PHP
/**
 * Unidata Files
 *
 * @author Graeme Moss
 * @modified
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_unidata_base extends endpoint_base {

    public $brand_name = 'unidata';

    function prepare_for_generateconfig() {
        $this->mac = strtolower($this->mac);
        parent::prepare_for_generateconfig();
    }

    function reboot() {
        if (($this->engine == "asterisk") AND ($this->system == "unix")) {
			// notification type "cisco-check-cfg" has equal effect on the Thomson phones.
            if ($this->settings['line'][0]['tech'] == "pjsip") {
                exec($this->engine_location . " -rx 'pjsip send notify reboot-yealink endpoint " . $this->settings['line'][0]['username'] . "'");
            } else {
                exec($this->engine_location . " -rx 'sip notify reboot-yealink " . $this->settings['line'][0]['username'] . "'");
            }
        }
    }

}
