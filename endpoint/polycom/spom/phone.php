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
    private $configfiles = array();
    public $directory_structure = array("logs", "overrides", "contacts", "licenses", "SoundPointIPLocalization", "languages");
    public $copy_files = array("SoundPointIPLocalization", "SoundPointIPWelcome.wav");

    function __construct() {
        parent::__construct();

        $dir = $this->root_dir . $this->modules_path . $this->brand_name . '/' . $this->family_line . '/SoundPointIPLocalization/';
        foreach (glob($dir . "*", GLOB_ONLYDIR) as $directory) {
            foreach (glob($directory . "/*.xml") as $filename) {
                $file = str_replace($dir, '', $filename);
                $this->copy_files[] = 'SoundPointIPLocalization/' . $file;
            }
        }
    }

    function parse_lines_hook($line_data, $line_total) {
		$line = $line_data['line'];
        $line_data['lineKeys'] = isset($this->settings['loops']['lineops'][$line]) ? $this->settings['loops']['lineops'][$line]['linekeys'] : '1';
        $line_data['digitmap'] = (isset($this->settings['digitmap']) ? $this->settings['digitmap'] : NULL);
        $line_data['digitmaptimeout'] = (isset($this->settings['digitmaptimeout']) ? $this->settings['digitmaptimeout'] : NULL);
        return($line_data);
    }

    function prepare_for_generateconfig() {
        $this->mac = strtolower($this->mac);
        $this->settings['mac'] = strtolower($this->mac);
        parent::prepare_for_generateconfig();

        $this->settings['createdFiles'] = "server_213.cfg, " . $this->mac . "_reg.cfg, phone1_213.cfg, sip_213.cfg";

        $this->protected_files = array('overrides/' . $this->mac . '-phone.cfg', 'logs/' . $this->mac . '-boot.log', 'logs/' . $this->mac . '-app.log', 'SoundPointIPLocalization');
    }

}
