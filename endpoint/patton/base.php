<?PHP

/**
 * Patton Base File
 *
 * @author Graeme Moss contact@dcdata.co.za
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
abstract class endpoint_patton_base extends endpoint_base {

    public $brand_name = 'patton';

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();
        //$this->mac = strtoupper($this->mac);
    }
}