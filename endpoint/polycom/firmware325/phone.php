<?php

/**
 * Phone Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_polycom_firmware325_phone extends endpoint_polycom_base {

    public $family_line = 'firmware325';
    public $directory_structure = array("logs", "overrides", "contacts", "licenses", "SoundPointIPLocalization");
    public $copy_files = array("SoundPointIPLocalization", "SoundPointIPWelcome.wav", "LoudRing.wav");

    function parse_lines_hook($line, $line_total) {
        $this->settings['line'][$line]['lineKeys'] = $line_total;
        $this->settings['line'][$line]['digitmap'] = (isset($this->settings['digitmap']) ? $this->settings['digitmap'] : NULL);
        $this->settings['line'][$line]['digitmaptimeout'] = (isset($this->settings['digitmaptimeout']) ? $this->settings['digitmaptimeout'] : NULL);
        $this->settings['line'][$line]['microbrowser_main_home'] = (isset($this->settings['microbrowser_main_home']) ? $this->settings['microbrowser_main_home'] : NULL);
        $this->settings['line'][$line]['idle_display'] = (isset($this->settings['idle_display']) ? $this->settings['idle_display'] : NULL);
        $this->settings['line'][$line]['idle_display_refresh'] = (isset($this->settings['idle_display_refresh']) ? $this->settings['idle_display_refresh'] : NULL);
    }

    function config_files() {
        $result = parent::config_files();

        $macprefix = $this->server_type == 'dynamic' ? strtolower($this->mac) : NULL;

        if ((isset($this->settings['file_prefix'])) && ($this->settings['file_prefix'] != "")) {

            $prefix = $this->settings['file_prefix'] . '_';
            $result[$prefix . 'server_325.cfg'] = 'server_325.cfg';
            unset($result['server_325.cfg']);

            $result[$prefix . 'sip_325.cfg'] = 'sip_325.cfg';
            unset($result['sip_325.cfg']);

            $result[$prefix . 'phone1_325.cfg'] = 'phone1_325.cfg';
            unset($result['phone1_325.cfg']);
        } elseif (isset($macprefix)) {
            $prefix = $macprefix . '_';

            $result[$prefix . 'server_325.cfg'] = 'server_325.cfg';
            unset($result['server_325.cfg']);

            $result[$prefix . 'sip_325.cfg'] = 'sip_325.cfg';
            unset($result['sip_325.cfg']);

            $result[$prefix . 'phone1_325.cfg'] = 'phone1_325.cfg';
            unset($result['phone1_325.cfg']);
        }
        return $result;
    }

    function prepare_for_generateconfig() {
        $this->mac = strtolower($this->mac);
        
        $this->settings['mac'] = strtolower($this->mac);

        parent::prepare_for_generateconfig();

        $this->settings['createdFiles'] = 'server_325.cfg, ' . $this->mac . '_reg.cfg, phone1_325.cfg, sip_325.cfg';

        $this->protected_files = array('overrides/' . $this->mac . '-phone.cfg', 'logs/' . $this->mac . '-boot.log', 'logs/' . $this->mac . '-app.log', 'SoundPointIPLocalization');
    }

}
