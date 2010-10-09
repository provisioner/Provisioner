<html>
<head>
        <!-- Begin JavaScript -->
        <script type="text/javascript" src="http://www.the159.com/test/jquery.js"></script>
	<script type="text/javascript" src="http://plugins.jquery.com/files/jquery.imagemap.js_1.txt"></script>
	<script type="text/javascript" src="../js/jquery.colorbox.js"></script>
        <script type="text/javascript" src="../js/endpoint.js"></script>

        <link media="screen" rel="stylesheet" href="../css/colorbox.css" />
	<link media="screen" rel="stylesheet" href="../css/endpoint.css" />


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
$phoneModel = 't22';

?>
</head>

<body>

    <h2>Two Options For Displaying Configurations...</h2>
    (Both options work in modals/pop-ups)



    <!-- Option 1 - select a model, then configure -->
    <h4>Option 1: Display a selector, then a phone on click</h4>
    <div id="phone_1" class="endpoint">
        <input type="hidden" name="phone_1">

        <div class="endpoint_select">
            <div class="yealink_t22">Yealink T22 Icon Goes Here</div>
            <div class="yealink_t28">Yealink T28 Icon Goes Here</div>
        </div>

        <div style="clear: both"></div>

        <div class="endpoint_configure">
        </div>
    </div>

    <input type="submit" value="Save">

    <hr>



    <!-- Option 2 - model is known, configure only that model -->
    <h4>Option 2: Display an already selected phone for configuration</h4>

    <div id="yealink_phone_1234929" class="endpoint">
        <div class="endpoint_configure yealink_t22">
        </div>
    </div>

    <button class="show_options">Click here to show options</button>




    <!-- Modal stuff -->
    <form name="blah" method="POST">

    <div class="phone_options" style="display:none">
        <div class="display" />
    </div>
    
    </form>

</body>
</html>
