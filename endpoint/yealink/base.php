<?PHP
/**
 * Yealink Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
abstract class endpoint_yealink_base extends endpoint_base {
	
	public static $brand_name = 'yealink';
	
	function reboot($id) {
		//reboot phone here
		//This was relient on Asterisk/MYSQL/Globals
		//So I removed it for now
		
	}
	
	function generate_config () {
		die('This function can not be called through a non-extended class!');
	}
	
	function delete_config () {
		
	}
}
?>