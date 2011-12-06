<?php

/**
 * Aastra 9133i and 9122i Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_aastra_aap91xxi_phone extends endpoint_aastra_base {

    public $family_line = 'aap91xxi';
    public $en_htmlspecialchars = FALSE;
    public $dynamic_mapping = array(
        '$mac.cfg' => array('$mac.cfg', 'aastra.cfg'),
        'aastra.cfg' => '#This File is intentionally left blank'
    );

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();

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