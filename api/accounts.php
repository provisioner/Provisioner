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

    /*private $_FIELDS_ACCOUNT = array('settings', 'name', 'provider_id');
    private $_FIELDS_MAC = array('settings', 'brand', 'family', 'model');*/

    function __construct() {
        $this->db = new BigCouch(DB_SERVER, DB_PORT);
    }

    // Will return the formated account_id from the raw account_id
    private function _get_account_db($account_id) {
        // account/xx/xx/xxxxxxxxxxxxxxxx
        return "account/" . substr_replace(substr_replace($account_id, '/', 2, 0), '/', 5, 0);
    }

    // Yep...
    function options()
    {
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
            $default_settings = array();
            $default_settings['data'] = $this->db->get($account_db, $account_id);

            if (isset($default_settings['data']['settings']))
                return $default_settings;
            else
                throw new RestException(404, 'This account_id do not exist or there are no default settings for this user');
        } else { // retrieving phone specific settings
            $mac_settings = array();
            $mac_settings['data'] = $this->db->get($account_db, $mac_address);

            if (isset($mac_settings['data']['settings']))
                return $mac_settings;
            else
                throw new RestException(404, 'There is no phone with this mac_address for this account or there are no specific settings for this phone');
        }
    }

    /**
     * Edit default settings for a user (just the settings)
     *
     * @url POST /{account_id}/defaults
     * @access protected
     * @class  AccessControl {@requires user}
     */

    function edit($account_id, $request_data = null) {
        $account_db = $this->_get_account_db($account_id);

        // This should force the user to send an object like {'settings': {}}
        if (!isset($request_data['settings'])) {
            foreach ($request_data['settings'] as $key => $value) {
                $this->db->update($account_db, $account_id, $key);
            }
        } else
            throw new RestException(400, "settings is not well formed");

        return array('status' => true, 'message' => 'Settings successfully modified');
            
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
        if (!$mac_address)
            $document_name = $account_id;
        else {
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
            if (!$this->db->update($account_db, $document_name, $key, $value))
                throw new RestException(500, 'Error while saving');
        }

        if ($mac_address) {
            if (!$this->db->isDocExist('mac_lookup', $mac_address)) {
                $obj = array('_id' => $mac_address, 'account_id' => $account_id);
                if ($this->db->add('mac_lookup', $obj))
                    return array('status' => true, 'message' => 'Document successfully added');
            } else {
                if (!$this->db->update('mac_lookup', $mac_address, 'account_id', $account_id))
                    throw new RestException(500, 'Error while saving mac_lookup');
            }

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
        if (!$request_data)
            throw new RestException(400, "The body cannot be empty for this request");

        // making sure that the mac_address is well formated
        $mac_address = strtolower(preg_replace('/[:-]/', '', $mac_address));
        $account_db = $this->_get_account_db($account_id);

        if ($mac_address) {
            if (!$this->db->isDBexist($account_db))
                return array('status' => false, 'message' => 'The account do not exist yet');
        }

        $object_ready = $this->db->prepareAddAccounts($request_data, $account_db, $account_id, $mac_address);

        if(!$this->db->add($account_db, $object_ready))
            throw new RestException(500, 'Error while saving');
        else {
            if ($mac_address) {
                if (!$this->db->isDocExist('mac_lookup', $mac_address)) {
                    $obj = array('_id' => $mac_address, 'account_id' => $account_id);
                    if ($this->db->add('mac_lookup', $obj))
                        return array('status' => true, 'message' => 'Document successfully added');
                }
                return array('status' => false, 'message' => 'Could not create the mac_lookup document');

            } else
                return array('status' => true, 'message' => 'Document successfully added');
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
                // Let's check also if the device that we are trying to delete exist
                if ($this->db->isDBexist($account_db)) {
                    // First we delete the device document
                    if (!$this->db->delete($account_db, $mac_address))
                        throw new RestException(500, 'Error while deleting');
                    else {
                        // Then we delete the device in the mac_lookup db
                        if (!$this->db->delete('mac_lookup', $mac_address))
                            throw new RestException(500, 'Could not delete the lookup entry');

                        return array('status' => true, 'message' => 'Document successfully deleted');
                    }
                } else
                    throw new RestException(404, 'This device do not exist in this account');
            } else { // If we are trying to delete an account
                $doc_list = $this->db->getAll($account_db);
                // We get the document list inside of the account database
                foreach ($doc_list['row'] as $doc) {
                    // /!\ Ghetto hack following...
                    // We check the id of the document to know if it a device doc or the account doc
                    if preg_match("/^[a-f0-9]{12}$/i", $doc['id']) {
                        if (!$this->db->delete('mac_lookup', $doc['id']))
                            throw new RestException(500, 'Could not delete a lookup entry');
                    }
                }

                // And let's delete the account database then
                if ($this->db->delete($account_db))
                    return array('status' => true, 'message' => 'Account successfully deleted');
                else
                    throw new RestException(500, 'Could not delete the account database');
            }
        } else
            throw new RestException(404, 'This account do not exist');
    }
}
?>
