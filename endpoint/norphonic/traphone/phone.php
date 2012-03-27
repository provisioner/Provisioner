<?php
/**
 * Norphonic Traphone Phone File
 **
 * @author Thord Matre
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_norphonic_traphone_phone extends endpoint_norphonic_base {
	public $family_line = 'traphone';

	function prepare_for_generateconfig() {
		parent::prepare_for_generateconfig();
		if (isset($this->lines[1]['secret'])) {
			$this->settings['srv_auth']="1";
		} else {
			$this->settings['srv_auth']="0";
			
		}
	}
}
