<?php

/**
 * Polycom SoundPoint In Production Modules Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_yealink_t3x_phone extends endpoint_yealink_base {

    public $family_line = 't3x';

    function parse_lines_hook($line_data, $line_total) {
        $line_data['line_active'] = 1;
        $line_data['line_m1'] = $line_data['line'] - 1;
        $line_data['voicemail_number'] = '*97';
        return($line_data);
    }
    
    function prepare_for_generateconfig() {
        # This contains the last 2 digits of y0000000000xx.cfg, for each model.
        $model_suffixes = array('T38' => '38', 'T32' => '32');
        //Yealink likes lower case letters in its mac address
        $this->mac = strtolower($this->mac);
        $this->config_file_replacements['$suffix'] = $model_suffixes[$this->model];
        parent::prepare_for_generateconfig();

        if (isset($this->settings['linekey'])) {
            foreach ($this->settings['linekey'] as $key => $data) {
                if (($key >= 1) && ($key <= 6)) {
                    $this->settings['linekey'][$key + 10] = $this->settings['linekey'][$key];
                }
            }
        }

        if (isset($this->settings['softkey'])) {
            foreach ($this->settings['softkey'] as $key => $data) {
                if ($this->settings['softkey'][$key]['type'] == '0') {
                    unset($this->settings['softkey'][$key]);
                }
            }
        }

        if (isset($this->settings['remotephonebook'])) {
            foreach ($this->settings['remotephonebook'] as $key => $data) {
                if ($this->settings['remotephonebook'][$key]['url'] == '') {
                    unset($this->settings['remotephonebook'][$key]);
                }
            }
        }

        if (isset($this->settings['sdext38'])) {
            foreach ($this->settings['sdext38'] as $key => $data) {
                if ($this->settings['sdext38'][$key]['type'] == '16') {
                    $this->settings['sdext38'][$key]['pickup_value'] = $this->settings['call_pickup'] . $this->settings['sdext38'][$key]['value'];
                } elseif ($this->settings['sdext38'][$key]['type'] == '0') {
                    unset($this->settings['sdext38'][$key]);
                } else {
                    $this->settings['sdext38'][$key]['pickup_value'] = '*8';
                }
            }
        }

        if (isset($this->settings['memkey'])) {
            foreach ($this->settings['memkey'] as $key => $data) {
                if ($this->settings['memkey'][$key]['type'] == '16') {
                    $this->settings['memkey'][$key]['pickup_value'] = $this->settings['call_pickup'] . $this->settings['memkey'][$key]['value'];
                } elseif ($this->settings['memkey'][$key]['type'] == '0') {
                    unset($this->settings['memkey'][$key]);
                } else {
                    $this->settings['memkey'][$key]['pickup_value'] = '*8';
                }
            }
        }

        if (isset($this->settings['memkey2'])) {
            foreach ($this->settings['memkey2'] as $key => $data) {
                if ($this->settings['memkey2'][$key]['type'] == '16') {
                    $this->settings['memkey2'][$key]['pickup_value'] = $this->settings['call_pickup'] . $this->settings['memkey2'][$key]['value'];
                } elseif ($this->settings['memkey2'][$key]['type'] == '0') {
                    unset($this->settings['memkey2'][$key]);
                } else {
                    $this->settings['memkey2'][$key]['pickup_value'] = '*8';
                }
            }
        }
    }

}