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

    private static function strip_uri($uri) {
        // Then let's check in the URI (should be at the end of it)
        // Then explode the url
        $explode_uri = explode('/', $uri);
        $mac_index = sizeof($explode_uri) - 1;

        return $explode_uri[$mac_index];
    }

    public static function get_folder($brand, $model) {
        $base_folder = MODULES_DIR . $brand . "/";
        $brand_data = json_decode(file_get_contents($base_folder . "brand_data.json"), true);

        return $base_folder . $brand_data["folders"][$model];
    }

    // This function will determine weither the current request is a static file or not
    // This must be adapted since we now use it when we knoe the brand
    public static function is_static_file_request($ua, $uri, $model) {
        $folder = null;
        $target = null;

        // Polycom
        if (preg_match("/polycom/", $ua)) {
            $folder = ProvisionerUtils::get_folder("polycom", $model);

            if (!preg_match("/[a-z0-9_]*\.cfg$/", $uri, $match_result))
                $target = ProvisionerUtils::strip_uri($uri);
        }

        if (!$target)
            return false;
        else
            return $folder . $target;
    }
}

?>