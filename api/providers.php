<?php 

class Providers {
    public $db;

    function __construct() {
        $this->db = new BigCouch('http://localhost');
    }

    
}

?>