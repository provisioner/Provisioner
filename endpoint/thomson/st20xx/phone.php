<?php
/**
 * Thomson ST2030S Provisioning System
 *
 * @author Andrew Nagy & Jort
 * @modified Thord Matre
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_thomson_st20xx_phone extends endpoint_thomson_base {

	public $family_line = 'st20xx';
    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();
        $this->mac = strtoupper($this->mac);
		/* Be sure the serial number is increased using epoch time. */
        $this->settings['config_sn']=str_pad(time(), 12, "0", STR_PAD_LEFT);
    }

}