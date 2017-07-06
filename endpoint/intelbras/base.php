<?php

/**
 * Intelbras Base File
 *
 * @author Pedro Kiefer
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
abstract class endpoint_intelbras_base extends endpoint_base {

    public $brand_name = 'intelbras';
    protected $use_system_dst = FALSE;

    function reboot() {
        if (($this->engine == "asterisk") AND ($this->system == "unix")) {
            if ($this->settings['line'][0]['tech'] == "pjsip") {
                exec($this->engine_location . " -rx 'pjsip send notify reboot-intelbras endpoint " . $this->settings['line'][0]['username'] . "'");
            } else {
                exec($this->engine_location . " -rx 'sip notify reboot-intelbras " . $this->settings['line'][0]['username'] . "'");
            }
        }
    }

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();
        preg_match('/.*(-|\+)(\d*):(\d*)/i', $this->timezone['timezone'], $matches);
        switch ($matches[3]) {
            case '30':
                $point = '.5';
                break;
            default:
                $point = '';
                break;
        }
        $this->timezone['timezone'] = $matches[1] . $matches[2] . $point;
    }

}
