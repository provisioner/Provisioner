<?php 

/**
 * Adapters take in desired settings for a phone from some other system and convert them into a standard form which we can use to generate config files.
 * In other words, some systems will send a SIP Proxy and some may send a SIP Registrar setting. This adapter will convert whatever gets sent from that
 * system into the format we need for provisioner.net, such as $settings['proxy'];
 *
 * This particular adapter is "dumb". It takes the data literally and, without conversion, uses it as your requested settings. All field names must match
 * the field names ultimately used by the particular vendor's phone configuration template and scripts.
 *
 * @author Francis Genet
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

class adapter_generic_adapter {
	private $_objConfig_manager;
	private $_strBrand;
	private $_strModel;
	private	$_boolImport = false;
	
	/**
	* Flat File Constructor
	*
	* @author	tm1000
    * @param	string	$strType	Type of output server (tftp or http)
    */
	function __construct($strType = 'tftp') {
		$this->_objConfig_manager = new system_configfile();
		$this->_objConfig_manager->set_request_type($strType);
	}

	/**
	* Load a JSON File into the settings merger
	*
	* This will load a json file from the disk for settings
	*
	* @author	tm1000
    * @param	string	$strFile	The filename (with path) of the json file
	* @return	array	the merged settings in their final form
    */
	public function load_json($strFile) {
		if(!file_exists($strFile)) {
			throw new Exception('Json File Doesnt Exist');
		}
		$strJson = file_get_contents($strFile);
		$arrJson = json_decode($strJson,TRUE);
		$error = helper_utils::json_errors();
		if($error) {
			throw new Exception($error);
		}
		
		return $this->load_settings($arrJson);
	}
	
	/**
	* Load a Array of Settings into settings manager
	*
	* This will load/merge an array of settings into the manager
	*
	* @author	tm1000
	* @author 	frifri
    * @param	array	$arrSettings	The array with settings to merge
	* @return	array	the merged settings in their final form
    */
	public function load_settings($arrSettings) {
		if(!empty($arrSettings['brand'])) {
			$this->_strBrand = $arrSettings['brand'];
			unset($arrSettings['brand']);
		}
		
		if(!empty($arrSettings['model'])) {
			$this->_strModel = $arrSettings['model'];
			unset($arrSettings['model']);
		}

		if(!empty($arrSettings['mac'])) {
			$this->_objConfig_manager->set_mac_address($arrSettings['mac']);
		}
		
		if(!$this->_boolImport) {
			$this->_objConfig_manager->set_settings($arrSettings,false);
			$this->_boolImport = true;
		} else {
			$this->_objConfig_manager->import_settings($arrSettings);
		}
		
		
		return $this->_objConfig_manager->get_settings();
	}
	
	/**
	* Get the Config Manager Object
	*
	* This will return the config_manager object used for processing
	*
	* @author	tm1000
	* @return	object	The config_manager object
    */
    public function get_config_manager() {
        // Load the config manager		
		if(empty($this->_strModel) || empty($this->_strBrand)) {
			throw new Exception('Model or Brand is Empty');
		}
		
        $this->_objConfig_manager->set_device_infos($this->_strBrand, $this->_strModel);

        return $this->_objConfig_manager;
    }
}