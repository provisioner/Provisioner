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

    function generate_config() {
        //Yealink likes lower case letters in its mac address
        $this->mac = strtoupper($this->mac);

		$this->options['model'] = $this->model;

        //Global Generic File
        $contents = $this->open_config_file("MN_Generic.cfg");
		$final['MN_Generic.cfg'] = $this->parse_config_file($contents, FALSE);

        //MN_$mac.cfg Specific file
        $contents = $this->open_config_file("\$mac.cfg");
        $final['MN_'.$this->mac.'.cfg'] = $this->parse_config_file($contents, FALSE);

		
		if($this->server_type == 'dynamic') {
			$out = '';
			$out[$this->mac.'.cfg'] = '';
			foreach($final as $key => $value) {
				$out[$this->mac.'.cfg'] .= $value . "\n";
				if($key != $this->mac.'.cfg') {
					$out[$key] = '#This File is intentionally left blank';
				}
			}
			
			return($out);
		} else {
			return($final);
		}

    }
}
?>