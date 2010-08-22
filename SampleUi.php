<html>
<head>
        <!-- Begin JavaScript -->
        <script type="text/javascript" src="http://www.the159.com/test/jquery.js"></script>
	<script type="text/javascript" src="http://plugins.jquery.com/files/jquery.imagemap.js_1.txt"></script>

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
$phoneModel = 't22p';

?>

</head>

<body>

<form name="whatever" action="save.php" method="POST">
<input type="hidden" name="settings">

<div id="phone" class="phone <?php echo $phoneType; ?> <?php echo $phoneType . '_' . $phoneModel; ?>">
</div>

<input type="submit" value="Save">
</form>

</body>
</html>

