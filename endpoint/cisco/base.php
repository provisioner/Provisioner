<?PHP
/**
 * Cisco Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_cisco_base extends endpoint_base {
    function prepareConfig($settings, $config_manager) {
        parent::prepareConfig($settings, $config_manager);

        return $settings;
    }
}