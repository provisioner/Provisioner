<?PHP
class cisco {
	function reboot($id) {
		global $global_cfg;
		$sql = "SELECT ext FROM  endpointman_mac_list WHERE  id =". $id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		exec($global_cfg['asterisk_location']." -rx 'sip notify reboot-snom " . $row['ext'] . "'");
	}
}
?>