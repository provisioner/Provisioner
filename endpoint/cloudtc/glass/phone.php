<?php

/**
 * Yealink Modules Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cloudtc_glass_phone extends endpoint_cloudtc_base {

    public $family_line = 'glass';

    function prepare_for_generateconfig() {
        $this->mac = strtolower($this->mac);
		parent::prepare_for_generateconfig();
	}

}