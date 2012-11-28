<?php

class ProvisionerUtils {
    public static function get_mac_address($ua, $uri) {
        if (preg_match("#[0-9a-fA-F]{2}(?=(:?))(?:\\1[0-9a-fA-F]{2}){5}#", $ua, $match_result))
            return $match_result[0];
        else 
            if (preg_match("#[0-9a-fA-F]{12}#", $uri, $match_result))
                return $match_result[0];
            else 
                return false;
    }

    // Will return the host and only that
    // No 'www.' and no port
    public static function get_provider($host_url) {
        $host = preg_replace("/^www\./", '', $host_url);
        $host = preg_replace("#:\d*$#", '', $host);
        return $host;
    }
}

?>