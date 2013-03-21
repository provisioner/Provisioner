<?php 

/**
 * Adapters take in desired settings for a phone from some other system and convert them into a standard form which we can use to generate config files.
 * In other words, some systems will send a SIP Proxy and some may send a SIP Registrar setting. This adapter will convert whatever gets sent from that
 * system into the format we need for provisioner.net, such as $settings['proxy'];
 *
 * This particular adapter is "dumb". It takes the data literally and, without conversion, uses it as your requested settings. All field names must match
 * the field names ultimately used by the particular vendor's phone configuration template and scripts.
 *
 * @author Francis Genet
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

class adapter_generic_adapter {
    public function get_config_manager($brand, $model, $arrConfig) {
        // Load the config manager
        $config_manager = new system_configfile();

        $config_manager->set_device_infos($brand, $model);

        $config_manager->set_settings($arrConfig);

        return $config_manager;
    }
}