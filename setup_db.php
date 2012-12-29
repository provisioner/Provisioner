<?php 

/**
 * This file will configure your database.
 * It MUST be run before using the provisioner
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */

require_once 'bootstrap.php';

require_once LIB_BASE . 'php_on_couch/couch.php';
require_once LIB_BASE . 'php_on_couch/couchClient.php';
require_once LIB_BASE . 'php_on_couch/couchDocument.php';

define('CONFIG_FILE', PROVISIONER_BASE . 'config.json');

// Loading config file
$configs = json_decode(file_get_contents(CONFIG_FILE));

if (!$configs)
    die('Could not load the config file');

if (strtolower($configs->database->type) == "bigcouch") {

    // Providers
    // =========

    // Creating the database
    $couch_client = new couchClient($configs->database->url, "providers_test");
    $couch_client->createDatabase();

    // Creating the master account
    $provider = $configs->database->master_provider;

    $new_doc = new stdClass();
    $new_doc->name = $provider->name;
    $new_doc->authorized_ip = $provider->ip;
    $new_doc->domain = $provider->domain;
    $new_doc->default_account_id = null;
    $new_doc->pvt_access_type = "admin";
    $new_doc->settings = null;

    try {
        $couch_client->storeDoc($new_doc);
    } catch (Exception $e) {
        die("ERROR: ". $e->getMessage() . " (". $e->getCode() .")<br>");
    }
}

 ?>