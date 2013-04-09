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
        spl_autoload_register(array(
            'ProvisionerConfig',
            'autoload'
        ));
    }

    public static function autoload($class) {		
        // If for some reason we get here and the class is already loaded, return
        if (class_exists($class, FALSE)) {
            return TRUE;
        }

        // Try to include the class
        $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        if (is_file(PROVISIONER_BASE . $file)) {
            require_once(PROVISIONER_BASE . $file);

            return TRUE;
        }
        
        return FALSE;
    }
}

ProvisionerConfig::setup();
require_once 'simple_twig.php';