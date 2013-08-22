<?php

/**
 * Mocet 3xxx Provisioning System
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_mocet_3xxx_phone extends endpoint_mocet_base {
    public function __construct(&$config_manager) {
        parent::__construct($config_manager);
    }

    function prepareConfig() {
        parent::prepareConfig();

        $this->_set_timezone();
    }

    // /!\ For this kind of model, the TZ management is ugly
    private function _set_timezone() {
        $mocet_tz_lookup = json_decode(file_get_contents(MODULES_DIR . 'mocet/3xxx/tz.json'), true);
        $constants = $this->config_manager->get_constants();
        $settings = $this->config_manager->get_settings();

        // Yeah... right?
        $settings['timezone'] = $mocet_tz_lookup[$constants['timezone_lookup'][$settings['timezone']]];

        $this->config_manager->set_settings($settings);
    }
}

?>
