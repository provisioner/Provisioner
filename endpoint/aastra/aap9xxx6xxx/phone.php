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
    protected $en_htmlspecialchars = FALSE;
    protected $dynamic_mapping = array(
        '$mac.cfg' => array('aastra.cfg','$mac.cfg'),
        'aastra.cfg' => '#This File is intentionally left blank'
    );
    
    function parse_lines_hook($line_data, $line_total) {
        $line_data['outbound_proxy_host'] = isset($line_data['outbound_proxy_host']) ? $line_data['outbound_proxy_host'] : $line_data['server_host'];
        $line_data['outbound_proxy_port'] = isset($line_data['outbound_proxy_port']) ? $line_data['outbound_proxy_port'] : $line_data['server_port'];
        return($line_data);
    }

    function generate_file($file, $extradata, $ignoredynamicmapping=FALSE, $prepare=FALSE) {
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
        if (isset($this->settings['loops']['softkey'])) {
            foreach ($this->settings['loops']['softkey'] as $key => $data) {
                if ($this->settings['loops']['softkey'][$key]['type'] == 'empty') {
                    unset($this->settings['loops']['softkey'][$key]);
                }
            }
        }

        if (isset($this->settings['loops']['topsoftkey'])) {
            foreach ($this->settings['loops']['topsoftkey'] as $key => $data) {
                if ($this->settings['loops']['topsoftkey'][$key]['type'] == 'empty') {
                    unset($this->settings['loops']['topsoftkey'][$key]);
                }
            }
        }

        if (isset($this->settings['loops']['prgkey'])) {
            foreach ($this->settings['loops']['prgkey'] as $key => $data) {
                if ($this->settings['loops']['prgkey'][$key]['type'] == 'empty') {
                    unset($this->settings['loops']['prgkey'][$key]);
                }
            }
        }

        if (isset($this->settings['loops']['expmod1'])) {
            foreach ($this->settings['loops']['expmod1'] as $key => $data) {
                if ($this->settings['loops']['expmod1'][$key]['type'] == 'empty') {
                    unset($this->settings['loops']['expmod1'][$key]);
                }
            }
        }

        if (isset($this->settings['loops']['expmod2'])) {
            foreach ($this->settings['loops']['expmod2'] as $key => $data) {
                if ($this->settings['loops']['expmod2'][$key]['type'] == 'empty') {
                    unset($this->settings['loops']['expmod2'][$key]);
                }
            }
        }

        if (isset($this->settings['loops']['expmod3'])) {
            foreach ($this->settings['loops']['expmod3'] as $key => $data) {
                if ($this->settings['loops']['expmod3'][$key]['type'] == 'empty') {
                    unset($this->settings['loops']['expmod3'][$key]);
                }
            }
        }

        if (isset($this->settings['loops']['featkeys'])) {
            foreach ($this->settings['loops']['featkeys'] as $key => $data) {
                if ($this->settings['loops']['featkeys'][$key]['enable'] == '0') {
                    unset($this->settings['loops']['featkeys'][$key]);
                }
            }
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
