<?php
/**
 * Phone Base File
 *
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_polycom_spipm_phone extends endpoint_polycom_base {

    public $family_line = 'spipm';
    public $directory_structure = array("logs","overrides","contacts","licenses","SoundPointIPLocalization");
    public $copy_files = array("SoundPointIPLocalization","SoundPointIPWelcome.wav","LoudRing.wav");

    function parse_lines_hook($line,$line_total) {
        $this->lines[$line]['options']['lineKeys'] = $line_total;
    }

    function config_files() {
	$result=parent::config_files();
	if((isset($this->options['file_prefix'])) && ($this->options['file_prefix'] != "")) {
		$fn=$this->options['file_prefix'].'_sip.cfg';
		$result[$fn]=$result['sip.cfg'];
		unset($result['sip.cfg']);
		$this->options['createdFiles'] = str_replace(", sip.cfg,",", $fn,",$this->options['createdFiles']);
	}
	return $result;
    }

    function prepare_for_generateconfig() {

        for ($i = 1; $i < 10; $i++) {
            if(isset($this->lines[$i]['secret'])) {
                $this->lines[$i]['options']['digitmap'] = (isset($this->options['digitmap']) ? $this->options['digitmap'] : NULL);
                $this->lines[$i]['options']['digitmaptimeout'] = (isset($this->options['digitmaptimeout']) ? $this->options['digitmaptimeout'] : NULL);
                $this->lines[$i]['options']['microbrowser_main_home'] = (isset($this->options['microbrowser_main_home']) ? $this->options['microbrowser_main_home'] : NULL);
                $this->lines[$i]['options']['idle_display'] = (isset($this->options['idle_display']) ? $this->options['idle_display'] : NULL);
                $this->lines[$i]['options']['idle_display_refresh'] = (isset($this->options['idle_display_refresh']) ? $this->options['idle_display_refresh'] : NULL);
            }
        }

        $this->options['createdFiles'] = $this->mac.'_reg.cfg, sip.cfg';

	$this->protected_files = array('overrides/'.$this->mac.'-phone.cfg', 'logs/'.$this->mac.'-boot.log', 'logs/'.$this->mac.'-app.log','SoundPointIPLocalization');
    }


}
