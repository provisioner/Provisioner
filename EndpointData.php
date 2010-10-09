<?php
/*
 * This is totally cheating and either needs to be fixed or needs to be re-implemented properly
 */

$xml = simplexml_load_file($_GET['filename']);
$json = json_encode($xml);
echo $json;
