<?php

class ConfigFile {
    private $_strBrand = null;
    private $_strFamily = null;
    private $_strModel = null;
    private $_strConfigFile = null;
    private $_arrData = array();

    /*
        This function will merge two array together to return only one.
        The first array must be the model. If some data from the second
        array are common with the first one, the datas from the first
        array will be overwritten
    */
    private function merge_array($arr1, $arr2) {
        $keys = array_keys($arr2);
        foreach($keys as $key) {
            if(isset( $arr1[$key]) && is_array($arr1[$key]) && is_array($arr2[$key])) {
                $arr1[$key] = my_merge($arr1[$key], $arr2[$key]);
            } else {
                $arr1[$key] = $arr2[$key];
            }
        }
        return $arr1;
    }

    // This function will merge all the json 
    private function merge_config_objects() {
        $arrConfig = array();

        $arrConfig = $this->_arrData[0];
        for ($i=0; $i < (sizeof($this->_arrData)-1); $i++) { 
            $arrConfig = $this->merge_array($arrConfig, $this->_arrData[i+1]);
        }

        return $arrConfig;
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

    public function get_config_file() {
        $arrConfig = $this->merge_config_objects();

        return $twig->render($this->_strConfigFile, $arrConfig);
    }
}

?>