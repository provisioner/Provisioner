<?php
/**
 * SPL Auto-loader
 *
 * @author Darren Schreiber
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class ProvisionerConfig {
    /**
     * Setup anything required to make our provisioner class work
     */
    public static function setup() {
        // Register auto-loader. When classes are requested that aren't loaded
        // It is possible to cumulate them.
        spl_autoload_register(array(
            'ProvisionerConfig',
            'wrapperAutoload'
        ));
    }

    public static function wrapperAutoload($class) {
        // If for some reason we get here and the class is already loaded, return
        if (class_exists($class, FALSE))
            return true;

        // Try to include the class
        $file = $class . '.php';
        if (is_file(WRAPPER_DIR . $file)) {
            require_once WRAPPER_DIR . $file;
            return true;
        }

        return false;
    }

    /*public static function endpointsAutoload($class) {		
        // If for some reason we get here and the class is already loaded, return
        if (class_exists($class, FALSE))
        {
            return TRUE;
        }

        // Try to include the class
        $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        if (is_file(PROVISIONER_BASE . $file)) {
            require_once(PROVISIONER_BASE . $file);

            return TRUE;
        }
        
        return FALSE;
    }*/
}

ProvisionerConfig::setup();
require_once 'simple_twig.php';
//require_once 'twig/lib/Twig/Autoloader.php';
//Twig_Autoloader::register();
