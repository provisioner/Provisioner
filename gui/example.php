<?
include('provisioner_gui.php');

$out = new provisioner_gui('../endpoint');

/* You can hide elements if you know their names.
$hide = array(
	"lineloop|line_1"
);
*/
$hide = array();

$html = $out->generate_textual_form("T26","t2x","yealink",$hide);

foreach($html as $category_name => $category) {
	echo "<h1>".$category_name."</h1>";
	foreach($category as $subcategory_name => $items) {
		echo "<h2>".$subcategory_name."</h2>";
		foreach($items as $data) {
			echo $data;
		}
	}
}