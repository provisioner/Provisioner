<?PHP
abstract class endpoint_polycom_base extends endpoint_base {
	
	public static $brand_name = 'polycom';
	
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
