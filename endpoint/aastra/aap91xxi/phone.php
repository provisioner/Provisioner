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
    protected $en_htmlspecialchars = FALSE;
    protected $dynamic_mapping = array(
        '$mac.cfg' => array('$mac.cfg', 'aastra.cfg'),
        'aastra.cfg' => '#This File is intentionally left blank'
    );

    function parse_lines_hook($line_data, $line_total) {
        $line_data['outbound_proxy_host'] = isset($line_data['outbound_proxy_host']) ? $line_data['outbound_proxy_host'] : $line_data['server_host'];
        $line_data['outbound_proxy_port'] = isset($line_data['outbound_proxy_port']) ? $line_data['outbound_proxy_port'] : $line_data['server_port'];
        return($line_data);
    }

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();

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
