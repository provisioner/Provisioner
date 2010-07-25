<?PHP
class grandstream {
	function reboot($id) {
		global $global_cfg;
		$sql = "SELECT  ext FROM  endpointman_mac_list WHERE  id =". $id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		exec($global_cfg['asterisk_location']." -rx 'sip notify grandstream-check-cfg " . $row['ext'] . "'");
		exec($global_cfg['asterisk_location']." -rx 'sip notify polycom-check-cfg " . $row['ext'] . "'");
	}
	
	function generate_config () {
		die('This function can not be called through a non-extended class!');
	}
	
	function delete_config () {
		
	}
	
	function parse_gs_config ($filename)
	{
		if (!($f = @fopen ($filename, "r"))) {
			echo ("Unable to open " . $filename . "\n");
			return FALSE;
		}

		while ($str = fgets ($f)) {
			if (($pos = strpos ($str, "#")) !== FALSE) {
				$str = substr ($str, 0, $pos);
			}
			if (strlen($str)) {
				if (ereg ("(.+)=(.*)", $str, $matches)) {
					$params[trim($matches[1])] = trim($matches[2]);
				}
			}
		}
		fclose ($f);

		return $params;
	}


	// MAC : 12 hex digits string
	// $params : array ("P01" => "something", ...)
	function gs_config_out ($mac, $params)
	{
		$prev = 0;

		if (!ereg ("^[0-9a-fA-F]{12}$", $mac))
			return FALSE;

		$params["gnkey"] = "0b82";

		foreach ($params as $key => $val) {
			if ($prev)
				$str .= "&";
			else
				$prev = 1;

			$str .= $key . "=" . $val;
		}

		if (strlen ($str) & 1) $str .= chr(0);

		// Insert the beginning
		$new_str = chr(0) . chr(0) . chr((16+strlen ($str)) / 2 >> 8 & 0xff) . chr((16+strlen ($str)) / 2 & 0xff) . chr(0) . chr(0);

		// Insert the MAC address
		for ($i = 0; $i < 6; $i++) {
			$new_str .= chr(hexdec (substr ($mac, $i*2, 2)));
		}

		// Insert the end of the first line
		$new_str .= chr(13) . chr(10) . chr(13) . chr(10) . $str;

		// Basic checksum
		$k = 0;
		for ($i = 0; $i < strlen ($new_str) / 2; $i++) {
			$k += ord($new_str[$i*2]) << 8 & 0xff00;
			$k += ord($new_str[$i*2 + 1]) & 0xff;
			$k &= 0xffff;
		}

		$k = 0x10000 - $k;
		$new_str[4] = chr($k >> 8 & 0xff);
		$new_str[5] = chr($k & 0xff);

		return $new_str;
	}
}
?>