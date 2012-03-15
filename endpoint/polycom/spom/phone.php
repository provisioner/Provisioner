<?php

/**
 * Phone Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_polycom_spom_phone extends endpoint_polycom_base {

    public $family_line = 'spom';

    function parse_lines_hook($line_data, $line_total) {
        $line_data['digitmap'] = (isset($this->settings['digitmap']) ? $this->settings['digitmap'] : NULL);
        $line_data['digitmaptimeout'] = (isset($this->settings['digitmaptimeout']) ? $this->settings['digitmaptimeout'] : NULL);
		return($line_data);
    }

    function prepare_for_generateconfig() {
        $this->mac = strtolower($this->mac);
        $this->settings['mac'] = strtolower($this->mac);
        parent::prepare_for_generateconfig();

        $this->settings['createdFiles'] = "server_213.cfg, " . $this->mac . "_reg.cfg, phone1_213.cfg, sip_213.cfg";

        $this->directory_structure = array("logs", "overrides", "contacts", "licenses");

        $this->copy_files = array("SoundPointIPLocalization", "SoundPointIPWelcome.wav");

        $this->protected_files = array('overrides/' . $this->mac . '-phone.cfg', 'logs/' . $this->mac . '-boot.log', 'logs/' . $this->mac . '-app.log', 'SoundPointIPLocalization');
    }

}
