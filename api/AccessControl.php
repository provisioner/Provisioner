<?php 

class AccessControl implements iAuthenticate {

    public static $requires = 'user';
    public static $role = 'user';

    // The authentication is based on the current ip address of the API user
    // This is for now really basic, the user cannot have multiple authorized IP
    function __isAllowed() {
        $this->db = new BigCouch(DB_SERVER, DB_PORT);
        $host_ip = $_SERVER['REMOTE_ADDR'];

        $response = $this->db->getOneByKey('providers', 'ip', $_SERVER['REMOTE_ADDR']);
        $access_type = isset($response['rows'][0]['value']['access_type']) ? $response['rows'][0]['value']['access_type'] : false;

        if (!$access_type) {
            return false;
        }

        static::$role = $access_type;
        return static::$requires == static::$role || static::$role == 'admin';
    }
}

?>