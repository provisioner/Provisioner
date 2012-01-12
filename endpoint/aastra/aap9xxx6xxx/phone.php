<?php

/**
 * Aastra xxxx Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_aastra_aap9xxx6xxx_phone extends endpoint_aastra_base {

    public $family_line = 'aap9xxx6xxx';
    public $en_htmlspecialchars = FALSE;
    public $dynamic_mapping = array(
        '$mac.cfg' => array('aastra.cfg','$mac.cfg'),
        'aastra.cfg' => '#This File is intentionally left blank'
    );
    
    function parse_lines_hook($line_data, $line_total) {
        $line_data['outbound_proxy_host'] = isset($line_data['outbound_proxy_host']) ? $line_data['outbound_proxy_host'] : $line_data['server_host'];
        $line_data['outbound_proxy_port'] = isset($line_data['outbound_proxy_port']) ? $line_data['outbound_proxy_port'] : $line_data['server_port'];
        return($line_data);
    }

    function generate_file($file, $extradata, $ignoredynamicmapping=FALSE) {
        $config = parent::generate_file($file, $extradata, $ignoredynamicmapping);
        if (($extradata == '$mac.cfg') && ($ignoredynamicmapping === FALSE) && ($this->enable_encryption)) {
            $this->enable_encryption();
            $config = $this->encrypt_files(array('$mac.cfg' => $config));
            return $config['$mac.cfg'];
        } else {
            $this->disable_encryption();
            return $config;
        }
    }

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();
        if (isset($this->settings['softkey'])) {
            foreach ($this->settings['softkey'] as $key => $data) {
                if ($this->settings['softkey'][$key]['type'] == 'empty') {
                    unset($this->settings['softkey'][$key]);
                }
            }
        }

        if (isset($this->settings['topsoftkey'])) {
            foreach ($this->settings['topsoftkey'] as $key => $data) {
                if ($this->settings['topsoftkey'][$key]['type'] == 'empty') {
                    unset($this->settings['topsoftkey'][$key]);
                }
            }
        }

        if (isset($this->settings['prgkey'])) {
            foreach ($this->settings['prgkey'] as $key => $data) {
                if ($this->settings['prgkey'][$key]['type'] == 'empty') {
                    unset($this->settings['prgkey'][$key]);
                }
            }
        }

        if (isset($this->settings['expmod1'])) {
            foreach ($this->settings['expmod1'] as $key => $data) {
                if ($this->settings['expmod1'][$key]['type'] == 'empty') {
                    unset($this->settings['expmod1'][$key]);
                }
            }
        }

        if (isset($this->settings['expmod2'])) {
            foreach ($this->settings['expmod2'] as $key => $data) {
                if ($this->settings['expmod2'][$key]['type'] == 'empty') {
                    unset($this->settings['expmod2'][$key]);
                }
            }
        }

        if (isset($this->settings['expmod3'])) {
            foreach ($this->settings['expmod3'] as $key => $data) {
                if ($this->settings['expmod3'][$key]['type'] == 'empty') {
                    unset($this->settings['expmod3'][$key]);
                }
            }
        }

        if (isset($this->settings['featkeys'])) {
            foreach ($this->settings['featkeys'] as $key => $data) {
                if ($this->settings['featkeys'][$key]['enable'] == '0') {
                    unset($this->settings['featkeys'][$key]);
                }
            }
        }

        if (!isset($this->settings['provisioning_server'])) {
            $this->settings['provisioning_server'] = $this->server[1]['ip'];
        }

        if (!isset($this->settings['provisioning_path'])) {
            $this->settings['provisioning_path'] = '';
        }

        switch ($this->provisioning_type) {
            case "tftp":
                $this->settings['provisioning_protocol'] = 'TFTP';
                break;
            case "http":
                $this->settings['provisioning_protocol'] = 'HTTP';
                break;
            case "https":
                $this->settings['provisioning_protocol'] = 'HTTPS';
                break;
        }
    }

}