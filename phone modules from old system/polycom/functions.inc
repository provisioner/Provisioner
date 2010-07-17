<?PHP
class endpoint_config_gen_polycom extends endpoint_config_gen {
	
	public static $brand_name = 'polycom';
	public static $family_line = 'spipm';
	
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