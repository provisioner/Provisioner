<?php 

/**
 * Yealink Base File
 *
 * @author Andrew Nagy
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_yealink_base extends endpoint_base {
    function prepareConfig(&$settings, $config_manager) {
        parent::prepareConfig($settings, $config_manager);

        $constants = $config_manager->get_constants();

        if (array_key_exists('timezone', $settings))
            $settings['timezone'] = $constants['timezone_lookup'][$settings['timezone']];

        // ContactList
        if ($config_manager->get_request_type() == 'http'){
            if (preg_match("/^(.*\/)(.*\.[a-z]{3})$/", $_SERVER['REQUEST_URI'], $match))
                $settings['contact_list_url'] = "http://" . $_SERVER['HTTP_HOST'] . $match[1] . "contactData1.xml";
        }
    }
}

?>