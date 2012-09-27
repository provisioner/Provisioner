<?php

/**
 * Xorcom Modules Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_xorcom_xpxxxx_phone extends endpoint_xorcom_base {

    public $family_line = 'xpxxxx';
    public $dynamic_mapping = array(
        '$mac.cfg' => array('$mac.cfg', 'y0000000000$suffix.cfg'),
        'y0000000000$suffix.cfg' => '#This File is intentionally left blank'
    );

    function parse_lines_hook($line_data, $line_total) {
        $line_data['line_active'] = 1;
        $line_data['line_m1'] = $line_data['line'] - 1;
        $line_data['enable_outbound_proxy_server'] = (isset($line_data['use_outbound_proxy']) && $line_data['use_outbound_proxy']) ? 1 : 0;
        $line_data['enable_stun'] = 0;
        $line_data['voicemail_number'] = '*97';

        if (isset($line_data['transport'])) {
            switch ($line_data['transport']) {
                case "UDP":
                    $line_data['transport'] = 0;
                    break;
                case "TCP":
                    $line_data['transport'] = 1;
                    break;
                case "TLS":
                    $line_data['transport'] = 2;
                    break;
                case "DNSSRV":
                    $line_data['transport'] = 3;
                    break;
                default:
                    $line_data['transport'] = 0;
                    break;
            }
        } else {
            $line_data['transport'] = 0;
        }

        return($line_data);
    }

    function prepare_for_generateconfig() {
        # This contains the last 2 digits of y0000000000xx.cfg, for each model.
        $model_suffixes = array('XP0100' => '11', 'XP0120' => '10');
        //Yealink likes lower case letters in its mac address
        $this->mac = strtolower($this->mac);
        $this->config_file_replacements['$suffix'] = $model_suffixes[$this->model];
        parent::prepare_for_generateconfig();

        //Setup password if not set
        if (!isset($this->settings['adminpw']) OR empty($this->settings['adminpw'])) {
            $this->settings['adminpw'] = substr(strrev(md5(filemtime(__FILE__) . date("j"))), 0, 8);
        }

        //Set softkeys or defaults
        if (isset($this->settings['loops']['softkey'])) {
            foreach ($this->settings['loops']['softkey'] as $key => $data) {
                //HIstory, Dir, DND, and Menu
                if ($this->settings['loops']['softkey'][$key]['type'] == '0') {
                    unset($this->settings['loops']['softkey'][$key]);
                }
            }
        } else {
            $this->settings['loops']['softkey'][1]['type'] = 28;
            $this->settings['loops']['softkey'][2]['type'] = 29;
            $this->settings['loops']['softkey'][3]['type'] = 5;
            $this->settings['loops']['softkey'][4]['type'] = 30;
        }

        if (isset($this->settings['loops']['remotephonebook'])) {
            foreach ($this->settings['loops']['remotephonebook'] as $key => $data) {
                if ($this->settings['loops']['remotephonebook'][$key]['url'] == '') {
                    unset($this->settings['loops']['remotephonebook'][$key]);
                }
            }
        }

        //Set line key defaults
        $s = $this->max_lines + 10;
        for ($i = 11; $i <= $s; $i++) {
            if (!isset($this->settings['loops']['linekey'][$i])) {
                $this->settings['loops']['linekey'][$i] = array(
                    "mode" => "blf",
                    "type" => 15,
                    "line" => 0
                );
            } elseif ($this->settings['loops']['linekey'][$i]['type'] == '16') {
                $this->settings['loops']['linekey'][$i]['line'] = $this->settings['loops']['linekey'][$i]['line'] != '0' ? $this->settings['loops']['linekey'][$i]['line'] - 1 : $this->settings['loops']['linekey'][$i]['line'];
            }
        }

        if (isset($this->settings['loops']['sdext38'])) {
            foreach ($this->settings['loops']['sdext38'] as $key => $data) {
                if ($this->settings['loops']['sdext38'][$key]['type'] == '16') {
                    $this->settings['loops']['sdext38'][$key]['pickup_value'] = $this->settings['call_pickup'] . $this->settings['loops']['sdext38'][$key]['value'];
                } elseif ($this->settings['loops']['sdext38'][$key]['type'] == '0') {
                    unset($this->settings['loops']['sdext38'][$key]);
                } else {
                    $this->settings['loops']['sdext38'][$key]['pickup_value'] = '*8';
                }
            }
        }

        if (isset($this->settings['loops']['memkey'])) {
            foreach ($this->settings['loops']['memkey'] as $key => $data) {
                if ($this->settings['loops']['memkey'][$key]['type'] == '16') {
                    $this->settings['loops']['memkey'][$key]['pickup_value'] = $this->settings['call_pickup'] . $this->settings['loops']['memkey'][$key]['value'];
                } elseif ($this->settings['loops']['memkey'][$key]['type'] == '0') {
                    unset($this->settings['loops']['memkey'][$key]);
                } else {
                    $this->settings['loops']['memkey'][$key]['pickup_value'] = '*8';
                }
            }
        }

        if (isset($this->settings['loops']['memkey2'])) {
            foreach ($this->settings['loops']['memkey2'] as $key => $data) {
                if ($this->settings['loops']['memkey2'][$key]['type'] == '16') {
                    $this->settings['loops']['memkey2'][$key]['pickup_value'] = $this->settings['call_pickup'] . $this->settings['loops']['memkey2'][$key]['value'];
                } elseif ($this->settings['loops']['memkey2'][$key]['type'] == '0') {
                    unset($this->settings['loops']['memkey2'][$key]);
                } else {
                    $this->settings['loops']['memkey2'][$key]['pickup_value'] = '*8';
                }
            }
        }
    }

}
