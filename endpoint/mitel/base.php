<?PHP
/**
 * Yealink Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
abstract class endpoint_mitel_base extends endpoint_base {
	
    public $brand_name = 'mitel';

    function prepare_for_generateconfig() {
        //Mitel likes lower case letters in its mac address
        $this->mac = strtoupper($this->mac);
	$this->options['model'] = $this->model;
	parent::prepare_for_generateconfig();
    }
}