<?php

/**
 * Phone Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_polycom_splm_phone extends endpoint_polycom_base {

    public $family_line = 'splm';
    
    function parse_lines_hook($line, $line_total) {
        $this->settings['line'][$line]['digitmap'] = (isset($this->settings['digitmap']) ? $this->settings['digitmap'] : NULL);
        $this->settings['line'][$line]['digitmaptimeout'] = (isset($this->settings['digitmaptimeout']) ? $this->settings['digitmaptimeout'] : NULL);
        $this->settings['line'][$line]['microbrowser_main_home'] = (isset($this->settings['microbrowser_main_home']) ? $this->settings['microbrowser_main_home'] : NULL);
        $this->settings['line'][$line]['idle_display'] = (isset($this->settings['idle_display']) ? $this->settings['idle_display'] : NULL);
        $this->settings['line'][$line]['idle_display_refresh'] = (isset($this->settings['idle_display_refresh']) ? $this->settings['idle_display_refresh'] : NULL);
    }

    function prepare_for_generateconfig() {
        $this->mac = strtolower($this->mac);
        $this->settings['mac'] = strtolower($this->mac);
        parent::prepare_for_generateconfig();

        $this->settings['createdFiles'] = 'server_317.cfg, ' . $this->mac . '_reg.cfg, phone1_317.cfg, sip_317.cfg';
        $this->directory_structure = array("logs", "overrides", "contacts", "licenses");
        $this->copy_files = array("SoundPointIPLocalization", "SoundPointIPWelcome.wav");
        $this->protected_files = array('overrides/' . $this->mac . '-phone.cfg', 'logs/' . $this->mac . '-boot.log', 'logs/' . $this->mac . '-app.log', 'SoundPointIPLocalization');
    }

}
