<?php

require_once "../classes/utils.php";
require_once "../bootstrap.php";


class UtilsTest extends PHPUnit_Framework_TestCase {
    public function testGetMacAddress() {
        $ua = "Yealink SIP-T22P 3.2.2.1136 00:15:65:00:00:00";
        $uri = "/002e3a6fe532d90943e6fcaf08e1a408/001565000000.cfg";
        // Normal - ua
        $mac_address = ProvisionerUtils::get_mac_address($ua, $uri);
        $this->assertEquals("001565000000", $mac_address);

        // ua - other syntax
        $ua = "Yealink SIP-T22P 3.2.2.1136 001565000000";
        $mac_address = ProvisionerUtils::get_mac_address($ua, $uri);
        $this->assertEquals("001565000000", $mac_address);

        // ua - other syntax
        $ua = "Yealink SIP-T22P 3.2.2.1136 00-15-65-00-00-00";
        $mac_address = ProvisionerUtils::get_mac_address($ua, $uri);
        $this->assertEquals("001565000000", $mac_address);

        // Normal - uri
        $ua = "Yealink SIP-T22P 3.2.2.1136";
        $mac_address = ProvisionerUtils::get_mac_address($ua, $uri);
        $this->assertEquals("001565000000", $mac_address);

        // Error - not in ua - not in uri
        $uri = "/002e3a6fe532d90943e6fcaf08e1a408/blabla.cfg";
        $mac_address = ProvisionerUtils::get_mac_address($ua, $uri);
        $this->assertEquals(false, $mac_address);
    }

    public function testGetProviderDomain() {
        // Normal - full host
        $http_host = "www.localhost.com:8888";
        $host = ProvisionerUtils::get_provider_domain($http_host);
        $this->assertEquals("localhost.com", $host);

        // Normal - no port
        $http_host = "www.localhost.com";
        $host = ProvisionerUtils::get_provider_domain($http_host);
        $this->assertEquals("localhost.com", $host);
    }

    public function testGetAccountId() {
        // Normal
        $uri = "/002e3a6fe532d90943e6fcaf08e1a408/001565000000.cfg";
        $account_id = ProvisionerUtils::get_account_id($uri);
        $this->assertEquals("002e3a6fe532d90943e6fcaf08e1a408", $account_id);

        // Error - wrong format - too long
        $uri = "/002e3a6fe532d90943e6fcaf08e1a408balbla/001565000000.cfg";
        $account_id = ProvisionerUtils::get_account_id($uri);
        $this->assertEquals(false, $account_id);

        // Error - wrong format - too short
        $uri = "/002e3a6fe532d90943e6fcaf08/001565000000.cfg";
        $account_id = ProvisionerUtils::get_account_id($uri);
        $this->assertEquals(false, $account_id);
    }

    public function testGetAccountDb() {
        // Normal
        $account_id = "002e3a6fe532d90943e6fcaf08e1a408";
        $account_db = ProvisionerUtils::get_account_db($account_id);
        $this->assertEquals("account/00/2e/3a6fe532d90943e6fcaf08e1a408", $account_db);

        // Error - not a GUID
        $account_id = "002e3a6fe532d90943e6fcaf08e1";
        $account_db = ProvisionerUtils::get_account_db($account_id);
        $this->assertEquals(false, $account_db);
    }

    public function testStripUri() {
        // Normal - 1 level
        $uri = "/002e3a6fe532d90943e6fcaf08e1a408/001565000000.cfg";
        $return = ProvisionerUtils::strip_uri($uri);
        $this->assertEquals("001565000000.cfg", $return);

        // Normal - 2 level
        $uri = "/002e3a6fe532d90943e6fcaf08e1a408/bla/001565000000.cfg";
        $return = ProvisionerUtils::strip_uri($uri);
        $this->assertEquals("001565000000.cfg", $return);
    }

    public function testGetFolder() {
        // Normal
        $folder = ProvisionerUtils::get_folder("yealink", "t22");
        $this->assertEquals('t2x');
    }
}

?>