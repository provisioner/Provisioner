<?php 

/**
 * This file will configure your database.
 * It MUST be run before using the provisioner
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
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

$server_url = $configs->database->url . ":" . $configs->database->port;

if (strtolower($configs->database->type) == "bigcouch") {

    // Providers
    // =========

    // Creating the database
    $couch_client = new couchClient($server_url, "providers_test");

    if (!$couch_client->databaseExists())
        $couch_client->createDatabase();

    // Creating the master account
    $provider = $configs->database->master_provider;

    $provider_doc = new stdClass();
    $provider_doc->name = $provider->name;
    $provider_doc->authorized_ip = $provider->ip;
    $provider_doc->domain = $provider->domain;
    $provider_doc->default_account_id = null;
    $provider_doc->pvt_access_type = "admin";
    $provider_doc->settings = null;

    $provider_view = new stdCLass();
    $provider_view->_id = "_design/providers";
    $provider_view->language = "javascript";

    $view = new stdCLass();
    // by domain
    $view->{"list_by_domain"} = array(
        "map" => "function(doc) { if (doc.pvt_type != 'provider') return; emit(doc.domain, {'id': doc._id, 'name': doc.name, 'domain' : doc.domain , 'default_account_id' : doc.default_account_id, 'settings': doc.settings}); }"
    );

    // by ip
    $view->{"list_by_ip"} = array(
        "map" => "function(doc) { if (doc.pvt_type != 'provider') return; emit(doc.authorized_ip, {'access_type': doc.pvt_access_type}); }"
    );

    $provider_view->views = $view;

    try {
        $couch_client->storeDoc($provider_doc);
        $couch_client->storeDoc($provider_view);
    } catch (Exception $e) {
        die("ERROR: " . $e->getMessage() . " (" . $e->getCode() . ")<br>");
    }

    // Factory defaults
    // ================

    // Creating the database
    $couch_client->useDatabase("factory_defaults_test");

    if (!$couch_client->databaseExists())
        $couch_client->createDatabase();


    // Creating the views
    $factory_view = new stdCLass();
    $factory_view->_id = "_design/factory_defaults";
    $factory_view->language = "javascript";

    // reset
    $view = new stdCLass();
    // By brand
    $view->{"list_by_brand"} = array(
        "map" => "function(doc) { if (doc.pvt_type != 'brand') return; emit(doc.brand, {'id': doc._id, 'name': doc.brand, 'settings' : doc.settings}); }"
    );

    // By family
    $view->{"list_by_family"} = array(
        "map" => "function(doc) { if (doc.pvt_type != 'family') return; emit([doc.brand,doc.family], {'id': doc._id, 'name': doc.family, 'settings' : doc.settings}); }"
    );

    // By model
    $view->{"list_by_model"} = array(
        "map" => "function(doc) { if (doc.pvt_type != 'model') return; emit([doc.family,doc.model], {'id': doc._id, 'name': doc.model, 'settings' : doc.settings}); }"
    );

    // Get All
    $view->{"list_by_all"} = array(
        "map" => "function(doc) { emit([doc.brand,doc.family,doc.model], {'id': doc._id, 'settings': doc.settings}); }"
    );
    $factory_view->views = $view;

    try {
        $couch_client->storeDoc($factory_view);
    } catch (Exception $e) {
        die("ERROR: " . $e->getMessage() . " (" . $e->getCode() . ")<br>");
    }

    // System Account settings
    // =======================

    // Creating the database
    $couch_client->useDatabase("system_account_test");

    if (!$couch_client->databaseExists())
        $couch_client->createDatabase();

    // Creating the documents 
    $system_global_doc = new stdCLass();
    $system_global_doc->_id = "global_settings";
    $system_global_doc->settings = null;

    $system_manual_doc = new stdClass();
    $system_manual_doc->_id = "manual_provisioning";
    $system_manual_doc->settings = null;

    try {
        $couch_client->storeDoc($system_global_doc);
        $couch_client->storeDoc($system_manual_doc);
    } catch (Exception $e) {
        die("ERROR: " . $e->getMessage() . " (" . $e->getCode() . ")<br>");
    }

    // OK, this is lame... But better then nothing.
    // TODO: put an ugly ASCII art right here
    echo "=========================== <br>";
    echo "SUCCESS!<br>";
    echo "=========================== <br>";
}

 ?>