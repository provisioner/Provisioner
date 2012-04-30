<?PHP
/**
 * Thomson Base File
 *
 * @author Andrew Nagy
 * @modified by Thord Matre
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_thomson_base extends endpoint_base {

    public $brand_name = 'thomson';
    public $protected_files = 'general_custom.xml';

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();
        $this->mac = strtoupper($this->mac);
    }

    function reboot() {
        if (($this->engine == "asterisk") AND ($this->system == "unix")) {
			// notification type "cisco-check-cfg" has equal effect on the Thomson phones.
            exec($this->engine_location . " -rx 'sip notify reboot-yealink " . $this->settings['line'][0]['username'] . "'");
        }
    }

}
