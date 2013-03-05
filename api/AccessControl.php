<?php 

/**
 * This file contains The AccessControl class
 * It is used to determine wether or not you are allowed to access an API
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

class AccessControl implements iAuthenticate {

    public static $requires = 'user';
    public static $role = 'user';

    // The authentication is based on the current ip address of the API user
    // This is for now really basic, the user cannot have multiple authorized IP
    function __isAllowed() {
        $this->db = new BigCouch(DB_SERVER, DB_PORT);
        $host_ip = $_SERVER['REMOTE_ADDR'];
        $database = 'providers';

        $response = $this->db->getOneByKey($database, 'ip', $_SERVER['REMOTE_ADDR']);
        $access_type = isset($response['rows'][0]['value']['access_type']) ? $response['rows'][0]['value']['access_type'] : false;

        if (!$access_type) {
            return false;
        }

        static::$role = $access_type;
        return static::$requires == static::$role || static::$role == 'admin';
    }
}

?>
