<?php 

require_once 'lib/php_on_couch/couch.php';
require_once 'lib/php_on_couch/couchClient.php';
require_once 'lib/php_on_couch/couchDocument.php';

class BigCouch {
    private $_server_url = null;
    private $_couch_client = null;

    // The server url must be like: http://my.couch.server.com
    public function __construct($server_url, $port = '5984') {
        if (strlen($server_url))
            $this->_server_url = $server_url . ':' . $port;
    }

    private function _set_client($database) {
        $this->_couch_client = new couchClient($this->_server_url, $database);
    }

    private function _getDoc($database, $document) {
        $this->_set_client($database);

        try {
            $doc = $this->_couch_client->getDoc($document);
        } catch (Exception $e) {
            return false;
        }

        return $doc;
    }

    public function getAll($database) {
        $this->_set_client($database);

        try {
            return $this->_couch_client
                        ->asArray()
                        ->getAllDocs();
        } catch (Exception $e) {
            return false;
        }
    }

    public function getAllByKey($database, $document_type, $filter_key) {
        $this->_set_client($database);

        try {
            if ($filter_key)
                return $this->_couch_client
                            ->include_docs(true)
                            ->startkey(array($filter_key))
                            ->endkey(array($filter_key, array()))
                            ->asArray()
                            ->getView($database, "list_by_$document_type");
            else
                return $this->_couch_client
                            ->include_docs(true)
                            ->asArray()
                            ->getView($database, "list_by_$document_type");

        } catch (Exception $e) {
            return false;
        }
    }

    public function get($database, $document) {
        return $this->_getDoc($database, $document);
    }

    public function add($database, $document) {
        $this->_set_client($database);
        if (is_array($document))
            $document = (object)$document;

        try {
            $this->_couch_client->storeDoc($document);
            return true;
        } catch (Exception $e) {
            return false;
        } 
    }

    // TODO: fix the needed parameters. 
    // It is a shame that the user need to enter the DB and the doc each time
    public function update($database, $document, $key, $value) {
        $doc = $this->_getDoc($database, $document);

        if ($doc) {
            try {
                $doc->$key = $value;
                $this->_couch_client->storeDoc($doc);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    // This will delete permanently the document
    public function delete($database, $document) {
        $doc = $this->_getDoc($database, $document);

        if ($doc) {
            try {
                $this->_couch_client->deleteDoc($doc);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }
}

?>