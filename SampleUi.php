<html>
<head>
        <!-- Begin JavaScript -->
        <script type="text/javascript" src="http://www.the159.com/test/jquery.js"></script>
	<script type="text/javascript" src="http://plugins.jquery.com/files/jquery.imagemap.js_1.txt"></script>
	<script src="http://www.provisioner.net/repo/javascript/jquery.colorbox.js"></script>
	<script src="http://www.provisioner.net/repo/javascript/phpjs.js"></script>
	<script src="http://www.provisioner.net/repo/javascript/endpoint_ui.js"></script>
	<link media="screen" rel="stylesheet" href="colorbox.css" />

<?php
include('EndpointUi.php');

EndpointUi::$moduleDir = 'endpoint/';

$js = EndpointUi::JsList();
$css = EndpointUi::CssList();

foreach ($js as $filename) {
	echo '<script type="text/javascript" src="' . $filename . '"></script>' . "\n";
}

foreach ($css as $filename) {
        echo '<LINK REL="stylesheet" HREF="' . $filename . '" TYPE="text/css" MEDIA="screen">' . "\n";
}

$phoneType = 'yealink';
$phoneProduct = 't2x';
$phoneModel = 'T22';

?>
<script>
	$(document).ready(function(){
		$(".example8").colorbox({width:"50%", inline:true, href:"#inline_example1"});
	});
</script>
</head>

<body>

<form name="whatever" action="http://www.the159.com/info/info.php" method="POST">
<input type="hidden" name="settings">

<div id="phone" class="phone <?php echo $phoneType; ?> <?php echo $phoneType . '_' . $phoneModel; ?>">
</div>

<?PHP
$endpoint_ui = new EndpointUi();

$family_data = $endpoint_ui->xml2array('endpoint/yealink/t2x/family_data.xml');

if (is_array($family_data['data']['model_list'])) {
    $key = $endpoint_ui->arraysearchrecursive($phoneModel, $family_data, "model");
    if ($key === FALSE) {
        die("You need to specify a valid model. Or change how this function works (line 110 of base.php)");
    } else {
        $template_data_list = $family_data['data']['model_list'][$key[2]]['template_data'];
    }
} else {
    $template_data_list = $family_data['data']['model_list']['template_data'];
}

$template_data = array();
$template_data_multi = "";
if (is_array($template_data_list['files'])) {
    foreach ($template_data_list['files'] as $files) {
        if (file_exists("endpoint/yealink/t2x/" . $files)) {
            $template_data_multi = $endpoint_ui->xml2array("endpoint/yealink/t2x/" . $files);
            $template_data_multi = $endpoint_ui->fix_single_array_keys($template_data_multi['template_data']['item']);
            $template_data = array_merge($template_data, $template_data_multi);
        }
    }
} else {
    if (file_exists("endpoint/yealink/t2x/" . $template_data_list['files'])) {
        $template_data_multi = $endpoint_ui->xml2array("endpoint/yealink/t2x/" . $template_data_list['files']);
        $template_data = $endpoint_ui->fix_single_array_keys($template_data_multi['template_data']['item']);
    }
}



if (file_exists("endpoint/yealink/t2x" . "/template_data_custom.xml")) {
    $template_data_multi = $endpoint_ui->xml2array("endpoint/yealink/t2x" . "/template_data_custom.xml");
    $template_data_multi = $endpoint_ui->fix_single_array_keys($template_data_multi['template_data']['item']);
    $template_data = array_merge($template_data, $template_data_multi);
}


if (file_exists("endpoint/yealink/t2x" . "/template_data_" . $phoneModel . "_custom.xml")) {
    $template_data_multi = $endpoint_ui->xml2array("endpoint/yealink/t2x" . "/template_data_" . $phoneModel . "_custom.xml");
    $template_data_multi = $endpoint_ui->fix_single_array_keys($template_data_multi['template_data']['item']);
    $template_data = array_merge($template_data, $template_data_multi);
}
?>
<pre>
<script type="text/javascript">
//var username
var test
var username
$.ajax({type: "GET",url: "http://www.provisioner.net/repo/endpoint/yealink/t2x/template_data.xml",success:GetResponse});

function GetResponse(data){
//var_dump(data);
//username = data
//var_dump(username);
}


//var_dump(username);
username = '<?PHP echo serialize($template_data); ?>'
username = unserialize(username);
test = arraysearchrecursivemulti("configureDisplay", username, "category");
var configureDisplayarray = new Array()
var count = 0
for ( var i in test )
{
    Key = test[i][0]
	configureDisplayarray[count] = username[Key];
	count++;
}
var_dump(configureDisplayarray);

//var_dump(this.generate_gui_html(configureDisplayarray,'h',TRUE,'h'));


</script>

<?PHP
$key = $endpoint_ui->arraysearchrecursivemulti("configureDisplay", $template_data, "category");
$configureDisplay = array();
$count = 0;
foreach($key as $data) {
	$configureDisplay[$count] = $template_data[$data[0]];
	$count++;
}
$modal_out['configureDisplay']["template_editor"] = $endpoint_ui->generate_gui_html($configureDisplay, NULL, TRUE);
$modal_out['configureDisplay']['header'] = "Configuring Display";


$key = $endpoint_ui->arraysearchrecursivemulti("configureHandset", $template_data, "category");
$configureDisplay = array();
$count = 0;
foreach($key as $data) {
	$configureDisplay[$count] = $template_data[$data[0]];
	$count++;
}
$modal_out['configureHandset']["template_editor"] = $endpoint_ui->generate_gui_html($configureDisplay, NULL, TRUE);
$modal_out['configureHandset']['header'] = "Configuring Handset Options";



$key = $endpoint_ui->arraysearchrecursivemulti("configureKey", $template_data, "category");
$configureDisplay = array();
$count = 0;
foreach($key as $data) {
	$configureDisplay[$count] = $template_data[$data[0]];
	$count++;
}
$modal_out['configureKey']["template_editor"] = $endpoint_ui->generate_gui_html($configureDisplay, NULL, TRUE);
?>

<!-- This contains the hidden content for inline calls -->
<div style='display:none'>
	<?PHP foreach($modal_out as $key => $data) { ?>
	<div id='<?PHP echo $key; ?>' style='padding:10px; background:#fff;'>
		<h2><?PHP echo $data['header'];?></h2>
		<?PHP
		$endpoint_ui->generate_html($data);
		?>
	</div>
	<?PHP } ?>
</div>

<input type="submit" value="Save">
</form>

</body>
</html>

