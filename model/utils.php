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
            // need to return the mac address without the ':''
            return strtolower(preg_replace('/[:-]/', '', $match_result[0]));
        else 
            // Then let's check in the URI (should be at the end of it)
            // Then explode the url
            $explode_uri = explode('/', $uri);
            $mac_index = sizeof($explode_uri) - 1;

            if (preg_match("#[0-9a-fA-F]{12}#", $explode_uri[$mac_index], $match_result))
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

    public static function is_generic_polycom_request($ua, $uri) {
        if (preg_match("/polycom/", $ua)) {
            if (preg_match("/0{12}\.cfg$/", $uri, $match_result))
                return $match_result[0];

            // This should be dynamic
            if (preg_match("/common.cfg$/", $uri, $match_result))
                return "common.cfg";
        }

        return false;
    }
}

?>