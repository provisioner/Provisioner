<?php

require_once '../bootstrap.php' ;

$db_type = "BigCouch";

$db = new $db_type('http://localhost');

echo '<pre>';
print_r($db->loadSettings('test', 'afb4be6d5870518b8093d6ab290398dd'));
echo '</pre>';

exit();

// We assume we have:
// DATABASE: SYSTEM_ACCOUNT -- All global preferences/settings
// DATABASE: PROVIDERS -- A document for each provider, by provider URL
// DATABASE: <ACCOUNT_ID> - An account_id (which is random) which belongs to a provider and has all of a customer's default account settings AND the individual phone MAC address settings


require_once 'model/configfile.php';

$mac = "00:15:65:00:00:00";
$provider_id = "p.kazoo.io";

// Retrieve settings about this MAC address
$db = new $db_type($server_ip);

$json_1 = $db->loadSettings("system_account", "globals");
$json_2 = $db->loadSettings("providers", $provider_id);
$json_3 = $db->loadSettings($account_id, "account_settings");
$json_4 = $db->loadSEttings($account_id, $mac);

if (!$final_settings['model'] or !$final_settings['brand']) {
    // If model is unknown, try to auto-detect
    $ua = "yealink SIP-T22P 7.40.1.2 00:15:65:00:00:00";    
}

// Prepare the template for this type of phone
$test = new ConfigFile($mac, $ua);


$test->import_settings($json);

echo '<pre>';
var_dump($test);
echo '</pre>';





/*$str_ua = $_SERVER['HTTP_USER_AGENT'];
$str_requested_file = $_GET['file'];

echo $str_ua . ' |||||| ' . $str_requested_file; */

/*function mergeArray($arr1, $arr2)
{
    $keys = array_keys($arr2);
    foreach($keys as $key) {
        if(isset( $arr1[$key]) && is_array($arr1[$key]) && is_array($arr2[$key])) {
            $arr1[$key] = mergeArray($arr1[$key], $arr2[$key]);
        } else {
            $arr1[$key] = $arr2[$key];
        }
    }
    return $arr1;
}

require_once 'twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader);

$defaultsFile = file_get_contents('defaults.json');
$providerFile = file_get_contents('provider.json');
$phoneFile = file_get_contents('phone.json');

$finalArray = mergeArray(mergeArray(json_decode($defaultsFile, true), json_decode($providerFile, true)), json_decode($phoneFile, true));

$myFile = "testFile.txt";
$fh = fopen($myFile, 'w') or die("can't open file");
$test = "bakajsksksks";
fwrite($fh, $test);
fclose($fh);

echo $twig->render("test.cfg", $finalArray);*/
?>