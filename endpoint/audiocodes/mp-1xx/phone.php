<?php
/**
 * Audicodes MP-1XX Provisioning System
 *
 * @author Graeme Moss
 * @modified
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_audiocodes_mp1xx_phone extends endpoint_unidata_base {

	public $family_line = 'mp-1xx';
    function prepare_for_generateconfig() {
        $this->mac = strtolower($this->mac);
        parent::prepare_for_generateconfig();
    }

}
