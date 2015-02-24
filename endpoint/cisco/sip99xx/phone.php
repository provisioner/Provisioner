<?php
/**
 * Cisco SIP 9900 Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_sip99xx_phone extends endpoint_cisco_base {
	
	public $family_line = 'sip99xx';
	public $mapfields=array( // this is applied in the main base.php
		'dateformat'=>array(
			'default'=>'D/M/YA',
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
			'default'=>'E. Australia Standard Time', // somewhere has to be default.
			'America/Los_Angeles'=>'Central Standard/Daylight Time',
			'Europe/Dublin'=>'GMT Standard/Daylight Time',
			'Pacific/Auckland'=>'New Zealand Standard/Daylight Time',
			'Australia/Brisbane'=>'E. Australia Standard Time',
			'Australia/Sydney'=>'AUS Eastern Standard/Daylight Time',
			'Australia/Adelaide'=>'Central Pacific Standard Time',
			'Australia/Tasmania'=>'Tasmania Standard/Daylight Time',
			'Australia/Perth'=>'W. Australia Standard Time',
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
		if (isset($this->settings['loops']['backup'])) {
		            foreach ($this->settings['loops']['backup'] as $key => $data) {
		                if ($this->settings['loops']['backup'][$key]['ip'] == '') {
		                    unset($this->settings['loops']['backup'][$key]);
		                }
		            }
	        }
		if (isset($this->settings['loops']['linekey'])) {
		            foreach ($this->settings['loops']['linekey'] as $key => $data) {
		                if ($this->settings['loops']['linekey'][$key]['label'] == '') {
		                    unset($this->settings['loops']['linekey'][$key]);
		                }
		            }
	        }
               if (isset($this->settings['loops']['kemkey']) and $this->settings["kem"] != '') {
                            foreach ($this->settings['loops']['kemkey'] as $key => $data) {
                                if ($this->settings['loops']['kemkey'][$key]['label'] == '') {
                                    unset($this->settings['loops']['kemkey'][$key]);
                                }
                            }
                }
		if (isset($this->settings['loops']['servicekey'])) {
		            foreach ($this->settings['loops']['servicekey'] as $key => $data) {
		                if ($this->settings['loops']['servicekey'][$key]['name'] == '') {
		                    unset($this->settings['loops']['servicekey'][$key]);
		                }
		            }
	        }





	}
	
}
