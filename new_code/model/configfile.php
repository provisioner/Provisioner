<?php

class ConfigFile {
    private $_strBrand = '';
    private $_strFamily = '';
    private $_strModel = '';
    private $_strConfigFile = '';
    private $_strTemplateDir = '';
    private $_objTwig = null;
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

    public function get_model() {
        return $this->_strModel;
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

    public function set_model($model) {
        $this->_strModel = $model;
    }

    public function set_template_dir($templateDir) {
        $this->_strTemplateDir = $templateDir;
    }

    // ===========================================

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
    public function setConfigFile($file) {
        // macaddr.cfg - 000000000000.cfg
        if (preg_match("/^([0-9a-f]{12})\.cfg$/i", $mac))
            $this->_strConfigFile = "$mac.cfg";
        // y00000000000
        elseif (preg_match("/^y00000000000([0-9a-f]{1})\.cfg$/i", $mac))
            $this->_strConfigFile = "y0000000000$suffix.cfg";
        else
            return false;
    }

    // Following line used if trying to detect family
    //public function __construct($brand, $model, $family = "") {
    public function __construct($brand, $family, $model) {
        $this->_strBrand = $brand;
        $this->_strModel = $model;
        // TODO: try to detect the family
        $this->_strFamily = $family;

        $this->_strTemplateDir = MODULES_DIR . DIRECTORY_SEPARATOR . $this->_strBrand . DIRECTORY_SEPARATOR . $this->_strFamily . DIRECTORY_SEPARATOR;

        // init twig object
        $this->_twig_init();
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

    public function generate_config_file() {
        $arrConfig = $this->_merge_config_objects();

        return $this->_objTwig->render($this->_strConfigFile, $arrConfig);
    }
}

?>