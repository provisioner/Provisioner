<?php

/*require_once 'wrapper/bigcouch.php';

$db = new BigCouch('http://localhost');

echo "<pre>";
print_r($db->getAllByKey('factory_defaults', 'family', 'yealink'));
echo "</pre>";*/

require_once 'vendor/restler.php';
use Luracast\Restler\Restler;

$r = new Restler();
$r->addAPIClass('phones');
$r->handle();

?>