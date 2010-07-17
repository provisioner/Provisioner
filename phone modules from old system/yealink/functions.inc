<?PHP
class yealink {
	function reboot($id) {
		global $global_cfg;
		$sql = "SELECT  ext FROM  endpointman_mac_list WHERE  id =". $id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		exec($global_cfg['asterisk_location']." -rx 'sip notify polycom-check-cfg " . $row['ext'] . "'");
		exec($global_cfg['asterisk_location']." -rx 'sip notify aastra-check-cfg " . $row['ext'] . "'");
	}
	
	function generate_config () {
		die('This function can not be called through a non-extended class!');
	}
	
	function delete_config () {
		
	}
}
?>