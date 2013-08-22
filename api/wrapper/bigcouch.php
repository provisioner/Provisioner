<?php 

/**
 * This file contains The BigCouch wrapper
 * Everything relating to the database manipulation should be done here
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

require_once LIB_BASE . 'php_on_couch/couch.php';
require_once LIB_BASE . 'php_on_couch/couchClient.php';
require_once LIB_BASE . 'php_on_couch/couchDocument.php';
require_once LIB_BASE . 'KLogger.php';

class wrapper_bigcouch {
    private $_server_url;
    private $_couch_client;
    private $_settings;
    private $_log;

    // The server url must be like: http://my.couch.server.com
    public function __construct() {
        $this->_log = KLogger::instance(LOGS_BASE, Klogger::DEBUG);

        $this->_settings = helper_settings::get_instance();
        $this->_server_url = $this->_settings->database->url . ':' . $this->_settings->database->port;

        if (strlen($this->_settings->database->username) && strlen($this->_settings->database->password)) {
            $this->_server_url = str_replace('http://', '', $this->_server_url);
            $credentials = $this->_settings->database->username . ':' . $this->_settings->database->password . '@';
            $this->_server_url = 'http://' . $credentials . $this->_server_url;
        }
    }

    // Format a normal response
    private function _formatNormalResponse($response) {
        foreach ($response as $key => $value) {
            // No pvt_* and no _* 
            if (preg_match("/^(_|pvt_)/", $key))
                unset($response[$key]);
        }

        return $response;
    }

    // Format a view response
    private function _formatViewResponse($response) {
        $rows = $response['rows'];
        $return_value = array();
        foreach ($rows as $row) {
            // The id will be the key
            // TODO: allow the user to choose what must be the key
            $return_value[$row['value']['name']] = $row['value'];
        }

        return $return_value;
    }

    // Set the database for the current client
    private function _set_client($database) {
        $database = $this->_settings->db_prefix . $database;
        $this->_couch_client = new couchClient($this->_server_url, $database);
    }

    // Will retrieve a single document
    private function _getDoc($database, $document, $format = true) {
        $this->_set_client($database);

        try {
            $doc = $this->_couch_client->asArray()->getDoc($document);
        } catch (Exception $e) {
            return false;
        }

        // Do we want to filter or not?
        if ($format)
            return $this->_formatNormalResponse($doc);
        else
            return $doc;
    }

    public function getUuid($database) {
        $this->_set_client($database);
        return $this->_couch_client->getUuids(1)[0];
    }

    // Retrieve all the document for a specific db
    // /!\ not adapted to views
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

    public function createDatabase($db_name) {
        $this->_set_client($db_name);
        if (!$this->_couch_client->databaseExists()) {
            $this->_couch_client->createDatabase();
            return true;
        }

        return false;
    }

    // Retrieve all the document of a certain type and for a specific key
    // /!\ adapted to views and only
    public function getAllByKey($database, $document_type, $filter_key = null, $format = true) {
        $this->_set_client($database);

        try {
            if ($filter_key)
                $response = $this->_couch_client
                            ->startkey(array($filter_key))
                            ->endkey(array($filter_key, array()))
                            ->asArray()
                            ->getView($this->_settings->db_prefix . $database, "list_by_$document_type");
            else
                $response = $this->_couch_client
                            ->asArray()
                            ->getView($this->_settings->db_prefix . $database, "list_by_$document_type");

            if ($format)
                return $this->_formatViewResponse($response);
            else
                return $response;

        } catch (Exception $e) {
            return false;
        }
    }

    public function getOneByKey($database, $document_type, $filter_key) {
        $this->_set_client($database);

        try {
            return $this->_couch_client->asArray()->key($filter_key)->getView($this->_settings->db_prefix . $database, "list_by_$document_type");
        } catch (Exception $e) {
            return false;
        }
    }

    public function isDBexist($db) {
        $db = $this->_settings->db_prefix . $db;
        // I think it is better to create a new client instead of changing the current one
        $client = new couchClient($this->_server_url, $db);
        if ($client->databaseExists())
            return true;
        else 
            return false;
    }

    public function isDocExist($db, $document) {
        if ($this->_getDoc($db, $document, false))
            return true;
        else
            return false;
    }

    /*
        This will get a specific document
        The format argument is used when retrieving a raw doc or a filtered doc.
        By filtered I mean without the _* and all the pvt_*.
    */
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
    public function delete($database, $document = null) {
        // We are deleting a document;
        if ($document) {
            $doc = $this->_getDoc($database, $document, false);
            if ($doc) {
                try {
                    $this->_couch_client->deleteDoc((object)$doc);
                    return true;
                } catch (Exception $e) {
                    return false;
                }
            }
        } else { // We are deleting a database
            $this->_set_client($database);
            $this->_couch_client->deleteDatabase();
        }
    }

    // This function will be used for now only for the phones APIs
    // it is necessary because of the way that we are handling the parenting stuffs.
    public function deleteView($database, $brand, $family = null, $model = null) {
        $this->_set_client($database);

        // In the following code, we need to add a 'z' at the end of last element
        // of the endkey since it is a range
        if (!$family) {
            $startkey = array($brand);
            $endkey = array($brand.'z');
        }
        elseif (!$model) {
            $startkey = array($brand, $family);
            $endkey = array($brand, $family.'z');
        } else {
            $startkey = array($brand, $family, $model);
            $endkey = array($brand, $family, $model.'z');
        }

        $response = $this->_couch_client
                         ->startkey($startkey)
                         ->endkey($endkey)
                         ->asArray()
                         ->getView($this->_settings->db_prefix . $database, "list_by_all");

        foreach ($response['rows'] as  $row) {
            if ($this->delete($database, $row['id']))
                throw new RestException(500, "Error while deleting element");
        }
    }

    // will return an array of the requested document
    public function load_settings($database, $document, $just_settings = true) {
        $doc = null;
        $database = $this->_settings->db_prefix . $database;

        $this->_log->logInfo('- Entering load_settings -');
        $this->_log->logInfo("Retrieving the document $document...");
        $couch_client = new couchClient($this->_server_url, $database);
        $this->_log->logInfo('Couch client loaded!');

        try {
            $doc = $couch_client->asArray()->getDoc($document);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            $this->_log->logWarn("An error occured while retrieving the document ($document)");
            $this->_log->logWarn("Error: $error_message");
            return false;
        }

        // If the user just want the settings
        if ($just_settings) {
            $this->_log->logInfo('Retrieved the doc! will return only the settings');
            // This is ugly but still useful.
            // What if there is a doc but no settings?
            if (array_key_exists('settings', $doc)) {
                $settings = $doc['settings'];
                $this->_log->logInfo('Settings found... returning them');
                return $settings;
            }
                
        } else {
            $this->_log->logInfo('Retrieved the doc! will return the whole de doc');
            $this->_log->logInfo('Settings found... returning them');
            return $doc;
        }

        $this->_log->logWarn('Oops... something went obviously wrong when getting the doc');
        return false;
    }

    public function get_provider($provider_domain) {
        $database = $this->_settings->db_prefix . 'providers';

        $couch_client = new couchClient($this->_server_url, $database);
        
        try {
            $response = $couch_client->key($provider_domain)->asArray()->getView($database, 'list_by_domain');

            // Basically if the view return an element for the filtered request
            if (isset($response['rows'][0]['value']))
                return $response['rows'][0]['value'];
            else 
                return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function get_account_id($mac_address) {
        $database = $this->_settings->db_prefix . 'mac_lookup';
        $couch_client = new couchClient($this->_server_url, $database);

        try {
            $doc = $couch_client->asArray()->getDoc($mac_address);
        } catch (Exception $e) {
            return false;
        }

        if (isset($doc['account_id']))
            return $doc['account_id'];

        return false;
    }

    /*
        Prepare functions
        Those functions are necessary to format the data which are going to be send
    */

    // Add - phones
    public function prepareAddPhones($request_data, $document_name, $brand, $family = null, $model = null) {
        $request_data['_id'] = $document_name;

        if (!$family) {
            $type = 'brand';
            $request_data['brand'] = $brand;
        } elseif(!$model) {
            $type = 'family';
            $request_data['brand'] = $brand;
            $request_data['family'] = $family;
        } else {
            $type = 'model';
            $request_data['brand'] = $brand;
            $request_data['family'] = $family;
            $request_data['model'] = $model;
        }
        $request_data['pvt_type'] = $type;

        return $request_data;
    }

    // Add - providers
    public function prepareAddProviders($request_data) {
        $request_data['pvt_type'] = 'provider';
        return $request_data;
    }

    // Add - accounts
    public function prepareAddAccounts($request_data, $account_db, $account_id, $mac_address = null) {
        $finalObj = array();

        if ($mac_address) {
            // We first need to make sure that the database is created
            $brand = $request_data['provision']['endpoint_brand'];
            $family = $request_data['provision']['endpoint_family'];
            $model = $request_data['provision']['endpoint_model'];

            // Set a random local port
            $request_data['local_port'] = rand(4000, 65000);

            // Set random port for RTP
            $request_data['rtp_min_port'] = rand(5000, 65000);
            $request_data['rtp_max_port'] = $request_data['rtp_min_port'] + 10;
        }

        // A couple of unset for useless value coming from kazoo
        unset($request_data['available_apps']);
        unset($request_data['apps']);
        unset($request_data['billing_id']);

        $this->_set_client($account_db);
        if (!$this->_couch_client->databaseExists())
            $this->_couch_client->createDatabase();

        // Device
        if ($mac_address) {
            $finalObj['_id'] = $mac_address;
            $finalObj['brand'] = $brand;
            $finalObj['family'] = $family;
            $finalObj['model'] = $model;
            $finalObj['settings'] = $request_data;
        } else { // Account
            $finalObj['_id'] = $account_id;
            $finalObj['name'] = $request_data['name'];
            $finalObj['settings'] = $request_data;
            $finalObj['provider_id'] = $request_data['provider_id'];
        }

        return $finalObj;
    }
}
