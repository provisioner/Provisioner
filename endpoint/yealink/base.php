<?php 

class endpoint_yealink_base extends endpoint_base {
    function prepareConfig($settings, $config_manager) {
        parent::prepareConfig($settings, $config_manager);

        return $settings;
    }
}

?>