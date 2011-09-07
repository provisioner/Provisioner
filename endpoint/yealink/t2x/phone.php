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
			if (($line >= 1) && ($line <=6)) {
				$this->options['linekey'][$line+10]=$this->options['linekey'][$line];
			}
		}
	}
	function prepare_for_generateconfig() {
		# This contains the last 2 digits of y0000000000xx.cfg, for each model.
		$model_suffixes=array('T28'=>'00','T26'=>'04','T22'=>'05','T20'=>'07');
		//Yealink likes lower case letters in its mac address
        	$this->mac = strtolower($this->mac);
		$this->config_file_replacements['$suffix']=$model_suffixes[$this->model];
		parent::prepare_for_generateconfig();

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

	}
}
?>
