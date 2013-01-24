<?php 

/**
 * This file manage all the APIs for the providers
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

class Providers {
    public $db;

    private $_FIELDS = array('name', 'settings', 'domain', 'default_account_id', 'authorized_ip');

    function __construct() {
        $this->db = new BigCouch(DB_SERVER, DB_PORT);
    }

    /**
     *  This is the function that will allow the administrator to retrieve all the providers
     *
     * @url GET /
     * @access protected
     * @class  AccessControl {@requires admin}
     */
    function retrieveAll() {
        $result = array();

        $result['data'] = $this->db->getAllByKey('providers', 'domain');
        
        return $result;
    }

    /**
     *  This will get the information for a specific provider
     *
     * @access protected
     * @class  AccessControl {@requires admin}
     */
    function get($provider_id) {
        $provider = array();

        $provider['data'] = $this->db->get('providers', $provider_id);

        if ($provider)
            return $provider;
        else
            throw new RestException(404, 'No information for this provider');
    }

    /**
     *  This will update the information for a provider
     *
     * @access protected
     * @class  AccessControl {@requires admin}
     */
    function post($provider_id, $request_data = null) {
        Validator::validateEdit($request_data, $this->_FIELDS);

        foreach ($request_data as $key => $value) {
            if (!$this->db->update('providers', $provider_id, $key, $value))
                throw new RestException(500, 'Error while saving');
        }

        return array('status' => true, 'message' => 'Provider successfully modified');
    }

    /**
     *  This will add a provider
     *
     * @access protected
     * @class  AccessControl {@requires admin}
     */
    function put($request_data = null) {
        $object_ready = $this->db->prepareAddProviders($request_data);
        if (!$this->db->add('providers', Validator::validateAdd($object_ready, $this->_FIELDS)))
            throw new RestException(500, 'Error while Adding');
        else
            return array('status' => true, 'message' => 'Provider successfully added');
    }

    /**
     *  This function only delete the provider
     *  TODO: allow the provider to also delete the users linked to this provider.
     *
     * @access protected
     * @class  AccessControl {@requires admin}
     */
    function delete($provider_id) {
        if (!$this->db->delete('providers', $provider_id))
            throw new RestException(500, 'Error while deleting');
        else
            return array('status' => true, 'message' => 'Provider successfully deleted');
    }
}

?>