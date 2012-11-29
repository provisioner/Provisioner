<?php
/**
 * CouchDB wrapper
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */

require_once LIB_BASE . 'php_on_couch/couch.php';
require_once LIB_BASE . 'php_on_couch/couchClient.php';
require_once LIB_BASE . 'php_on_couch/couchDocument.php';

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

    // will return an array of the requested document
    public function load_settings($database, $document, $just_settings = true) {
        $doc = null;
        $couch_client = new couchClient($this->_server_url, $database);

        try {
            $doc = $couch_client->asArray()->getDoc($document);
        } catch (Exception $e) {
            return array();
        }

        if (is_array($doc))
            if ($just_settings)
                return $doc['settings'];
            else
                return $doc;
        else
            return array();
    }

    public function get_provider($provider_domain) {
        $couch_client = new couchClient($this->_server_url, "providers");

        try {
            $response = $couch_client->asArray()->getView('providers', 'list_by_domain');
            
            // TODO: Improve this !!!           
            foreach ($response['rows'] as $doc) {
                if ($doc['key'] == $provider_domain) {
                    return $doc['value'];
                }
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /*public function get_account_from_ip($ip) {
        $couch_client = new couchClient($this->_server_url, "authorized_ips");

        try {
            $doc = $couch_client->asArray()->getDoc($ip);
            return $doc['account_id'];
        } catch (Exception $e) {
            return false;
        }
    }*/
}
