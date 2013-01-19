<?PHP

/**
 * Polycom Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
abstract class endpoint_polycom_base extends endpoint_base {
    function prepareConfig($settings) {
        parent::prepareConfig($settings);

        return $settings;
    }
}