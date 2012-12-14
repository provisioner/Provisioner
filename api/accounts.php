<?php 

class Accounts {
    public $db;

    function __construct() {
        $this->db = new BigCouch(DB_SERVER);
    }

    // Will return the formated account_id from the raw account_id
    private function _get_account_db($account_id) {
        // account/xx/xx/xxxxxxxxxxxxxxxx
        return "account/" . substr_replace(substr_replace($account_id, '/', 2, 0), '/', 5, 0);
    }

    /**
     * 
     *
     * @url GET /{account_id}/defaults
     * @url GET /{account_id}/{mac_address}
     */

    function retrieveDocument($account_id, $mac_address = null) {
        $account_db = $this->_get_account_db($account_id);

        // Retrieving the default settings for a user
        if (!$mac_address) {
            $default_settings = $this->db->get($account_db, $account_id);
            if (!$default_settings && array_key_exists('settings', $default_settings))
                return $default_settings['settings'];
            else
                throw new RestException(200, 'This account_id do not exist or there are no default settings for this user');
        } else { // retrieving phone specific settings
            $mac_settings = $this->db->get($account_db, $mac_address);
            if (!$mac_settings && array_key_exists('settings', $mac_settings))
                return $mac_settings['settings'];
            else
                throw new RestException(200, 'There is no phone with this mac_address for this account or there are no specific settings for this phone');
        }
    }

    /**
     * Edit default settings for a user (just the settings)
     *
     * @url POST /{account_id}/defaults
     */

    function edit($account_id, $request_data = null) {
        $account_db = $this->_get_account_db($account_id);

        // This should force the user to send an object like {'settings': {}}
        if (!empty($request_data['settings'])) {
            foreach ($request_data as $key => $value) {
                $this->db->update($account_db, $account_id, $key);
            }
        }
    }

    /**
     * 
     *
     * @url POST /{account_id}
     * @url POST /{account_id}/{mac_address}
     */

    function editDocument($account_id, $mac_address = null, $request_data = null) {
        
    }

    /**
     * 
     *
     * @url PUT /
     * @url PUT /{account_id}/{mac_address}
     */

    function addDocument($account_id = null, $mac_address = null, $request_data = null) {
        $account_db = $this->_get_account_db($account_id);

        if (!$account_id && !$mac_address) {
            if (!$this->db->add($account_db, $request_data))
                return;
        } elseif ($account_id && $mac_address) 
            return;
        }
    }

    /**
     * 
     *
     * @url PUT /{account_id}/defaults
     * @url PUT /{account_id}/{mac_address}
     */
    function delDocument($account_id, $mac_address = null) {
        
    }
}

7fddae8e897711e0bc11003048c3b1f2
?>