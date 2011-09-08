<?php
/**
 * Grandstream GXP Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_grandstream_gxp_phone extends endpoint_grandstream_base {

	public $family_line = 'gxp';

	function prepare_for_generateconfig() {
		parent::prepare_for_generateconfig();
                // Grandstreams support lines 2-6, so let's add them if they're set
                for ($i = 1; $i < 6; $i++) {
                    $this->lines[$i]['options']['line_active'] = (isset($this->lines[$i]['secret']) ? '1' : '0');
                }
	}
	
}
