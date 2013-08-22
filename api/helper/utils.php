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
	/**
	* Extract the mac address from a User Agent or URI
	*
	* @author	frifri
    * @param	string	$ua	User Agent
	* @param	string	$uri	URI
	* @return	mixed	The Extracted Address or False
    */
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

	/**
	* Will return the host and only that
	*
	* Removes 'www.' and port
	*
	* @author	frifri
    * @param	string	$http_host	The HTTP Host
	* @return	string	HTTP HOST without www. and port
    */
    public static function get_provider_domain($http_host) {
        $host = preg_replace("#:\d*$#", '', preg_replace("/^www\./", '', $http_host));
        return $host;
    }

	/**
	* Will return the raw account_id from the URI
	*
	* @author	frifri
    * @param	string	$uri	The URI
	* @return	mixed	The account ID
    */
    public static function get_account_id($uri) {
        if (preg_match("#\/([0-9a-f]{32})\/#", $uri, $match_result))
            return $match_result[1];
        else
            return false;
    }

	/**
	* Will return the formatted account_id from the raw account_id
	*
	* @author	frifri
    * @param	string	$account_id	The Raw Account ID
	* @return	mixed	formatted account_id
    */
    public static function get_account_db($account_id) {
        // making sure that $account_id is well formed
        if (preg_match("#[0-9a-f]{32}#", $account_id))
            // account/xx/xx/xxxxxxxxxxxxxxxx
            return "account/" . substr_replace(substr_replace($account_id, '/', 2, 0), '/', 5, 0);
        else 
            return false;
    }

	/**
	* Get the Brand extra data from the brand json file
	*
	* @author	frifri
    * @param	string	$brand	The brand name (yealink, cisco, polycom)
	* @return	$array	the decoded settings from inside brand_data.json
    */
    private static function _get_brand_data($brand) {
        $base_folder = MODULES_DIR . $brand . "/";
		if(!file_exists($base_folder . "brand_data.json"))
			throw new Exception('Missing:'.$base_folder . "brand_data.json");
        return json_decode(file_get_contents($base_folder . "brand_data.json"), true);
    }

	/**
	* Get the family folder for a brand and model
	*
	* @author	frifri
    * @param	string	$brand	The brand name (yealink, cisco, polycom)
	* @param	string	$model	The model name (t26, 7960, 501)
	* @return	string	the folder location
    */
    public static function get_folder($brand, $model) {
        $brand_data = helper_utils::_get_brand_data($brand);
        return $brand_data[$model]["folder"];
    }

	/**
	* Get List of Configuration files to be generated
	*
	* @author	frifri
    * @param	string	$brand	The brand name (yealink, cisco, polycom)
	* @param	string	$model	The model name (t26, 7960, 501)
	* @return	array	the configuration files that need to be generated
    */
    public static function get_file_list($brand, $model) {
        $files = json_decode(file_get_contents(MODULES_DIR . $brand . "/brand_data.json"), true);
        return $files[$model]["config_files"];
    }

	/**
	* Get the Regular Expressions for matching web requests
	*
	* @author	frifri
    * @param	string	$brand	The brand name (yealink, cisco, polycom)
	* @param	string	$model	The model name (t26, 7960, 501)
	* @return	array	the list of regular expressions
    */
    public static function get_regex_list($brand, $model) {
        $files = json_decode(file_get_contents(MODULES_DIR . $brand . "/brand_data.json"), true);
        return $files[$model]["regexs"];
    }

	/**
	* Get The JSON Error code (IF any) of preceding json_decode command
	*
	* @author	tm1000
	* @return	string	the error code
    */
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

	/**
	* Get the Regular Expressions for matching web requests
	*
	* @author	?
    * @param	array	$array       An array
	* @return	object	the object that was an array
    */
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

	/**
	* Get the Regular Expressions for matching web requests
	*
	* @author	?
    * @param	object	$data       An object?
	* @return	array	the array that was an object
    */
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
