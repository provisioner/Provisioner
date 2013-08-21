<?php 

/**
 * All methods in this class are protected - Some more than others
 * Users APIs
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

class Users {
    private $_db;

    function __construct() {
        $this->_db = new BigCouch(DB_SERVER, DB_PORT);
    }

    // Will return the formated account_id from the raw account_id
    private function _get_account_db($account_id) {
        // account/xx/xx/xxxxxxxxxxxxxxxx
        return "account/" . substr_replace(substr_replace($account_id, '/', 2, 0), '/', 5, 0);
    }

    /**
     *  This is the function that will allow the administrator create a user
     *
     * @url PUT /
     * @access protected
     * @class  AccessControl {@requires admin}
     */
    function createUser($request_data) {
        if (!empty($request_data['account_id'])) {

            $user_view = $this->_db->getOneByKey('users', 'username', $request_data['username']);
            if ($user_view['total_rows'] < 1) {
                echo true;
                $arr_user = array(
                    "username" => $request_data['username'],
                    "password" => sha1($request_data['password']),
                    "account_id" => $request_data['account_id'],
                    "pvt_type" => 'user'
                );

                $this->_db->add('users', $arr_user);

                return array('status' => 'success');
            } else
                return array('status' => 'error', 'message' => 'This username is already in use');
        } else {
            // We need to create and account database first
            $account_id = $this->_db->getUuid('users');
            $account_db = $this->_get_account_db($account_id);
            if ($this->_db->createDatabase($account_db)) {
                $user_view = $this->_db->getOneByKey('users', 'username', $request_data['username']);
                if ($user_view['total_rows'] < 1) {
                    $arr_user = array(
                        "username" => $request_data['username'],
                        "password" => sha1($request_data['password']),
                        "account_id" => $account_id,
                        "pvt_type" => 'user'
                    );

                    // Create the User
                    $this->_db->add('users', $arr_user);
                    // Create the document in account_db
                    $this->_db->add($account_db, array(
                            '_id' => $account_id,
                            'settings' => '{}',
                            'name' => $request_data['username'] . ' account',
                            'provider_id' => $request_data['provider_id']
                        )
                    );

                    return array('status' => 'success');
                } else
                    return array('status' => 'error', 'message' => 'This username is already in use');
            }
        }
    }

    /**
     *  Login API
     *
     * @url POST /login
     * @access protected
     * @class  AccessControl {@requires admin}
     */
    function login($request_data) {
        $username = $request_data['username'];
        $password = sha1($request_data['password']);

        $user_view = $this->_db->getOneByKey('users', 'username', $username);
        if ($user_view['total_rows'] == 1) {
            if ($user_view['rows'][0]['value']['password'] == $password)
                return array('status' => 'success');
            else 
                return array('status' => 'error', 'message' => 'Wrong password');
        } else
            return array('status' => 'error', 'message' => "No user named $username");
    }
}