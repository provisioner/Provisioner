<?php 

/**
 * Yealink Base File
 *
 * @author Andrew Nagy
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_yealink_base extends endpoint_base {
    public function __construct(&$config_manager) {
        parent::__construct($config_manager);
    }

    function prepareConfig() {
        parent::prepareConfig();

        $constants = $this->config_manager->get_constants();
        $settings = $this->config_manager->get_settings();

        if (array_key_exists('timezone', $settings))
            $settings['timezone'] = $constants['timezone_lookup'][$settings['timezone']];

        // Codecs
        if(isset($settings['media']['audio']['codecs'])) {
            foreach ($settings['media']['audio']['codecs'] as $codec) {
	            if ($codec == "G729")
	                $settings['codecs']['g729'] = true;
	            elseif ($codec == "PCMU")
	                $settings['codecs']['pcmu'] = true;
	            elseif ($codec == "PCMA")
	                $settings['codecs']['pcma'] = true;
	            elseif ($codec == "G722_16" || $codec == "G722_32") 
	                $settings['codecs']['g722'] = true;
	        }
		}

        if ($this->config_manager->get_request_type() == 'http') {
            $settings['provisioning_url'] = 'http://' . $this->config_manager->get_domain() . '/';
            $settings['directory_url'] = $settings['provisioning_url'] . 'directory/' . $this->config_manager->get_mac_address() . '/phonebook1.xml';
        }

        $this->config_manager->set_settings($settings);
    }
	
	public function setFilename($strFilename) {
		$settings = $this->config_manager->get_settings();
		$constants = $this->config_manager->get_constants();

        var_dump($this->config_manager->get_mac_address());

		$model = $this->config_manager->get_model();
		$strFilename = preg_replace('/\$suffix/', $constants['yealink']['suffixes'][$model], $strFilename);
		
		//Yealink likes lower case letters in its mac address
		$strFilename = preg_replace('/\$mac/', strtolower($this->config_manager->get_mac_address()), $strFilename);
		
		return $strFilename;
	}
}