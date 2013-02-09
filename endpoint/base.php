<?php

/**
 * Base File
 *
 * @author Andrew Nagy
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */

require_once "simple_twig.php";

abstract class endpoint_base {
    private $_objTwig = null;

    // Initialize Twig
    private function _twig_init() {
        $loader = new Twig_Loader_Filesystem("master_json/");
        $this->_objTwig = new Twig_Environment($loader);
    }

    protected function encode_config(&$settings, $config_manager) {
        $template_file = "master_" . $config_manager->get_brand() . "_" . $config_manager->get_family() . ".json";

        if ($this->_objTwig)
            // /!\ The result is a StdClass, not an array
            $settings = json_decode($this->_objTwig->render($template_file, $settings));
        else
            die();
    }

    public function __construct() {
        $this->_twig_init();
    }

    public function prepareConfig(&$settings, $config_manager) {
        
    }
}

?>