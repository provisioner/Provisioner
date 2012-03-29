<?php
$brands_list = file2json('http://repo.provisioner.net/endpoint/master.json');
$brands_list = $brands_list['data']['brands'];

require_once('../includes/json.php');
if (!class_exists("DateTimeZone")) { require_once('../includes/timezone.php'); }
$zones = DateTimeZone::listIdentifiers();
?>
<html>
<head>
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" charset="utf-8">
	$(function(){
		$("select#brand").change(function(){
            $.ajaxSetup({ cache: false });
			$.getJSON("ajax.php?atype=model",{id: $(this).val()}, function(j){
				var options = '';
				for (var i = 0; i < j.length; i++) {
					options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
				}
				$("#model_demo").html(options);
				$('#model_demo option:first').attr('selected', 'selected');
			})
		})
	})
	$(function(){
		$("select#model_demo").change(function(){
            $.ajaxSetup({ cache: false });
			var brand = '';
			brand = $('select#brand').val();
			$.getJSON("ajax.php?atype=model_demo&brand="+brand,{id: $(this).val()}, function(j){
				var options = '';
				for (var i = 0; i < j.length; i++) {
					options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
				}
				$("#regs").html(options);
				$('#regs option:first').attr('selected', 'selected');
			})
		})
	})
	</script>
</head>
<body>
<h2>Provisioner.net Demo</h2>
<form name="form1" method="post" action="display.php">
<label>Mac Address:<input type="text" name="mac" id="mac" /></label><br />
<label>Please select Brand of Phone:<select name="brand" id="brand">
	<option value="--">--</option>
	<? foreach($brands_list as $data){ ?>
  <option value="<? echo $data['directory']  ?>"><? echo $data['name']  ?></option>
	<? } ?>
</select></label>
<br />
<label>Please select Model of Phone:<select name="model_demo" id="model_demo">
<option></option>
</select></label>
<br />
  <label>Configure # Registrations:
	<select name="regs" id="regs">
	<option></option>
	</select>  </label><br />
<label>Timezone:<select name="timezone" id="timezone">
	<? foreach($zones as $key => $data){ ?>
  <option value="<? echo $data  ?>" <? echo ($data == 'America/Los_Angeles') ? 'selected' : ''; ?>><? echo $data  ?></option>
	<? } ?>
</select></label><br />
<label>Please Select Output Type:<select name="output" id="output">
	<option value="TFTP">FILE</option>
  <option value="HTTP">HTTP</option>
</select>
</label>
<br />
<label>
  <input type="submit" name="Configure Phone" id="button" value="Configure Phone">
</label>
</form>
</body>
</html>