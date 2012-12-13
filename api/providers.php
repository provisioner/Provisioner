<?php 

class Providers {
    public $db;

    function __construct() {
        $this->db = new BigCouch('http://localhost');
    }

    function get($provider_id) {
        $provider = $this->db->get('providers', $provider_id);

        if ($provider)
            return $provider;
        else
            throw new Exception(200, 'No information for this provider');
    }

    function post($provider_id, $request_data = null) {
        
    }

    function put($request_data = null) {
        
    }

    function delete($provider_id) {
        
    }
}

?>