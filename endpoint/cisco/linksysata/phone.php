<?php
/**
 * Cisco SPA Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_linksysata_phone extends endpoint_cisco_base {
	
	public $family_line = 'linksysata';
	
    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();
		//spa likes lower case letters in its mac address
		$this->mac = strtolower($this->mac);
	}
}