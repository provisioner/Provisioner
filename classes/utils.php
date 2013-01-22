<?php

/**
 * This file contains utilities functions
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

class ProvisionerUtils {
    public static function get_mac_address($ua, $uri) {
        // Let's check in th001565000000e User-Agent
        if (preg_match("#[0-9a-fA-F]{2}(?=([:-]?))(?:\\1[0-9a-fA-F]{2}){5}#", $ua, $match_result))
            // need to return the mac address without the ':'
            return strtolower(preg_replace('/[:-]/', '', $match_result[0]));
        else 
            $requested_file = ProvisionerUtils::strip_uri($uri);

            if (preg_match("#[0-9a-fA-F]{12}#", $requested_file, $match_result))
                return strtolower($match_result[0]);
            else 
                return false;
    }

    // Will return the host and only that
    // No 'www.' and no port
    public static function get_provider_domain($http_host) {
        $host = preg_replace("/^www\./", '', $http_host);
        $host = preg_replace("#:\d*$#", '', $host);
        return $host;
    }

    // Will return the raw account_id from the URI
    public static function get_account_id($uri) {
        if (preg_match("#[0-9a-f]{32}#", $uri, $match_result))
            return $match_result[0];
        else
            return false;
    }

    // Will return the formated account_id from the raw account_id
    public static function get_account_db($account_id) {
        // account/xx/xx/xxxxxxxxxxxxxxxx
        return "account/" . substr_replace(substr_replace($account_id, '/', 2, 0), '/', 5, 0);
    }

    public static function strip_uri($uri) {
        // Then let's check in the URI (should be at the end of it)
        // Then explode the url
        $explode_uri = explode('/', $uri);
        $mac_index = sizeof($explode_uri) - 1;

        return $explode_uri[$mac_index];
    }

    private static function _get_brand_data($brand) {
        $base_folder = MODULES_DIR . $brand . "/";
        return json_decode(file_get_contents($base_folder . "brand_data.json"), true);
    }

    public static function get_folder($brand, $model) {
        $brand_data = ProvisionerUtils::_get_brand_data($brand);
        return $brand_data[$model]["folder"];
    }

    public static function get_file_list($brand, $model) {
        $brand_data = ProvisionerUtils::_get_brand_data($brand);
        return $brand_data[$model]["config_files"];
    }

    // This function will determine weither the current request is a static file or not
    public static function is_static_file($ua, $uri, $model) {
        $folder = null;
        $target = null;

        // Polycom
        if (preg_match("/polycom/", $ua)) {
            $folder = ProvisionerUtils::get_folder("polycom", $model);

            if (preg_match("/0{12}\.cfg$/", $uri))
                $target = "000000000000.cfg";
            elseif (!preg_match("/[a-z0-9_]*\.cfg$/", $uri, $match_result))
                $target = ProvisionerUtils::strip_uri($uri);
        }

        if (!$target)
            return false;
        else
            return $folder . $target;
    }

    public static function validate_arguments($argv) {
        // TODO
    }
}

?>