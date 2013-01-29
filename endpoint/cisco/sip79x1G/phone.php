<?php
/**
 * Cisco SIP 7900 Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_sip79x1G_phone extends endpoint_cisco_base {
	
	public $family_line = 'sip79x1G';
	public $mapfields=array( // this is applied in the main base.php
		'dateformat'=>array(
			'default'=>'M/D/YA',
			'middle-endian'=>'M/D/YA',
			'big-endian'=>'YA.M.D',
			'little-endian'=>'D-M-Ya',
		),
		// Tonescheme. I don't understand the values for these, and some parts may later need to be seperated out.
		// Note that the keys are ISO 3166 codes, see Wiki. Defaults to US.
		'tonescheme'=>array(
			'default'=>'<networkLocale>United_States</networkLocale><networkLocaleInfo><name>United_States</name><uid>64</uid><version>1.0.0.0-1</version></networkLocaleInfo>',
			'UK'=>'<networkLocale>United_Kingdom</networkLocale><networkLocaleInfo><name>United_Kingdom</name></networkLocaleInfo>',
			'US'=>'<networkLocale>United_States</networkLocale><networkLocaleInfo><name>United_States</name><uid>64</uid><version>1.0.0.0-1</version></networkLocaleInfo>',
			'NZ'=>'<networkLocaleInfo><name>New_Zealand</name><version>5.0(2)a</version></networkLocaleInfo>',
		),
		'ciscotz'=>array( 
			// Add in your Timezone. Find the php name for your timezone in http://www.php.net/manual/en/timezones.php, 
			// and the cisco one in http://www.voip-info.org/wiki/view/Asterisk+phone+cisco+79x1+xml+configuration+files+for+SIP
			// Then add it to the list, in alphabetical order, phptimezone=>ciscotimezone
			'default'=>'Central Standard/Daylight Time', // somewhere has to be default.
			'America/Los_Angeles'=>'Central Standard/Daylight Time',
			'Europe/Dublin'=>'GMT Standard/Daylight Time',
			'Pacific/Auckland'=>'New Zealand Standard/Daylight Time',
		),
	);
	function prepare_for_generateconfig() {
		$this->settings['ciscotz']=$this->DateTimeZone->getName();
		parent::prepare_for_generateconfig();
		$this->config_file_replacements['$mac']=strtoupper($this->mac);
		foreach ($this->settings['line'] AS &$line) {
			if (array_key_exists('displayname',$line) && (strlen($line['displayname']) > 11)) {
				$name = explode(" ", $line['displayname']);
				$line['displayname'] = substr($name[0],0,11);
			}
		}
		//Cisco time offset is in minutes, our global variable is in seconds
		//$this->timezone = $global_cfg['gmtoff']/60;
	}
	
}
