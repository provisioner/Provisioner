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

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();
        //Polycom likes lower case letters in its mac address
        $this->mac = strtolower($this->mac);
    }

    function reboot() {
        if (($this->engine == "asterisk") AND ($this->system == "unix")) {
            exec($this->engine_location . " -rx 'sip notify polycom-check-cfg " . $this->settings['line'][0]['username'] . "'");
        }
    }

}
