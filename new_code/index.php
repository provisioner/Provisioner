<?php

/*$str_ua = $_SERVER['HTTP_USER_AGENT'];
$str_requested_file = $_GET['file'];

echo $str_ua . ' |||||| ' . $str_requested_file; */

$mac = "y000000000005.cfg";

if (preg_match("/^([0-9a-f]{12})\.cfg$/i", $mac))
    echo "Mac";
elseif (preg_match("/^y00000000000([0-9a-f]{1})\.cfg$/i", $mac))
    echo "y000000";
else
    echo "nothing";

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