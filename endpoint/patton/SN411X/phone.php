<?php

/**
 * SN411X Modules Phone File
 *
 * @author Graeme Moss contact@dcdata.co.za
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_patton_SN411X_phone extends endpoint_patton_base {

    public $family_line = 'SN411X';
    

    function parse_lines_hook($line_data, $line_total) {
        
        $line = $line_data['line'];
        $line_data['fxsnum'] = $line - 1;

        return($line_data);
    }
    
    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();
        if (isset($this->settings['tone_set_data'])) {
            switch ($this->settings['tone_set_data']) {
                case "ToneSet_AR":
                     $this->settings['tone_set_data'] = "profile call-progress-tone defaultDialtone
  flush-play-list
  play 1 1000 425 -6

profile call-progress-tone defaultAlertingtone
  flush-play-list
  play 1 1000 425 -13
  pause 2 4000
  
profile call-progress-tone defaultBusytone
  flush-play-list
  play 1 300 425 -7
  pause 2 200

profile call-progress-tone defaultReleasetone
  flush-play-list
  play 1 300 425 -7
  pause 2 400

profile call-progress-tone defaultCongestiontone
  flush-play-list
  play 1 300 425 -7
  pause 2 400";
                     $this->settings['tone_set_data_profile'] = "use profile fxs etsi";
                    break;
                case "ToneSet_AU":
                     $this->settings['tone_set_data'] = "profile call-progress-tone defaultDialtone
  flush-play-list
  play 1 1000 425 -6 450 -6

profile call-progress-tone defaultAlertingtone
  flush-play-list
  play 1 400 425 -13 450 -13
  pause 2 200
  play 1 400 425 -13 450 -13
  pause 2 2000
  
profile call-progress-tone defaultBusytone
  flush-play-list
  play 1 375 425 -7
  pause 2 375

profile call-progress-tone defaultReleasetone
  flush-play-list
  play 1 375 425 -7
  pause 2 375

profile call-progress-tone defaultCongestiontone
  flush-play-list
  play 1 375 425 -7
  pause 2 375";
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_AT420":
                     $this->settings['tone_set_data'] = "profile call-progress-tone defaultDialtone
  flush-play-list
  play 1 1000 420 -6

profile call-progress-tone defaultAlertingtone
  flush-play-list
  play 1 1000 420 -13
  pause 2 5000
  
profile call-progress-tone defaultBusytone
  flush-play-list
  play 1 400 420 -7
  pause 2 400

profile call-progress-tone defaultReleasetone
  flush-play-list
  play 1 200 420 -7
  pause 2 200

profile call-progress-tone defaultCongestiontone
  flush-play-list
  play 1 200 420 -7
  pause 2 200";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_AT450":
                     $this->settings['tone_set_data'] = "profile call-progress-tone defaultDialtone
  flush-play-list
  play 1 1000 450 -6

profile call-progress-tone defaultAlertingtone
  flush-play-list
  play 1 1000 450 -13
  pause 2 5000
  
profile call-progress-tone defaultBusytone
  flush-play-list
  play 1 300 450 -7
  pause 2 300

profile call-progress-tone defaultReleasetone
  flush-play-list
  play 1 300 450 -7
  pause 2 300

profile call-progress-tone defaultCongestiontone
  flush-play-list
  play 1 300 450 -7
  pause 2 300";                     
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_BE":
                     $this->settings['tone_set_data'] = "profile call-progress-tone defaultDialtone
  flush-play-list
  play 1 1000 425 0

profile call-progress-tone defaultAlertingtone
  flush-play-list
  play 1 1000 425 -7
  pause 2 3000

profile call-progress-tone defaultBusytone
  flush-play-list
  play 1 500 425 -7
  pause 2 500

profile call-progress-tone defaultReleasetone
  flush-play-list
  play 1 170 425 -7
  pause 2 170

profile call-progress-tone defaultCongestiontone
  flush-play-list
  play 1 170 425 -7
  pause 2 170";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_BR":
                     $this->settings['tone_set_data'] = "profile call-progress-tone defaultDialtone
  flush-play-list
  play 1 1000 425 -6

profile call-progress-tone defaultAlertingtone
  flush-play-list
  play 1 1000 425 -7
  pause 2 4000
  
profile call-progress-tone defaultBusytone
  flush-play-list
  play 1 250 425 -7
  pause 2 250

profile call-progress-tone defaultReleasetone
  flush-play-list
  play 1 250 425 -7
  pause 2 250

profile call-progress-tone defaultCongestiontone
  flush-play-list
  play 1 250 425 -7
  pause 2 250";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_CY":
                     $this->settings['tone_set_data'] = "profile call-progress-tone defaultDialtone
  flush-play-list
  play 1 1000 350 -6 450 -6

profile call-progress-tone defaultAlertingtone
  flush-play-list
  play 1 1500 425 -13
  pause 2 3000
  
profile call-progress-tone defaultBusytone
  flush-play-list
  play 1 500 425 -7
  pause 2 500

profile call-progress-tone defaultReleasetone
  flush-play-list
  play 1 250 425 -7
  pause 2 250

profile call-progress-tone defaultCongestiontone
  flush-play-list
  play 1 250 425 -7
  pause 2 250";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_CZ":
                     $this->settings['tone_set_data'] = "profile call-progress-tone defaultDialtone
  flush-play-list
  play 1 330 425 -6
  pause 2 330
  play 3 660 425 -6
  pause 4 660
  
profile call-progress-tone defaultAlertingtone
  flush-play-list
  play 1 1000 425 -13
  pause 2 4000

profile call-progress-tone defaultBusytone
  flush-play-list
  play 1 330 425 -7
  pause 2 330

profile call-progress-tone defaultReleasetone
  flush-play-list
  play 1 165 425 -7
  pause 2 165

profile call-progress-tone defaultCongestiontone
  flush-play-list
  play 1 165 425 -7
  pause 2 165";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_DK":
                     $this->settings['tone_set_data'] = "profile call-progress-tone defaultDialtone
  flush-play-list
  play 1 1000 425 -6
  
profile call-progress-tone defaultAlertingtone
  flush-play-list
  play 1 1000 425 -7
  pause 2 4000

profile call-progress-tone defaultBusytone
  flush-play-list
  play 1 250 425 -7
  pause 2 250

profile call-progress-tone defaultReleasetone
  flush-play-list
  play 1 250 425 -7
  pause 2 250

profile call-progress-tone defaultCongestiontone
  flush-play-list
  play 1 250 425 -7
  pause 2 250";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_FI":
                     $this->settings['tone_set_data'] = "profile call-progress-tone defaultDialtone
  flush-play-list
  play 1 1000 425 -6
  
profile call-progress-tone defaultAlertingtone
  flush-play-list
  play 1 1000 425 -7
  pause 2 4000

profile call-progress-tone defaultBusytone
  flush-play-list
  play 1 300 425 -7
  pause 2 300

profile call-progress-tone defaultReleasetone
  flush-play-list
  play 1 200 425 -7
  pause 2 200

profile call-progress-tone defaultCongestiontone
  flush-play-list
  play 1 200 425 -7
  pause 2 200";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_FR":
                     $this->settings['tone_set_data'] = "profile call-progress-tone defaultDialtone
  flush-play-list
  play 1 1000 440 0

profile call-progress-tone defaultAlertingtone
  flush-play-list
  play 1 1500 440 -7
  pause 2 3500

profile call-progress-tone defaultBusytone
  flush-play-list
  play 1 500 440 -7
  pause 2 500

profile call-progress-tone defaultReleasetone
  flush-play-list
  play 1 250 425 -7
  pause 2 250

profile call-progress-tone defaultCongestiontone
  flush-play-list
  play 1 250 425 -7
  pause 2 250";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_DE":
                     $this->settings['tone_set_data'] = "profile call-progress-tone defaultDialtone
  flush-play-list
  play 1 1000 425 0

profile call-progress-tone defaultAlertingtone
  flush-play-list
  play 1 1000 425 -7
  pause 2 4000

profile call-progress-tone defaultBusytone
  flush-play-list
  play 1 480 425 -7
  pause 2 480

profile call-progress-tone defaultReleasetone
  flush-play-list
  play 1 240 425 -7
  pause 2 240

profile call-progress-tone defaultCongestiontone
  flush-play-list
  play 1 240 425 -7
  pause 2 240";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_GR":
                     $this->settings['tone_set_data'] = "profile call-progress-tone defaultDialtone
  play 1 200 425 0
  pause 2 300
  play 3 700 425 0
  pause 4 800

profile call-progress-tone defaultBusytone
  play 1 300 425 -7
  pause 2 300

profile call-progress-tone defaultReleasetone
  play 1 150 425 -7
  pause 2 150

profile call-progress-tone defaultCongestiontone
  play 1 150 425 -7
  pause 2 150";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_NL":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_IN":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_IE":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_IT":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_JP":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_NZ":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_NO":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_PL":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_PT":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_RU":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_ZA":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_ES":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_SE":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_CH":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_TR":
                     $this->settings['tone_set_data'] = "";                   
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_UK":
                     $this->settings['tone_set_data'] = "";                   
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                case "ToneSet_US":
                     $this->settings['tone_set_data'] = "";                    
                     $this->settings['tone_set_data_profile'] = "";
                    break;
                default:
                     $this->settings['tone_set_data'] = "";                   
                     $this->settings['tone_set_data_profile'] = "";
                    break;
            }
        } else {
             $this->settings['tone_set_data'] = "";                    
             $this->settings['tone_set_data_profile'] = "";
        }
    }
}