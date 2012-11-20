<?php
// This represent the constant file
define("CONSTANTS_FILE", ROOT_PATH."/new_code/constants.json");

class ConfigFile {
    private $_strBrand = null;
    private $_strMac = null;
    private $_strFamily = null;
    private $_strConfigFile = null;
    private $_strTemplateDir = null;
    private $_strFirmVers = null;
    private $_objTwig = null;
    private $_arrConstants = null;
    private $_arrData = null;

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
    /*public function set_brand($brand) {
        $this->_strBrand = $brand;
        $this->_set_template_dir();
    }

    public function set_family($family) {
        $this->_strFamily = $family;
        $this->_set_template_dir();
    }*/

    // This function will allow the user to set his own template directory
    public function set_template_dir($templateDir) {
        $this->_strTemplateDir = $templateDir;
    }

    // ===========================================

    public function __construct($mac = null, $ua = null) {
        // This is the case of a HTTP request
        if ($mac && $ua) {
            // Load the constants
            if ($this->_load_constants()) {
                // Making sure that the mac is well formed: XXXXXXXXXXXX
                $this->_strMac = preg_replace('/:/', '', $mac);

                if ($this->_strMac) {
                    // Trying to detect the device informations
                    $this->_strBrand = $this->_get_brand_from_mac($mac);

                    if ($this->_strBrand)
                        $this->_get_family_from_ua($ua, $this->_strBrand);
                } else
                    return false;
                
                if ($this->_strBrand && $this->_strFamily)
                    $this->_set_template_dir();
                else
                    return false;

                // init twig object
                $this->_twig_init();
            } else 
                return false;
        }
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
            $brand = $this->_arrConstants['mac_lookup'][$suffix];
            return $brand;
        } catch (Exception $e) {
            return false;
        }
    }

    // This funcrion will try to determine the family model from the ua and the brand
    private function _get_family_from_ua($ua, $brand) {
        switch ($brand) {
            case 'yealink':
                if (preg_match('#Yealink SIP-[a-z](\d\d)[a-z] (\d*\.\d*\.\d*\.\d*) ((?:[0-9a-fA-F]{2}[:;.]?){6})#i', $ua, $elements)) {
                    // Setting the family
                    if ($elements[1] < 20)
                        $this->_strFamily = "t1x";
                    elseif ($elements[1] < 30 && $elements[1] >= 20)
                        $this->_strFamily = "t2x";
                    elseif ($elements[1] >= 30)
                        $this->_strFamily = "t3x";
                    else
                        return false;

                    // Setting the firmware version
                    $this->_strFirmVers = $elements[2];

                    // Checking the mac address
                    $elements[3] = preg_replace('/:/', '', $elements[3]);
                    if ($this->_strMac != $elements[3])
                        return false;

                    return true;
                } else
                    return false;

            default:
                return false;
        }
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

    // This function will merge all the json 
    private function _merge_config_objects() {
        $arrConfig = array();

        $arrConfig = $this->_arrData[0];
        for ($i=0; $i < (sizeof($this->_arrData)-1); $i++) { 
            $arrConfig = $this->_merge_array($arrConfig, $this->_arrData[i+1]);
        }

        return $arrConfig;
    }

    /*
        This function is used if you already have the brand and family info
        Or if you don't have the UA, like if you are using TFTP.
        This function require to declare the object without any parameters
        and then use this function:

        $obj = new ConfigFile();
        $obj-> set_device_infos('yealink', 't2x');
    */
    public function set_device_infos($brand, $family) {
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
    public function set_config_file($file) {
        // macaddr.cfg - 000000000000.cfg
        if (preg_match("/^([0-9a-f]{12})\.cfg$/i", $file))
            $this->_strConfigFile = "$mac.cfg";
        // y00000000000
        elseif (preg_match("/^y00000000000([0-9a-f]{1})\.cfg$/i", $file))
            $this->_strConfigFile = "y0000000000$suffix.cfg";
        else
            return false;
    }
    
    /* 
        This function will add a json object to merge with the other ones
        You should send first the object containing the more general infos
        and the more specific at the end
        $obj MUST be a json object (not yet decoded)
        $obj will be decoded into an associative array
    */
    public function import_settings($obj) {
        // TODO: need to check if array or json object
        array_push($this->_arrData, json_decode($obj, true));
    }

    // This is the final step
    public function generate_config_file() {
        $arrConfig = $this->_merge_config_objects();

        return $this->_objTwig->render($this->_strConfigFile, $arrConfig);
    }
}

?>