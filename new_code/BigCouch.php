<?php
require_once 'lib/php_on_couch/couch.php';
require_once 'lib/php_on_couch/couchClient.php';
require_once 'lib/php_on_couch/couchDocument.php';

class BigCouch {
    private $_server_url = null;

    /*
        Accessors
    */

    // Getters
    public function get_server_url() {
        return $this->_server_url;
    }

    // ===========================================

    // The server url must be like: http://my.couch.server.com
    public function __construct($server_url, $port = '5984') {
        if (strlen($server_url))
            $this->_server_url = $server_url . ':' . $port;
    }

    public function loadSettings($database, $document) {
        $couch_client = new couchClient($this->_server_url, $database);

        return $couch_client->asArray()->getDoc($document);
    }
}
