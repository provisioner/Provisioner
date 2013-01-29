<?php

abstract class endpoint_base {
    public function prepareConfig($settings, $config_manager) {
        return $settings;
    }
}

?>