<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$xml = simplexml_load_file($_GET['filename']);
$json = json_encode($xml);
echo $json;
