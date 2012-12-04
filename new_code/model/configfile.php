<?php
/**
 * Represent the config file class that will merge / load / return the requested config file
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */

// This represent the constant file
define('CONSTANTS_FILE', ROOT_PATH.'/new_code/constants.json');

class ConfigFile {
    // Device infos
    private $_strBrand = null;
    private $_strFamily = null;
    private $_strModel = null; // Not used

    private $_strMac = null;
    private $_strConfigFile = null;
    private $_strTemplateDir = null;
    private $_strFirmVers = null; // Not used
    private $_objTwig = null;
    private $_arrConstants = array();
    private $_arrData = array();

    /*
        Accessors
    */

    // Getters
    public function get_brand() {
        return $this->_strBrand;
    }

    public function get_family() {
        return $this->_strFamily;
    }

    // This thing is useless for now
    public function get_model() {
        return $this->_strModel;
    }

    public function get_firmware_version() {
        return $this->_strFirmVers;
    }

    public function get_config_file() {
        return $this->_strConfigFile;
    }

    public function get_template_dir() {
        return $this->_strTemplateDir;
    }

    // Setter
    public function set_brand($brand) {
        $this->_strBrand = $brand;
    }

    public function set_family($family) {
        $this->_strFamily = $family;
    }

    // This function will allow the user to set his own template directory
    public function set_template_dir($templateDir) {
        $this->_strTemplateDir = $templateDir;
    }

    // ===========================================

    public function __construct() {
        $this->_load_constants();
    }

    // Load the constant file once and for all
    private function _load_constants() {
        return $this->_arrConstants = json_decode(file_get_contents(CONSTANTS_FILE), true);
    }

    /*
        This function will merge two array together to return only one.
        The first array must be the model. If some data from the second
        array are common with the first one, the datas from the first
        array will be overwritten
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

    // This function will try to determine the brand from the mac address
    // TODO: This should send an email with the data if nothing is returned
    private function _get_brand_from_mac() {
        $suffix = substr($this->_strMac, 0, 6);

        try {
            if (array_key_exists($suffix, $this->_arrConstants['mac_lookup'])) {
                $this->_strBrand = $this->_arrConstants['mac_lookup'][$suffix];
                return true;
            } else 
                return false;
        } catch (Exception $e) {
            return false;
        }
    }

    // This funcrion will try to determine the family model from the ua and the brand
    private function _get_family_from_ua($ua) {
        switch ($this->_strBrand) {
            case 'yealink':
                if (preg_match('#Yealink SIP-[a-z](\d\d)[a-z] (\d*\.\d*\.\d*\.\d*) ((?:[0-9a-fA-F]{2}[:;.]?){6})#i', $ua, $elements)) {
                    // Set the family
                    if ($elements[1] < 20)
                        $this->_strFamily = 't1x';
                    elseif ($elements[1] < 30 && $elements[1] >= 20)
                        $this->_strFamily = 't2x';
                    elseif ($elements[1] >= 30)
                        $this->_strFamily = 't3x';
                    else
                        return false;

                    // Set the firmware version
                    $this->_strFirmVers = $elements[2];

                    // Checking the mac address
                    $elements[3] = strtolower(preg_replace('/:/', '', $elements[3]));
                    if ($this->_strMac != $elements[3])
                        return false;

                    return true;
                } else
                    return false;
            case 'aastra':
                if (preg_match('#Aastra(\d*\w.) MAC:((?:[0-9a-fA-F]{2}-?){6}) V:(\d*\.\d*\.\d*\.\d*)#i', $ua, $elements)) {
                    // Set the family. this is harcoded for now.
                    $this->_strFamily = 'aap9xxx6xxx';

                    // Set the firmware version
                    $this->_strFirmVers = $elements[3];

                    // Set the mac address
                    $elements[2] = strtolower(preg_replace('/-/', '', $elements[2]));
                    if ($this->_strMac != $elements[2])
                        return false;
                }
            default:
                return false;
        }
    }

    // This function will merge all the json 
    private function _merge_config_objects() {
        $arrConfig = array();

        $arrConfig = $this->_arrData[0];
        for ($i=0; $i < (sizeof($this->_arrData)-1); $i++)
            $arrConfig = $this->_merge_array($arrConfig, $this->_arrData[$i+1]);

        return $arrConfig;
    }

    // This function will determine the template directory
    private function _set_template_dir() {
        $this->_strTemplateDir = MODULES_DIR . DIRECTORY_SEPARATOR . $this->_strBrand . DIRECTORY_SEPARATOR . $this->_strFamily . DIRECTORY_SEPARATOR;
    }

    // Initialize Twig
    private function _twig_init() {
        $loader = new Twig_Loader_Filesystem($this->_strTemplateDir);
        $this->_objTwig = new Twig_Environment($loader);
    }

    // Will try to detect the phone information
    public function detect_phone_info($mac, $ua) {
        $this->_strMac = preg_replace('/[:\-]/', '', $mac);
        if ($this->_get_brand_from_mac())
            if($this->_get_family_from_ua($ua))
                return true;

        return false;
    }

    /*
        This function is used if you already have the brand and family info
        Or if you don't have the UA, like if you are using TFTP.
        This function require to declare the object without any parameters
        and then use this function:

        $obj = new ConfigFile();
        $obj-> set_device_infos('yealink', 't2x');
    */
    public function set_device_infos($brand, $family, $model = '') {
        $this->_strBrand = strtolower($brand);
        $this->_strFamily = strtolower($family);

        if ($this->_strBrand && $this->_strFamily)
            $this->_set_template_dir();
        else
            return false;

        // init twig object
        $this->_twig_init();
    }

    // This function will select the right template to file
    public function set_config_file($uri) {
        switch ($this->_strBrand) {
            case 'yealink':
                // macaddr.cfg - 000000000000.cfg
                if (preg_match("/([0-9a-f]{12})\.cfg$/", $uri))
                    $this->_strConfigFile = "\$mac.cfg";
                // y00000000000
                elseif (preg_match("/y00000000000([0-9a-f]{1})\.cfg$/", $uri))
                    $this->_strConfigFile = "y0000000000\$suffix.cfg";
                else
                    return false;
            case 'aastra':
                // macaddr.cfg - 000000000000.cfg
                if (preg_match("/([0-9a-f]{12})\.cfg$/", $uri))
                    $this->_strConfigFile = "\$mac.cfg";
                // This one is pretty obvious no?
                elseif (preg_match("/(aastra\.cfg)$/", $uri))
                    $this->_strConfigFile = "aastra.cfg";
                else
                    return false;
            default:
                return false;
        }
    }
    
    /* 
        This function will add a json object to merge with the other ones
        You should send first the object containing the more general infos
        and the more specific at the end
        $obj can be a json object (not yet decoded) or an array
        $obj will be decoded into an associative array if simple json object
    */
    public function import_settings($obj) {
        if (!is_array($obj))
            array_push($this->_arrData, json_decode($obj, true));
        else
            array_push($this->_arrData, $obj);
    }

    // This is the final step
    public function generate_config_file() {
        $arrConfig = $this->_merge_config_objects();

        // Set the twig template directory
        $this->_set_template_dir();
        // init twig object
        $this->_twig_init();

        if ($this->_objTwig)
            return $this->_objTwig->render($this->_strConfigFile, $arrConfig);
    }
}

?>