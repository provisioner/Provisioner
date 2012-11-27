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
}

?>