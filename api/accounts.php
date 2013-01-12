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

    private $_FIELDS_ACCOUNT = array('settings', 'name', 'provider_id');
    private $_FIELDS_MAC = array('settings', 'brand', 'family', 'model');

    function __construct() {
        $this->db = new BigCouch(DB_SERVER, DB_PORT);
    }

    // Will return the formated account_id from the raw account_id
    private function _get_account_db($account_id) {
        // account/xx/xx/xxxxxxxxxxxxxxxx
        return "account/" . substr_replace(substr_replace($account_id, '/', 2, 0), '/', 5, 0);
    }

    /**
     * This will allow the user to get the default settings for an account and for a phone 
     *
     * @url GET /{account_id}/defaults
     * @url GET /{account_id}/{mac_address}
     * @access protected
     * @class  AccessControl {@requires user}
     */

    function retrieveDocument($account_id, $mac_address = null) {
        $account_db = $this->_get_account_db($account_id);

        // Retrieving the default settings for a user
        if (!$mac_address) {
            $default_settings = $this->db->get($account_db, $account_id);
            
            if ($default_settings && array_key_exists('settings', $default_settings))
                return $default_settings['settings'];
            else
                throw new RestException(404, 'This account_id do not exist or there are no default settings for this user');
        } else { // retrieving phone specific settings
            $mac_settings = $this->db->get($account_db, $mac_address);
            if (!$mac_settings && array_key_exists('settings', $mac_settings))
                return $mac_settings['settings'];
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
     * This function is used to add a logo
     *
     * @url POST /{account_id}/logo
     * @access protected
     * @class  AccessControl {@requires user}
     */
    function updateLogo($account_id, $request_data) {
        $error = $request_data['logo']['error'];
        $extension = substr($request_data['logo']['name'], -3);
        $size = $request_data['logo']['size'];
        $tmp_name = $request_data['logo']['tmp_name'];

        if ($error != UPLOAD_ERR_OK)
            throw new RestException(500, "An error occured while uploading the logo");

        $account_folder = "upload/" . $account_id;
        if (!opendir($account_folder)) {
            if (!mkdir(pathname($account_folder)))
                throw new RestException(500, "Could not create the account folder");
        }

        if (!$extension == 'dob')
            throw new RestException(400, "The logo must be a 'dob' file");

        // 1000000 B = 1MB
        if ($size > 1000000)
            throw new RestException(400, "The file is too big");

        if (!move_uploaded_file($tmp_name, $account_folder . '/logo.dob'))
            throw new RestException(400, "The logo could not be moved to his final destination");

        $account_db = $this->_get_account_db($account_id);
        $account_doc = $this->db->get($account_db, $account_id);

        $json_obj = json_decode($account_doc['settings'], true);

        return array('status' => true, 'message' => 'Logo uploaded and settings updated');
    }

    /**
     * This will allow the user to modify the account/phone settings
     *
     * @class  Auth {@requires user}
     * @url POST /{account_id}
     * @url POST /{account_id}/{mac_address}
     * @access protected
     * @class  AccessControl {@requires user}
     */

    function editDocument($account_id, $mac_address = null, $request_data = null) {
        $account_db = $this->_get_account_db($account_id);
        if (!$mac_address)
            $document_name = $account_id;
        else
            $document_name = $mac_address;
        
        foreach ($request_data as $key => $value) {
            if (!$this->db->update($account_db, $document_name, $key, $value))
                throw new RestException(500, 'Error while saving');
        }

        return array('status' => true, 'message' => 'Document successfully modified');
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
        $object_ready = $this->db->prepareAddAccounts($request_data, $account_db, $account_id, $mac_address);

        if (!$mac_address)
            Validator::validateAdd($object_ready, $this->_FIELDS_ACCOUNT);
        else
            Validator::validateAdd($object_ready, $this->_FIELDS_MAC);

        if(!$this->db->add($account_db, $object_ready))
            throw new RestException(500, 'Error while saving');
        else
            return array('status' => true, 'message' => 'Document successfully added');
    }

    /**
     * Delete the whole account or just a phone
     *
     * @url DELETE /{account_id}
     * @url DELETE /{account_id}/{mac_address}
     * @access protected
     * @class  AccessControl {@requires user}
     */

    function delDocument($account_id, $mac_address = null) {
        // making sure that the mac_address is well fornated
        $mac_address = strtolower(preg_replace('/-/', '', $mac_address));
        $account_db = $this->_get_account_db($account_id);

        if ($mac_address) {
            if (!$this->db->delete($account_db, $mac_address))
                throw new RestException(500, 'Error while deleting');
            else
                return array('status' => true, 'message' => 'Document successfully deleted');
        } else {
            $this->db->delete($account_db);
            return array('status' => true, 'message' => 'Account successfully deleted');
        }
    }
}
?>