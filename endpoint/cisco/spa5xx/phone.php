<?php

/**
 * Cisco SPA Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_spa5xx_phone extends endpoint_cisco_base {

    public $family_line = 'spa5xx';

    function parse_lines_hook($line) {

        if (strlen($this->settings['line'][$line]['displayname']) > 12) {
            $short_name = substr($this->settings['line'][$line]['displayname'], 0, 8) . "...";
        } else {
            $short_name = $this->settings['line'][$line]['displayname'];
        }
        if ((isset($this->settings['line'][$line]['secret'])) && ($this->settings['line'][$line]['secret'] != "")) {
            $this->settings['line'][$line]['dial_plan'] = $this->settings['dial_plan'];
        } else {
            $this->settings['line'][$line]['dial_plan'] = "";
        }
        if (isset($this->settings['lineops'][$line])) {
            $this->settings['line'][$line]['displaynameline'] = $this->settings['lineops'][$line]['displaynameline'];
            $this->settings['line'][$line]['short_name'] = $this->settings['lineops'][$line]['displaynameline'];
            if (($this->settings['lineops'][$line]['keytype'] == "blf") AND ($this->settings['lineops'][$line]['blfext'] != "")) {
                $this->settings['line'][$line]['username'] = $this->settings['lineops'][$line]['blfext'];
                $this->settings['line'][$line]['secret'] = 'n/a';
                $this->settings['line'][$line]['blf_ext_type'] = "Disabled";
                $this->settings['line'][$line]['share_call_appearance'] = "shared";
                $this->settings['line'][$line]['extended_function'] = "fnc=blf+sd+cp;sub=" . $this->settings['lineops'][$line]['blfext'] . "@" . $this->server[1]['ip'];
            } elseif (($this->settings['lineops'][$line]['keytype'] == "sd") AND ($this->settings['lineops'][$line]['blfext'] != "")) {
                $this->settings['line'][$line]['username'] = $this->settings['lineops'][$line]['blfext'];
                $this->settings['line'][$line]['secret'] = 'n/a';
                $this->settings['line'][$line]['blf_ext_type'] = "Disabled";
                $this->settings['line'][$line]['share_call_appearance'] = "shared";
                $this->settings['line'][$line]['extended_function'] = "fnc=sd;sub=" . $this->settings['lineops'][$line]['blfext'] . "@" . $this->server[1]['ip'];
            } else {
                if (!isset($this->settings['line'][$line]['secret'])) {
                    $this->settings['line'][$line]['displaynameline'] = $this->lines[1]['options']['displaynameline'];
                    $this->settings['line'][$line]['short_name'] = $this->lines[1]['options']['short_name'];
                    $this->settings['line'][$line]['username'] = '';
                    $this->settings['line'][$line]['secret'] = '';
                    $this->settings['line'][$line]['blf_ext_type'] = "1";
                    $this->settings['line'][$line]['share_call_appearance'] = "private";
                    $this->settings['line'][$line]['extended_function'] = "";
                } else {
                    $this->settings['line'][$line]['blf_ext_type'] = $line;
                    $this->settings['line'][$line]['share_call_appearance'] = "private";
                    $this->settings['line'][$line]['extended_function'] = "";
                }
            }
        } else {
            $this->settings['line'][$line]['displaynameline'] = $this->settings['line'][$line]['displayname'];
            $this->settings['line'][$line]['short_name'] = $short_name;
            $this->settings['line'][$line]['blf_ext_type'] = "1";
            $this->settings['line'][$line]['share_call_appearance'] = "private";
            $this->settings['line'][$line]['extended_function'] = "";
        }
    }

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();

        if (isset($this->settings['unit1'])) {
            foreach ($this->settings['unit1'] as $key => $data) {
                if ($this->settings['unit1'][$key]['data'] == '') {
                    unset($this->settings['unit1'][$key]);
                }
                if (($this->settings['unit1'][$key]['data'] != '') && ($this->settings['unit1'][$key]['keytype'] == 'blf')) {
                    $temp_ext = $this->settings['unit1'][$key]['data'];
                    $this->settings['unit1'][$key]['data'] = "fnc=blf+sd+cp;sub=" . $temp_ext . "@" . $this->server[1]['ip'];
                }
            }
        }

        if (isset($this->settings['unit2'])) {
            foreach ($this->settings['unit2'] as $key => $data) {
                if ($this->settings['unit2'][$key]['data'] == '') {
                    unset($this->settings['unit2'][$key]);
                }
                if (($this->settings['unit2'][$key]['data'] != '') && ($this->settings['unit2'][$key]['keytype'] == 'blf')) {
                    $temp_ext = $this->settings['unit2'][$key]['data'];
                    $this->settings['unit2'][$key]['data'] = "fnc=blf+sd+cp;sub=" . $temp_ext . "@" . $this->server[1]['ip'];
                }
            }
        }
    }

}
