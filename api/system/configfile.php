<?php

/**
 * Represent the config file class that will merge / load / return the requested config file
 *
 * @author Francis Genet
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

// This represent the constant file
define('CONSTANTS_FILE', ROOT_PATH.'/constants.json');

class system_configfile {
    // Device infos
    private $_strBrand = null;
    private $_strFamily = null;
    private $_strModel = null;

    // http or tftp
    private $_strRequestType = null;

    private $_strMac = null;
    private $_strDomain = null;
    private $_strConfigFile = null;
    private $_strTemplateDir = null;
    private $_objTwig = null;
    private $_arrConstants = array();
    private $_arrData = array();

    /*
        Accessors
    */

	/**
	* Get the Brand Name
	*
	* @author	frifri
	* @return	string	The Brand (yealink,cisco)
    */
    public function get_brand() {
        return $this->_strBrand;
    }

	/**
	* Get the Family Name
	*
	* @author	frifri
	* @return	string	The family (t2x, 79xx)
    */
    public function get_family() {
        return $this->_strFamily;
    }

	/**
	* Get the Model Name
	*
	* @author	frifri
	* @return	string	The model (T26)
    */
    public function get_model() {
        return $this->_strModel;
    }

    /**
    * Get the mac address
    *
    * @author   frifri
    * @return   string  The mac address
    */
    public function get_mac_address() {
        return $this->_strMac;
    }

    /**
    * Get the domain
    *
    * @author   frifri
    * @return   string  The domain
    */
    public function get_domain() {
        return $this->_strDomain;
    }


	/**
	* Get the Request Type (TFTP, HTTP)
	*
	* @author	frifri
	* @return	string	The request type (tftp, http)
    */
    public function get_request_type() {
        return $this->_strRequestType;
    }

	/**
	* Get the Configuration File
	*
	* @author	frifri
	* @return	string	The configuration file
    */
    public function get_config_file() {
        return $this->_strConfigFile;
    }

	/**
	* Get the template directory
	*
	* @author	frifri
	* @return	string	The template directory
    */
    public function get_template_dir() {
        return $this->_strTemplateDir;
    }

	/**
	* Get the constants (These shouldn't change!)
	*
	* @author	frifri
	* @return	array	The constants as an array
    */
    public function get_constants() {
        return $this->_arrConstants;
    }

	/**
	* Get the configuration file settings
	*
	* @author	frifri
	* @return	array	The settings as an array
    */
    public function get_settings() {
        return $this->_arrData;
    }

	/**
	* Set the Brand
	*
	* @author	frifri
	* @param	string	The Brand name (yealink, cisco)
    */
    public function set_brand($brand) {
        $this->_strBrand = $brand;
    }

	/**
	* Set the Family
	*
	* @author	frifri
	* @param	string	The family name (t2x)
    */
    public function set_family($family) {
        $this->_strFamily = $family;
    }

	/**
	* Set the Model
	*
	* @author	frifri
	* @param	string	The model name (t26)
    */
    public function set_model($model) {
        $this->_strModel = $model;
    }

    /**
    * Set the mac address
    *
    * @author   frifri
    * @param    string  The mac address
    */
    public function set_mac_address($mac_address) {
        $this->_strMac = $mac_address;
    }

    /**
    * Set the domain
    *
    * @author   frifri
    * @param    string  The domain
    */
    public function set_domain($domain) {
        $this->_strDomain = $domain;
    }

	/**
	* Set the request type
	*
	* @author	frifri
	* @param	string	The request type
    */
    public function set_request_type($requestType) {
        $this->_strRequestType = $requestType;
    }

	/**
	* Set the config file name
	*
	* @author	frifri
	* @param	string	the config file name
    */
    public function set_config_file($file) {
        $this->_strConfigFile = $file;
    }

	/**
	* Set the template directory
	*
	* This function will allow the user to set his own template directory
	*
	* @author	frifri
	* @param	string	The Brand name (yealink, cisco)
    */
    public function set_template_dir($templateDir) {
        $this->_strTemplateDir = $templateDir;
    }

	/**
	* Set the settings
    * /!\ This will erase all current settings in _arrData
    * Basicall
	*
	* @author	frifri
	* @param	array	    the settings array
    * @param    boolean     Will set the settings at key 0 or as value
    */
    public function set_settings($settings, $reset = true) {
        if (!$reset) {
            $this->_arrData = array();
            $this->_arrData[0] = $settings;
        } else
            $this->_arrData = $settings;
    }

    // ===========================================

    public function __construct() {
        $this->_load_constants();
    }

	/**
	* Load the constant file once and for all
	*
	* @author	frifri
	* @param	array	the settings array
    */
    private function _load_constants() {
        return $this->_arrConstants = json_decode(file_get_contents(CONSTANTS_FILE), true);
    }

	/**
	* Merge two arrays together to get one
	*
	*    This function will merge two array together to return only one.
	*    The first array must be the model. If some data from the second
	*    array are common with the first one, the datas from the first
	*    array will be overwritten
	*
    * @author   ?
	* @param	array	array #1
	* @param	array	array #2
	* @return	array	array #1 + #2
    */
    private function _merge_array($arr1, $arr2) {
        $keys = array_keys($arr2);

        foreach($keys as $key) {
            if(isset( $arr1[$key]) && is_array($arr1[$key]) && is_array($arr2[$key])) {
                $arr1[$key] = $this->_merge_array($arr1[$key], $arr2[$key]);
            } else {
                $arr1[$key] = $arr2[$key];
            }
        }
        return $arr1;
    }

	/**
	* This function will determine the template directory
	*
	* @author	frifri
    */
    private function _set_template_dir() {
        $folder = helper_utils::get_folder($this->_strBrand, $this->_strModel);
        $this->_strTemplateDir = MODULES_DIR . $this->_strBrand . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
    }

	/**
	* Initialize Twig
	*
	* @author	frifri
    */
    private function _twig_init() {
        $loader = new Twig_Loader_Filesystem($this->_strTemplateDir);
        $this->_objTwig = new Twig_Environment($loader);
    }

	/**
	* This function will merge all the json 
	*
	* @author	frifri
	* @return	array	The Fully Merged Array
    */
    public function get_merged_config_objects() {
        $arrConfig = array();

        $arrConfig = $this->_arrData[0];
        for ($i=0; $i < (sizeof($this->_arrData)-1); $i++)
            $arrConfig = $this->_merge_array($arrConfig, $this->_arrData[$i+1]);

        return $arrConfig;
    }

	/**
	* Set Device Information
	*
	*   This function is used if you already have the brand and family info
	*   Or if you don't have the UA, like if you are using TFTP.
	*   This function require to declare the object without any parameters
	*   and then use this function: 
	*
	* @author	frifri
	* @return	boolean	true if good.....true if bad
	* @example	$obj = new ConfigFile();
	*			$obj-> set_device_infos('polycom', '550');
    */
    public function set_device_infos($brand, $model) {
        $this->_strBrand = strtolower($brand);
        $this->_strModel = strtolower($model);
        $this->_strFamily = helper_utils::get_folder($brand, $model);

        return true;
    }

	/**
	* Import the Settings
	*
	*   This function will add a json object to merge with the other ones
	*   You should send first the object containing the more general infos
	*   and the more specific at the end
	*   $obj can be a json object (not yet decoded) or an array
	*   $obj will be decoded into an associative array if simple json object
	*
	* @author	frifri
	* @return	array	The Fully Merged Array
    */
    public function import_settings($obj) {
        if ($obj) {
            if (!is_array($obj))
                array_push($this->_arrData, json_decode($obj, true));
            else
                array_push($this->_arrData, $obj);
        }
    }
	
	/**
	* Generate ALL Configuration File
	*
	*   This generated the final configuration files as parsed for the ENTIRE phone
	*
	* @author	tm1000
    * @author   frifri
	* @return	array	The files as a key (filename) value (data) pair
    */
	public function generate_config_files() {
		$folder = helper_utils::get_folder($this->_strBrand, $this->_strModel);

        // Set the twig template directory
        // Not sure if that should be here
        $this->_set_template_dir();

        // init twig object
        $this->_twig_init();

        // Merging the settings
        $this->_arrData = $this->get_merged_config_objects();
		
        $target_phone = "endpoint_" . $this->_strBrand . "_" . $folder . "_phone";
        $phone = new $target_phone($this);
		$phone->prepareConfig();

	    foreach (helper_utils::get_file_list($this->_strBrand, $this->_strModel) as $value) {
            $filename = $phone->setFilename($value);

            if ($filename) {
                $file_content = $this->_objTwig->render($value, $this->_arrData);
                if (!file_put_contents(CONFIG_FILES_BASE . "/$filename", $file_content))
                    return false;
            }
	    }

        return true;
	}
}