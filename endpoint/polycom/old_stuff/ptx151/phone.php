<?php
/**
 * Phone Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 **/
class endpoint_polycom_ptx151_phone extends endpoint_polycom_base {

	public $family_line = 'ptx151';	
	public $copy_files = array("pd11gl3.bin","pd11sid.bin","pd11sid3.bin","pi110001.bin");
	
	function config_files() {
		$result=parent::config_files();
		$ext = $this->settings['line'][0]['username'];
		$result['sip_'.$ext.'.cfg'] = 'sip_$ext.cfg';
		unset($result['sip_$ext.cfg']);    
		return $result;
    }

    function prepare_for_generateconfig() {
		$this->mac = strtolower($this->mac);
		parent::prepare_for_generateconfig();
    }
}
