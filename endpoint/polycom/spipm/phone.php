<?php
/**
 * Phone Base File
 *
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_polycom_spipm_phone extends endpoint_polycom_base {

    public $family_line = 'spipm';
    private $configfiles = array();
    public $directory_structure = array("logs", "overrides", "contacts", "licenses", "SoundPointIPLocalization");
    public $copy_files = array("SoundPointIPLocalization", "SoundPointIPWelcome.wav", "LoudRing.wav");
    
    function parse_lines_hook($line_data, $line_total) {
        $line_data['digitmap'] = (isset($this->settings['digitmap']) ? $this->settings['digitmap'] : NULL);
        $line_data['digitmaptimeout'] = (isset($this->settings['digitmaptimeout']) ? $this->settings['digitmaptimeout'] : NULL);
        
        $line = $line_data['line'];
        $line_data['lineKeys'] = isset($this->settings['loops']['lineops'][$line]) ? $this->settings['loops']['lineops'][$line]['linekeys'] : '1';
        
        return($line_data);
    }

    function config_files() {
        $result = parent::config_files();
        $this->configfiles = array(
            '$mac.cfg' => $this->mac . '_reg.cfg',
            'sip.cfg' => 'sip.cfg'
        );
        
        $macprefix = $this->server_type == 'dynamic' ? $this->mac . "_" : NULL;
        if ((isset($this->settings['file_prefix'])) && ($this->settings['file_prefix'] != "")) {
            $fp = $this->settings['file_prefix'];
            foreach(array_values($this->configfiles) as $data) {
                if(isset($result[$data]) AND $data != $this->mac . '_reg.cfg') {
                    $result[$fp.$data] = $result[$data];
                    $this->configfiles[$data] = $fp.$data;
                    unset($result[$data]);
                }
            }
        } elseif (isset($macprefix)) {
            foreach(array_values($this->configfiles) as $data) {
                if(isset($result[$data]) AND $data != $this->mac . '_reg.cfg') {
                    $result[$macprefix.$data] = $result[$data];
                    $this->configfiles[$data] = $macprefix.$data;
                    unset($result[$data]);
                }
            }
        }
        
        //This is for the regular $mac.cfg file.
        $this->settings['createdFiles'] = implode(', ', array_values($this->configfiles));
        
        //This is for the old school buddylist file
        if($this->settings['enablebl'] == 1) {
            $result['contacts/'.$this->mac.'-directory.xml'] = 'contacts/$mac-directory.xml';
            $this->settings['presence'] = 1;
            foreach($this->settings['loops']['bl'] as $key => $data) {
                if(!empty($data['fname']) && !empty($data['bext'])) {
                    $this->settings['loops']['bl'][$key]['type'] = isset($this->settings['loops']['bl'][$key]['type']) ? $this->settings['loops']['bl'][$key]['type'] : '0';
                } else {
                    unset($this->settings['loops']['bl'][$key]);
                }
            }
        } else {
            $this->settings['presence'] = 0;
        }
        
        return $result;
    }

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();

        if (isset($this->settings['loops']['attendant'])) {
            foreach ($this->settings['loops']['attendant'] as $key => $data) {
                if ($this->settings['loops']['attendant'][$key]['ext'] == '') {
                    unset($this->settings['loops']['attendant'][$key]);
                }
            }
        }

        $this->protected_files = array('overrides/' . $this->mac . '-phone.cfg', 'logs/' . $this->mac . '-boot.log', 'logs/' . $this->mac . '-app.log', 'SoundPointIPLocalization', 'overrides/' . $this->mac . '-phone.cfg');
    }

}
