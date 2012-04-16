<?php
/**
 * Yealink Modules Phone File
 *
 * @author Michael Beham
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_yealink_t1x_phone extends endpoint_yealink_base {

    public $family_line = 't1x';
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
        return($line_data);
    }

    function prepare_for_generateconfig() {
        # This contains the last 2 digits of y0000000000xx.cfg, for each model.
        $model_suffixes = array('T18' => '09');
        //Yealink likes lower case letters in its mac address
        $this->mac = strtolower($this->mac);
        $this->config_file_replacements['$suffix'] = $model_suffixes[$this->model];
        parent::prepare_for_generateconfig();


        if (isset($this->settings['loops']['remotephonebook'])) {
            foreach ($this->settings['loops']['remotephonebook'] as $key => $data) {
                if ($this->settings['loops']['remotephonebook'][$key]['url'] == '') {
                    unset($this->settings['loops']['remotephonebook'][$key]);
                }
            }
        }

    }

}
