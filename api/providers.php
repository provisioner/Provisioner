<?php 

class Providers {
    public $db;

    function __construct() {
        $this->db = new BigCouch('http://localhost');
    }

    function get($provider_id) {
        
    }

    function post($provider_id, $request_data = null) {
        
    }

    function put($request_data = null) {
        
    }

    function delete($provider_id) {
        
    }
}

?>