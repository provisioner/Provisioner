<?php
/**
 * AudioCodes MP1xx Provisioning System
 *
 * @author Andrew Nagy & Jort
 * @modified iKono
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_audiocodes_mp_phone extends endpoint_audiocodes_base {

    public $family_line = 'mp1xx';
    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();
        $this->mac = strtoupper($this->mac);
        /* Be sure the serial number is increased using epoch time. */
        $this->settings['config_sn']=str_pad(time(), 12, "0", STR_PAD_LEFT);
    }

}


