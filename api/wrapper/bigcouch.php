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

    private function _formatNormalResponse($response) {
        foreach ($response as $key => $value) {
            if (preg_match("/^(_|pvt_)/", $key))
                unset($response[$key]);
        }

        return $response;
    }

    private function _formatViewResponse($response) {
        $rows = $response['rows'];
        $return_value = array();
        foreach ($rows as $row) {
            $return_value[$row['id']] = $row['value'];
        }

        return $return_value;
    }

    private function _set_client($database) {
        $this->_couch_client = new couchClient($this->_server_url, $database);
    }

    private function _getDoc($database, $document, $format = true) {
        $this->_set_client($database);

        try {
            $doc = $this->_couch_client->asArray()->getDoc($document);
        } catch (Exception $e) {
            return false;
        }

        if ($format)
            return $this->_formatNormalResponse($doc);
        else
            return $doc;
    }

    public function getAll($database) {
        $this->_set_client($database);

        try {
            $response = $this->_couch_client
                             ->asArray()
                             ->getAllDocs();

            return $this->_formatNormalResponse($response);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getAllByKey($database, $document_type, $filter_key = null) {
        $this->_set_client($database);

        try {
            if ($filter_key)
                $response = $this->_couch_client
                            ->startkey(array($filter_key))
                            ->endkey(array($filter_key, array()))
                            ->asArray()
                            ->getView($database, "list_by_$document_type");
            else
                $response = $this->_couch_client
                            ->asArray()
                            ->getView($database, "list_by_$document_type");

            return $this->_formatViewResponse($response);

        } catch (Exception $e) {
            return false;
        }
    }

    public function get($database, $document, $format = true) {
        return $this->_getDoc($database, $document, $format);
    }

    // Do I need to add a parameter specific for the name here?
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
        $doc = $this->_getDoc($database, $document, false);

        if ($doc) {
            try {
                $doc[$key] = $value;
                $this->_couch_client->storeDoc((object)$doc);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    // This will delete permanently the document
    public function delete($database, $document) {
        $doc = $this->_getDoc($database, $document, false);
        if ($doc) {
            try {
                $this->_couch_client->deleteDoc((object)$doc);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    /*
        prepare functions
    */

    public function prepareAddPhones($request_data, $document_name, $brand, $family = null, $model = null) {
        $request_data['_id'] = $document_name;

        if (!$family) {
            $type = 'brand';
            $name = $brand;
        } elseif(!$model) {
            $type = 'family';
            $name = $family;
        } else {
            $type = 'model';
            $name = $model;
        }
        $request_data['pvt_type'] = $type;
        $request_data['name'] = ucfirst($name);

        return $request_data;
    }
}

?>