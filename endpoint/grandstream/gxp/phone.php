<?php

/**
 * Grandstream GXP Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_grandstream_gxp_phone extends endpoint_grandstream_base {

    public $family_line = 'gxp';
    
    function parse_lines_hook($line_data, $line_total) {
        $line_data['line_active'] = (isset($line_data['secret']) ? '1' : '0');
        return($line_data);
    }

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();

        if (isset($this->settings['loop']['ext1'])) {
            foreach ($this->settings['loop']['ext1'] as $key => $data) {
                if ($this->settings['loop']['ext1'][$key]['mode'] == '999') {

                    $this->settings['loop']['ext1'][$key]['account'] = '';
                    $this->settings['loop']['ext1'][$key]['name'] = '';
                    $this->settings['loop']['ext1'][$key]['uid'] = '';
                    $this->settings['loop']['ext1'][$key]['mode'] = '';
                }

                $this->settings['loop']['ext1'][$key]['pnum'] = (strlen($key) == '1') ? '0' . $key : $key;
            }
        }

        if (isset($this->settings['loop']['ext2'])) {
            foreach ($this->settings['loop']['ext2'] as $key => $data) {
                if ($this->settings['loop']['ext2'][$key]['mode'] == '999') {

                    $this->settings['loop']['ext2'][$key]['account'] = '';
                    $this->settings['loop']['ext2'][$key]['name'] = '';
                    $this->settings['loop']['ext2'][$key]['uid'] = '';
                    $this->settings['loop']['ext2'][$key]['mode'] = '';
                }

                $this->settings['loop']['ext2'][$key]['pnum'] = (strlen($key) == '1') ? '0' . $key : $key;
            }
        }
    }

}
