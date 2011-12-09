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

    function config_files() {
        $result = parent::config_files();
		return $result;
	}
        
    function prepare_for_generateconfig() {
        $this->mac = strtolower($this->mac);
        parent::prepare_for_generateconfig();
    }

    function reboot() {
        if (($this->engine == "asterisk") AND ($this->system == "unix")) {
            exec($this->engine_location . " -rx 'sip notify polycom-check-cfg " . $this->settings['line'][0]['username'] . "'");
        }
    }

}