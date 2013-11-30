<?php
/**
 * Unidata WPU-7800 Provisioning System
 *
 * @author Graeme Moss
 * @modified
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_unidata_WPUxxxx_phone extends endpoint_unidata_base {

	public $family_line = 'WPUxxxx';
    function prepare_for_generateconfig() {
        $this->mac = strtolower($this->mac);
        parent::prepare_for_generateconfig();
    }

}
