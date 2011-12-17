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
        
        $this->configfiles = array(
            '$mac.cfg' => $this->mac . '_reg.cfg',
            'server_325.cfg' => 'server_325.cfg',
            'phone1_325.cfg' => 'phone1_325.cfg',
            'sip_325.cfg' => 'sip_325.cfg'
        );
        
        $macprefix = $this->server_type == 'dynamic' ? $this->mac . "_" : NULL;        
        if ((isset($this->settings['file_prefix'])) && ($this->settings['file_prefix'] != "")) {
            $fp = $this->settings['file_prefix'];
            foreach(array_values($this->configfiles) as $data) {
                if(isset($result[$data]) AND $data != $this->mac . '_reg.cfg') {
                    $result[$fp.$data] = $result[$data];
                    $this->configfiles[$data] = $fp.$data;
                    unset($result[$data]);
                }
            }
        } elseif (isset($macprefix)) {
            foreach(array_values($this->configfiles) as $data) {
                if(isset($result[$data]) AND $data != $this->mac . '_reg.cfg') {
                    $result[$macprefix.$data] = $result[$data];
                    $this->configfiles[$data] = $macprefix.$data;
                    unset($result[$data]);
                }
            }
        }
        
        $this->settings['createdFiles'] = implode(', ', array_values($this->configfiles));
        
        return $result;
    }

    function prepare_for_generateconfig() {
        $this->mac = strtolower($this->mac);
        
        $this->settings['mac'] = strtolower($this->mac);

        parent::prepare_for_generateconfig();

        $this->protected_files = array('overrides/' . $this->mac . '-phone.cfg', 'logs/' . $this->mac . '-boot.log', 'logs/' . $this->mac . '-app.log', 'SoundPointIPLocalization');
    }

}
