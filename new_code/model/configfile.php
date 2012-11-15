<?php
// This represent the constant file
define("CONSTANTS_FILE", ROOT_PATH."/new_code/constants.json");

class ConfigFile {
    private $_strBrand = '';
    private $_strFamily = '';
    private $_strConfigFile = '';
    private $_strTemplateDir = '';
    private $_objTwig = null;
    private $_arrConstantes = array();
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

    public function get_config_file() {
        return $this->_strConfigFile;
    }

    public function get_template_dir() {
        return $this->_strTemplateDir;
    }

    // Setter
    public function set_brand($brand) {
        $this->_strBrand = $brand;
        $this->_set_template_dir();
    }

    public function set_family($family) {
        $this->_strFamily = $family;
        $this->_set_template_dir();
    }

    // This function will allow the user to set his own template directory
    public function set_template_dir($templateDir) {
        $this->_strTemplateDir = $templateDir;
    }

    // ===========================================

    public function __construct($mac, $ua) {
        // Load the constants
        $this->_load_constants();

        $this->_strBrand = $this->_get_brand_from_mac($mac);
        $this->_strFamily = $this->_get_family_from_ua($ua, $this->_strBrand);

        $this->_set_template_dir();

        // init twig object
        $this->_twig_init();
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

    // Load the constant file once and for all
    private function _load_constants() {
        $thid->_arrConstantes = json_decode(file_get_contents(CONSTANTS_FILE), true);
    }

    // This function will try to determine the brand from the mac address
    // TODO: This should send an email with the data if nothing is returned
    private function _get_brand_from_mac($mac) {
        $suffix = substr($mac, 0, 6);

        try {
            $brand = $this->_arrConstantes['mac_lookup'][$suffix];
            return $brand;
        } catch (Exception $e) {
            return false;
        }
    }

    // This funcrion will try to determine the family model from the ua and the brand
    private function _get_family_from_ua($ua, $brand) {
        switch ($brand) {
            case 'yealink':
                # code...
                break;
            
            default:
                # code....
                break;
        }
    }

    // This function will determine the template directory
    private function _set_template_dir() {
        $this->_strTemplateDir = MODULES_DIR . DIRECTORY_SEPARATOR . $this->_strBrand . DIRECTORY_SEPARATOR . $this->_strFamily . DIRECTORY_SEPARATOR;
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

    // Initialize Twig
    private function _twig_init() {
        $loader = new Twig_Loader_Filesystem($this->_strTemplateDir);
        $this->_objTwig = new Twig_Environment($loader);
    }

    // This function will select the right template to fill
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
        $obj MUST be a json object
        $obj will be decoded into an associative array
    */
    public function import_settings($obj) {
        array_push($this->_arrData, json_decode($obj, true));
    }

    // This is the final step
    public function generate_config_file() {
        $arrConfig = $this->_merge_config_objects();

        return $this->_objTwig->render($this->_strConfigFile, $arrConfig);
    }
}

?>