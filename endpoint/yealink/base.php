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
    public function __construct() {
        parent::__construct();
    }

    function prepareConfig(&$settings, $config_manager) {
        parent::prepareConfig($settings, $config_manager);

        $constants = $config_manager->get_constants();

        if (array_key_exists('timezone', $settings))
            $settings['timezone'] = $constants['timezone_lookup'][$settings['timezone']];

        // ContactList
        if ($config_manager->get_request_type() == 'http'){
            $settings['contact_list_url'] = $config_manager->get_current_provisioning_url() . "contactData1.xml";
        }

        // Codecs
        foreach ($settings['media']['audio']['codecs'] as $codec) {
            if ($codec == "G729")
                $settings['codecs']['g729'] = true;
            elseif ($codec == "PCMU")
                $settings['codecs']['pcmu'] = true;
            elseif ($codec == "PCMA")
                $settings['codecs']['pcma'] = true;
            elseif ($codec == "G722_16" || $codec == "G722_32")
                $settings['codecs']['g722'] = true;
        }
    }
}

?>