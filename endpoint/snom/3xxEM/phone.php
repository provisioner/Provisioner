<?php
/**
 * Snom 320, 360, 370 with Exp.Module Provisioning System
 *
 * @author Corrado Mella
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_snom_3xxEM_phone extends endpoint_snom_base {

	public $family_line = '3xxEM';
        
        function prepare_for_generateconfig() {
            parent::prepare_for_generateconfig();
            $this->mac = strtolower($this->mac);
            
            if (!isset($this->settings['loops']['functionkey'])) {
                if (($this->engine == "asterisk") AND ($this->system == "unix")) {
                    exec($this->engine_location . " -rx 'database show SIP' | head -n-1 | cut -d ' ' -f 1 | cut -d '/' -f 4",$ext_list);
                    foreach ($ext_list as $key => $data) {
                        //$this->settings['ext_list']
                        $key++;
                        
                            $this->settings['loops']['functionkey'][$key]['context'] = "active";
                            $this->settings['loops']['functionkey'][$key]['label'] = $data;
                            if($key === 1 || $key === 2 ){
                                $this->settings['loops']['functionkey'][$key]['type'] = "line";
                            }else{
                                $this->settings['loops']['functionkey'][$key]['type'] = "dest";
                            }
                            $this->settings['loops']['functionkey'][$key]['value'] = $data;

                    }
                }
                
            }
        
        
        }
	
}
