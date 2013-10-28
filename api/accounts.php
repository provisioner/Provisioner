<?php 

/**
 * All methods in this class are protected
 * Accounts APIs
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

class Accounts {
    public $db;
    private $_log;

    /*private $_FIELDS_ACCOUNT = array('settings', 'name', 'provider_id');
    private $_FIELDS_MAC = array('settings', 'brand', 'family', 'model');*/

    function __construct() {
        // Le EPIC logger init
        $this->_log = KLogger::instance('logs', Klogger::DEBUG);

        $this->_log->logInfo('======================================================');
        $this->_log->logInfo('================== Starting process ==================');
        $this->_log->logInfo('======================================================');
        $this->_log->logDebug("Connecting to BigCouch...");
        $this->db = new BigCouch(DB_SERVER, DB_PORT);
    }

    // Will return the formated account_id from the raw account_id
    private function _get_account_db($account_id) {
        // account/xx/xx/xxxxxxxxxxxxxxxx
        return "account/" . substr_replace(substr_replace($account_id, '/', 2, 0), '/', 5, 0);
    }

    // Yep...
    function options() {
        return;
    }

    /**
     * This will allow the user to get the default settings for an account and for a phone 
     *
     * @url GET /{account_id}
     * @url GET /{account_id}/{mac_address}
     * @access protected
     * @class  AccessControl {@requires user}
     */

    function retrieveDocument($account_id, $mac_address = null) {
        $account_db = $this->_get_account_db($account_id);

        // Retrieving the default settings for a user
        if (!$mac_address) {
            $this->_log->logDebug(" - GET - retrieve account doc $account_id");
            $this->_log->logDebug("Request coming from " . $_SERVER['REMOTE_ADDR']);

            $default_settings = array();
            $default_settings['data'] = $this->db->get($account_db, $account_id);

            if (isset($default_settings['data']['settings']))
                return $default_settings;
            else {
                $this->_log->logDebug("No account with this id - EXIT");
                throw new RestException(404, 'This account_id do not exist or there are no default settings for this user');
            }
        } else { // retrieving phone specific settings
            $this->_log->logDebug(" - GET - retrieve account doc $account_id for mac doc $mac_address");
            $this->_log->logDebug("Request coming from " . $_SERVER['REMOTE_ADDR']);

            $mac_settings = array();
            $mac_settings['data'] = $this->db->get($account_db, $mac_address);

            if (isset($mac_settings['data']['settings']))
                return $mac_settings;
            else {
                $this->_log->logDebug("No doc for this mac address - EXIT");
                throw new RestException(404, 'There is no phone with this mac_address for this account or there are no specific settings for this phone');
            }
        }
    }
    
    /**
     * This will allow the user to modify the account/phone settings
     *
     * @url POST /{account_id}
     * @url POST /{account_id}/{mac_address}
     * @access protected
     * @class  AccessControl {@requires user}
     */

    function editDocument($account_id, $mac_address = null, $request_data = null) {
        $account_db = $this->_get_account_db($account_id);
        if (!$mac_address) {
            $this->_log->logDebug(" - POST - edit account doc $account_id");
            $this->_log->logDebug("Request coming from " . $_SERVER['REMOTE_ADDR']);
            $document_name = $account_id;
        }
        else {
            $this->_log->logDebug(" - POST - edit in doc $account_id mac address $mac_address");
            $this->_log->logDebug("Request coming from " . $_SERVER['REMOTE_ADDR']);

            $document_name = $mac_address;
            $current_doc = $this->db->get($account_db, $mac_address);

            if (isset($current_doc['settings']['local_port']))
                $request_data['settings']['local_port'] = $current_doc['settings']['local_port'];

            if (isset($request_data['settings']['provision'])) {
                // This update the brand/model/family if needed.
                $request_data['brand'] = $request_data['settings']['provision']['endpoint_brand'];
                $request_data['family'] = $request_data['settings']['provision']['endpoint_family'];
                $request_data['model'] = $request_data['settings']['provision']['endpoint_model'];
            }
        }
        
        foreach ($request_data as $key => $value) {
            if (!$this->db->update($account_db, $document_name, $key, $value)) {
                $this->_log->logDebug("Could not save key:$key - EXIT");
                throw new RestException(500, 'Error while saving');
            }
        }

        if ($mac_address) {
            $this->_log->logDebug("Will now edit the mac_lookup...");
            if (!$this->db->isDocExist('mac_lookup', $mac_address)) {
                $obj = array('_id' => $mac_address, 'account_id' => $account_id);
                if ($this->db->add('mac_lookup', $obj))
                    return array('status' => true, 'message' => 'Document successfully added');
            } else {
                if (!$this->db->update('mac_lookup', $mac_address, 'account_id', $account_id)) {
                    $this->_log->logDebug("Error... Edit for account $account_id and mac_address $mac_address FAIL");
                    throw new RestException(500, 'Error while saving mac_lookup');
                }
            }
            $this->_log->logDebug("done... Edit for account $account_id and mac_address $mac_address SUCCESS");

            return array('status' => true, 'message' => 'Document successfully added');

        } else
            return array('status' => true, 'message' => 'Document successfully added');
    }

    /**
     * This will allow the user to add an account or a phone
     *
     * @class  Auth {@requires user}
     * @url PUT /{account_id}
     * @url PUT /{account_id}/{mac_address}
     * @access protected
     * @class  AccessControl {@requires user}
     */

    function addDocument($account_id, $mac_address = null, $request_data = null) {
        $this->_log->logDebug(" - PUT - Adding account first...");
        $this->_log->logDebug("Request coming from " . $_SERVER['REMOTE_ADDR']);

        if (!$request_data) {
            $this->_log->logDebug("Empty body... Stopping here");
            throw new RestException(400, "The body cannot be empty for this request");
        }

        // making sure that the mac_address is well formated
        $mac_address = strtolower(preg_replace('/[:-]/', '', $mac_address));
        $account_db = $this->_get_account_db($account_id);

        if ($mac_address) {
            if (!$this->db->isDBexist($account_db)) {
                $this->_log->logDebug("Account $account_id do not exist and trying to add a mac address... EXIT");
                return array('status' => false, 'message' => 'The account do not exist yet');
            }
        }

        $object_ready = $this->db->prepareAddAccounts($request_data, $account_db, $account_id, $mac_address);

        if(!$this->db->add($account_db, $object_ready)) {
            $this->_log->logDebug("Fail to add the account... EXIT");
            throw new RestException(500, 'Error while saving');
        } else {
            if ($mac_address) {
                $this->_log->logDebug("Adding the device with mac_address $mac_address...");
                if (!$this->db->isDocExist('mac_lookup', $mac_address)) {
                    $this->_log->logDebug("The mac_lookup...");
                    $obj = array('_id' => $mac_address, 'account_id' => $account_id);
                    if ($this->db->add('mac_lookup', $obj)) {
                        $this->_log->logDebug("SUCCESS! exit...");
                        return array('status' => true, 'message' => 'Document successfully added');
                    }
                }
                $this->_log->logDebug("Could not add the mac_lookup entry... EXIT");
                return array('status' => false, 'message' => 'Could not create the mac_lookup document');

            } else {
                $this->_log->logDebug("Successfully Add account");
                return array('status' => true, 'message' => 'Document successfully added');
            }
        }
    }

    /**
     * Delete the whole account or just a phone
     *
     * @url DELETE /{account_id}
     * @url DELETE /{account_id}/{mac_address}
     * @access protected
     * @class  AccessControl {@requires admin}
     */

    function delDocument($account_id, $mac_address = null) {
        // making sure that the mac_address is well fornated
        $mac_address = strtolower(preg_replace('/-/', '', $mac_address));
        $account_db = $this->_get_account_db($account_id);

        // Let's first try of the account that we are trying to delete exist
        if ($this->db->isDBexist($account_db)) {
            // If we are trying to delete a device
            if ($mac_address) {
                $this->_log->logDebug(" - DELETE - Deleting a device ($mac_address) for account $account_id...");
                $this->_log->logDebug("Request coming from " . $_SERVER['REMOTE_ADDR']);
                // Let's check also if the device that we are trying to delete exist
                if ($this->db->isDocExist($account_db, $mac_address)) {
                    $this->_log->logDebug("The device does exist... let's delete it");
                    // First we delete the device document
                    if (!$this->db->delete($account_db, $mac_address)) {
                        $this->_log->logDebug("Could not delete the device doc ($mac_address) - EXIT");
                        throw new RestException(500, 'Error while deleting');
                    } else {
                        $this->_log->logDebug("Now deleting the mac lookup entry...");
                        // Then we delete the device in the mac_lookup db
                        if (!$this->db->delete('mac_lookup', $mac_address)) {
                            $this->_log->logDebug("Failed to delete the mac_lookup entry ($mac_address) - EXIT");
                            throw new RestException(500, 'Could not delete the lookup entry');
                        }

                        $this->_log->logDebug("Successfully deleted device ($mac_address)");
                        return array('status' => true, 'message' => 'Document successfully deleted');
                    }
                } else
                    throw new RestException(404, 'This device do not exist in this account');
            } else { // If we are trying to delete an account
                $this->_log->logDebug(" - DELETE - Deleting an account ($account_id)...");
                $this->_log->logDebug("Request coming from " . $_SERVER['REMOTE_ADDR']);

                $doc_list = $this->db->getAll($account_db);
                $this->_log->logDebug("Retrieved the device list now deleting them...");
                // We get the document list inside of the account database
                foreach ($doc_list['rows'] as $doc) {
                    // /!\ Ghetto hack following...
                    // We check the id of the document to know if it a device doc or the account doc
                    if (preg_match("/^[a-f0-9]{12}$/i", $doc['id'])) {
                        if (!$this->db->delete('mac_lookup', $doc['id'])) {
                            $this->_log->logDebug("Fail to delete the mac_lookup entry for" . $doc['id']);
                            throw new RestException(500, 'Could not delete a lookup entry');
                        }
                    }
                }

                $this->_log->logDebug("All devices for account $account_id deleted, will now delete the account db...");

                // And let's delete the account database then
                if ($this->db->delete($account_db)) {
                    $this->_log->logDebug("SUCCESS - exit");
                    return array('status' => true, 'message' => 'Account successfully deleted');
                } else {
                    $this->_log->logDebug("Failed to delete the account ($account_id) - EXIT");
                    throw new RestException(500, 'Could not delete the account database');
                }
            }
        } else {
            $this->_log->logDebug("The account ($account_id) do not exist");
            throw new RestException(404, 'This account do not exist');
        }
    }
}
?>
