<?php
/**
 * Snom 300, 320, 360, 370 Provisioning System
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_snom_Cxxx_phone extends endpoint_snom_base {

    public $family_line = 'Cxxx';

    function prepare_for_generateconfig() {
	parent::prepare_for_generateconfig();
	if (isset($this->DateTimeZone)) $this->settings['time_zone_name'] = $this->DateTimeZone->getName();
    }
}
