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
        $macprefix = $this->server_type == 'dynamic' ? $this->mac . "_" : NULL;
        if ((isset($this->settings['file_prefix'])) && ($this->settings['file_prefix'] != "")) {
            $fn = $macprefix . $this->settings['file_prefix'] . '_sip.cfg';
            $result[$fn] = $result['sip.cfg'];
            unset($result['sip.cfg']);
            $this->settings['createdFiles'] = str_replace(", sip.cfg", ", $fn", $this->settings['createdFiles']);
        } elseif (isset($macprefix)) {
            $fn = $macprefix . 'sip.cfg';
            $result[$fn] = $result['sip.cfg'];
            unset($result['sip.cfg']);
            $this->settings['createdFiles'] = str_replace(", sip.cfg", ", $fn", $this->settings['createdFiles']);
        }
        return $result;
    }

    function prepare_for_generateconfig() {
        $this->mac = strtolower($this->mac);
        parent::prepare_for_generateconfig();

        if (isset($this->settings['attendant'])) {
            foreach ($this->settings['attendant'] as $key => $data) {
                if ($this->settings['attendant'][$key]['ext'] == '') {
                    unset($this->settings['attendant'][$key]);
                }
            }
        }

        $this->settings['createdFiles'] = $this->mac . '_reg.cfg, sip.cfg';

        $this->protected_files = array('overrides/' . $this->mac . '-phone.cfg', 'logs/' . $this->mac . '-boot.log', 'logs/' . $this->mac . '-app.log', 'SoundPointIPLocalization', 'overrides/' . $this->mac . '-phone.cfg');
    }

}
