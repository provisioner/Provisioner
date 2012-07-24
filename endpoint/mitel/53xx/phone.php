<?php
/**
 * Polycom SoundPoint In Production Modules Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_mitel_53xx_phone extends endpoint_mitel_base {

    public $family_line = '5xxx';
    public $dynamic_mapping = array(
		'MN_$mac.cfg'=>array('MN_Generic.cfg','MN_$mac.cfg'),
		'MN_Generic.cfg'=>'#This File is intentionally left blank',
    );

}