<?php
/**
 * Yealink Modules Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_yealink_t2x_phone extends endpoint_yealink_base {

    public $family_line = 't2x';

	function parse_lines_hook($line) {
		$this->lines[$line]['options']['line_active'] = (isset($this->lines[$line]['secret']) ? '1' : '0');
        $this->lines[$line]['options']['line_m1'] = (isset($this->lines[$line]['secret']) ? $line-1 : '');
        $this->lines[$line]['options']['voicemail_number'] = (isset($this->options['voicemail_number']) ? $this->options['voicemail_number'] : '');
		
		if(isset($this->options['linekey'])) {
			switch ($line) {
				case "1":
					$this->options['linekey'][11]['type'] = $this->options['linekey'][$line]['type'];
					$this->options['linekey'][11]['mode'] = $this->options['linekey'][$line]['mode'];
					$this->options['linekey'][11]['line'] = $this->options['linekey'][$line]['line'];
					$this->options['linekey'][11]['extension'] = $this->options['linekey'][$line]['extension'];
					$this->options['linekey'][11]['pickup'] = $this->options['linekey'][$line]['pickup'];
					unset($this->options['linekey'][$line]);
					break;	
				case "2":
					$this->options['linekey'][12]['type'] = $this->options['linekey'][$line]['type'];
					$this->options['linekey'][12]['mode'] = $this->options['linekey'][$line]['mode'];
					$this->options['linekey'][12]['line'] = $this->options['linekey'][$line]['line'];
					$this->options['linekey'][12]['extension'] = $this->options['linekey'][$line]['extension'];
					$this->options['linekey'][12]['pickup'] = $this->options['linekey'][$line]['pickup'];
					unset($this->options['linekey'][$line]);
					break;
				case "3":
					$this->options['linekey'][13]['type'] = $this->options['linekey'][$line]['type'];
					$this->options['linekey'][13]['mode'] = $this->options['linekey'][$line]['mode'];
					$this->options['linekey'][13]['line'] = $this->options['linekey'][$line]['line'];
					$this->options['linekey'][13]['extension'] = $this->options['linekey'][$line]['extension'];
					$this->options['linekey'][13]['pickup'] = $this->options['linekey'][$line]['pickup'];
					unset($this->options['linekey'][$line]);
					break;
				case "4":
					$this->options['linekey'][14]['type'] = $this->options['linekey'][$line]['type'];
					$this->options['linekey'][14]['mode'] = $this->options['linekey'][$line]['mode'];
					$this->options['linekey'][14]['line'] = $this->options['linekey'][$line]['line'];
					$this->options['linekey'][14]['extension'] = $this->options['linekey'][$line]['extension'];
					$this->options['linekey'][14]['pickup'] = $this->options['linekey'][$line]['pickup'];
					unset($this->options['linekey'][$line]);
					break;
				case "5":
					$this->options['linekey'][15]['type'] = $this->options['linekey'][$line]['type'];
					$this->options['linekey'][15]['mode'] = $this->options['linekey'][$line]['mode'];
					$this->options['linekey'][15]['line'] = $this->options['linekey'][$line]['line'];
					$this->options['linekey'][15]['extension'] = $this->options['linekey'][$line]['extension'];
					$this->options['linekey'][15]['pickup'] = $this->options['linekey'][$line]['pickup'];
					unset($this->options['linekey'][$line]);
					break;		
				case "5":
					$this->options['linekey'][16]['type'] = $this->options['linekey'][$line]['type'];
					$this->options['linekey'][16]['mode'] = $this->options['linekey'][$line]['mode'];
					$this->options['linekey'][16]['line'] = $this->options['linekey'][$line]['line'];
					$this->options['linekey'][16]['extension'] = $this->options['linekey'][$line]['extension'];
					$this->options['linekey'][16]['pickup'] = $this->options['linekey'][$line]['pickup'];
					unset($this->options['linekey'][$line]);
					break;		
			}
		}
	}

    function generate_config() {
        //Yealink likes lower case letters in its mac address
        $this->mac = strtolower($this->mac);


        //Global 0000000000000000-blah.cfg file (too many zeros man! why?)
        $contents = $this->open_config_file("y000000000000.cfg");

        //We go ahead and build this each time a phone is added/changed the other models have a different number at the end of the file name eg:-
        //T28:y000000000000.cfg
        //T26:y000000000004.cfg
        //T22:y000000000005.cfg
        //T20:y000000000007.cfg
        switch($this->model) {
            case "T28":
                $final['y000000000000.cfg'] = $this->parse_config_file($contents, FALSE);
                break;
            case "T26":
                $final['y000000000004.cfg'] = $this->parse_config_file($contents, FALSE);
                break;
            case "T22":
                $final['y000000000005.cfg'] = $this->parse_config_file($contents, FALSE);
                break;
            case "T20":
                $final['y000000000007.cfg'] = $this->parse_config_file($contents, FALSE);
                break;
        }
		
		if(isset($this->options['softkey'])) {
            foreach($this->options['softkey'] as $key => $data) {
                if ($this->options['softkey'][$key]['type'] == '0') {
                    unset($this->options['softkey'][$key]);
				}
			}
		}

		if(isset($this->options['remotephonebook'])) {
            foreach($this->options['remotephonebook'] as $key => $data) {
                if ($this->options['remotephonebook'][$key]['url'] == '') {
                    unset($this->options['remotephonebook'][$key]);
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

        //$mac.cfg file
        $contents = $this->open_config_file("\$mac.cfg");

        $final[$this->mac.'.cfg'] = $this->parse_config_file($contents, FALSE);
		
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
