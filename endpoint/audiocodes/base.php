<?PHP
/**
 * AudioCodes Base File
 *
 * @author Andrew Nagy
 * @modified by iKono
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_audiocodes_base extends endpoint_base {

    public $brand_name = 'audiocodes';

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();
        $this->mac = strtoupper($this->mac);
    }

}
