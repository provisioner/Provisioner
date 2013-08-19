<?php

/**
 * This file contains The BigCouch wrapper
 * (Almost) Everything relating to the database manipulation should be done here
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
    private $_server_url = null;
    private $_log = null;
    private $_settings = null;

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
        $this->_log = KLogger::instance(LOGS_BASE, Klogger::DEBUG);

        // Load the settings
        $objSettings = new helper_settings();
        $this->_settings = $objSettings->getSettings();

        if (strlen($server_url))
            $this->_server_url = $server_url . ':' . $port;

        if (strlen($this->_settings->database->username) && strlen($this->_settings->database->password)) {
            $this->_server_url = str_replace('http://', '', $this->_server_url);
            $credentials = $this->_settings->database->username . ':' . $this->_settings->database->password . '@';
            $this->_server_url = 'http://' . $credentials . $this->_server_url;
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
            $this->_log->logCrit('Exception while getting the provider info!');
            $this->_log->logDebug($e);
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
}
