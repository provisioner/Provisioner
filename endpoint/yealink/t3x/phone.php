<?php
/**
 * Polycom SoundPoint In Production Modules Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_yealink_t3x_phone extends endpoint_yealink_base {

    public $family_line = 't3x';

    function generate_config() {
        //Yealink likes lower case letters in its mac address
        $this->mac = strtolower($this->mac);


        //Global 0000000000000000-blah.cfg file (too many zeros man! why?)
        $contents = $this->open_config_file("y000000000000.cfg");

        //We go ahead and build this each time a phone is added/changed the other models have a different number at the end of the file name eg:-
        //T38:y000000000038.cfg
        //T32:y000000000032.cfg
        switch($this->model) {
            case "T38":
                $final['y000000000038.cfg'] = $this->parse_config_file($contents, FALSE);
                break;
            case "T32":
                $final['y000000000032.cfg'] = $this->parse_config_file($contents, FALSE);
                break;
        }

		if(isset($this->options['linekey'])) {
			foreach($this->options['linekey'] as $key => $data) {
				if (($key >= 1) && ($key <=6))
					$this->options['linekey'][$key+10]=$this->options['linekey'][$key];
				}
			}
		}
		
		if(isset($this->options['softkey'])) {
            foreach($this->options['softkey'] as $key => $data) {
                if ($this->options['softkey'][$key]['type'] == '0') {
                    unset($this->options['softkey'][$key]);
				}
			}
		}

        if(isset($this->options['sdext38'])) {
            foreach($this->options['sdext38'] as $key => $data) {
                if ($this->options['sdext38'][$key]['type'] == '16') {
                    $this->options['sdext38'][$key]['pickup_value'] = $this->options['call_pickup'].$this->options['sdext38'][$key]['value'];
                } elseif ($this->options['sdext38'][$key]['type'] == '0') {
                    unset($this->options['sdext38'][$key]);
                } else {
                    $this->options['sdext38'][$key]['pickup_value'] = '*8';
                }
            }
        }

        if(isset($this->options['memkey'])) {
            foreach($this->options['memkey'] as $key => $data) {
                if ($this->options['memkey'][$key]['type'] == '16') {
                    $this->options['memkey'][$key]['pickup_value'] = $this->options['call_pickup'].$this->options['memkey'][$key]['value'];
                } elseif ($this->options['memkey'][$key]['type'] == '0') {
                    unset($this->options['memkey'][$key]);
                } else {
                    $this->options['memkey'][$key]['pickup_value'] = '*8';
                }
            }
        }

        if(isset($this->options['memkey2'])) {
            foreach($this->options['memkey2'] as $key => $data) {
                if ($this->options['memkey2'][$key]['type'] == '16') {
                    $this->options['memkey2'][$key]['pickup_value'] = $this->options['call_pickup'].$this->options['memkey2'][$key]['value'];
                } elseif ($this->options['memkey2'][$key]['type'] == '0') {
                    unset($this->options['memkey2'][$key]);
                } else {
                    $this->options['memkey2'][$key]['pickup_value'] = '*8';
                }
            }
        }
        
        //Yealink support lines 2-6, so let's add them if they're set
        for ($i = 1; $i < 6; $i++) {
            $this->lines[$i]['options']['line_active'] = (isset($this->lines[$i]['secret']) ? '1' : '0');
            $this->lines[$i]['options']['line_m1'] = (isset($this->lines[$i]['secret']) ? $i-1 : '');
            $this->lines[$i]['options']['voicemail_number'] = (isset($this->options['voicemail_number']) ? $this->options['voicemail_number'] : '');
        }




        //$mac.cfg file
        $contents = $this->open_config_file("\$mac.cfg");

        $final[$this->mac.'.cfg'] = $this->parse_config_file($contents, FALSE);


	//dialnow.xml file
	$contents = $this->open_config_file("dialnow.xml");

	$final['dialnow.xml'] = $this->parse_config_file($contents, FALSE);


        return($final);
    }
}
?>
