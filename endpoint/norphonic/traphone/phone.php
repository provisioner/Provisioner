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

	function parse_lines_hook($line_data, $line_total) {

		if(isset($line_data['secret'])){
			$line_data['srv_auth']="1";
		} else {
			$line_data['srv_auth']="0";
		}
		
		return($line_data);
	}

	function prepare_for_generateconfig() {
		parent::prepare_for_generateconfig();
	}
}
