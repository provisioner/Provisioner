<?php

/*require_once 'wrapper/bigcouch.php';

$db = new BigCouch('http://localhost');

echo "<pre>";
print_r($db->get('copy_defaults', 'yealink'));
echo "</pre>";

exit();

delChildren($db->get('copy_defaults', 'yealink_t2x'));

function delChildren($document) {
    $db = new BigCouch('http://localhost');

    if (array_key_exists('children', $document)) {
        foreach ($document['children'] as $child) {
            $doc_child = $db->get('copy_defaults', $child);
            delChildren($doc_child);
        }
    }

    if ($db->delete('copy_defaults', $document['_id'])) {
        echo 'test';
        echo "<br>";
    }
}*/

/*require_once 'wrapper/bigcouch.php';

$db = new BigCouch('http://localhost');

echo "<pre>";
print_r($db->getAllByKey('factory_defaults', 'model', 't2x'));
echo "</pre>";
*/
require_once 'vendor/restler.php';
use Luracast\Restler\Restler;

$r = new Restler();
$r->addAPIClass('phones');
$r->handle();

?>