<?php

/**
 * This file contains utilities functions
 *
 * @author Francis Genet
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

require_once LIB_BASE . 'KLogger.php';

class helper_utils {
    public static function get_mac_address($ua, $uri) {
        // Let's check in the User-Agent
        if (!preg_match("/linksys|cisco/i", $ua) && preg_match("#[0-9a-fA-F]{2}(?=([:-]?))(?:\\1[0-9a-fA-F]{2}){5}#", $ua, $match_result))
            // need to return the mac address without the ':'
            return strtolower(preg_replace('/[:-]/', '', $match_result[0]));
        else 
            $requested_file = helper_utils::strip_uri($uri);

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
        if (preg_match("#\/([0-9a-f]{32})\/#", $uri, $match_result))
            return $match_result[1];
        else
            return false;
    }

    // Will return the formated account_id from the raw account_id
    public static function get_account_db($account_id) {
        // making sure that $account_id is well formed
        if (preg_match("#[0-9a-f]{32}#", $account_id))
            // account/xx/xx/xxxxxxxxxxxxxxxx
            return "account/" . substr_replace(substr_replace($account_id, '/', 2, 0), '/', 5, 0);
        else 
            return false;
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
        $brand_data = helper_utils::_get_brand_data($brand);
        return $brand_data[$model]["folder"];
    }

    public static function get_file_list($brand, $model) {
        $files = json_decode(file_get_contents(MODULES_DIR . $brand . "/brand_data.json"), true);
        return $files[$model]["config_files"];
    }

    public static function get_regex_list($brand, $model) {
        $files = json_decode(file_get_contents(MODULES_DIR . $brand . "/brand_data.json"), true);
        return $files[$model]["regexs"];
    }

    // This function will determine weither the current request is a static file or not
    public static function is_static_file($ua, $uri, $model, $brand, $settings) {
        $log = KLogger::instance(LOGS_BASE, Klogger::DEBUG);

        $log->logInfo('- Entering static file function -');
        $folder = null;
        $target = null;
        $location = null;

        // Polycom
        if ($brand == "polycom") {
            $log->logInfo('Looking for Polycom...');
            $folder = helper_utils::get_folder("polycom", $model);
            $log->logDebug("Looking into folder: $folder");

            if (preg_match("/0{12}\.cfg$/", $uri)) {
                $log->logInfo('File is 000000000000.cfg...');
                $location = $settings->paths->endpoint . "polycom/000000000000.cfg";
            } elseif (!preg_match("/[a-z0-9_]*\.cfg$/", $uri)) {
                if (preg_match("/([0-9a-zA-Z\-_]*\.ld)$/", $uri, $match_result))
                    $location = $settings->paths->firmwares . $brand . "/" . $folder . "/firmware/" . $match_result[1];
                // This is if we have an account_id in the url
                elseif (preg_match("/(\/[0-9a-fA-F]{32}){0,1}(.*)$/", $uri, $match_result))
                    $location = $settings->paths->endpoint . $brand . "/" . $folder . $match_result[2];
            }
        }

        if (!$location)
            return false;
        else {
            $log->logInfo('Will redirect to :', $location);

            $location = 'Location: ' . $location;
            header($location);
            exit();
        }
    }

    public static function json_errors() {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return false;
            break;
            case JSON_ERROR_DEPTH:
                return ' - Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                return ' - Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                return ' - Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                return ' - Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                return ' - Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
            default:
                return ' - Unknown error';
            break;
        }
    }

    // This not from me
    public static function array_to_object($array) {
        $obj = new stdClass;
        foreach($array as $k => $v) {
            if(is_array($v)) {
                $obj->{$k} = helper_utils::array_to_object($v);
            } else {
                $obj->{$k} = $v;
            }
        }
        return $obj;
    }

    // Not from me either
    public static function object_to_array($data) {
        if (is_array($data) || is_object($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $result[$key] = helper_utils::object_to_array($value);
            }
            return $result;
        }
        return $data;
    }
}

?>