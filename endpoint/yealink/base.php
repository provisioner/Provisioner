<?PHP
/**
 * Yealink Base File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
abstract class endpoint_yealink_base extends endpoint_base {
	
	public $brand_name = 'yealink';

	function reboot() {
		if(($this->engine == "asterisk") AND ($this->system == "unix")) {
			exec($this->engine_location." -rx 'sip notify polycom-check-cfg ".$this->lines[1]['ext']."'");
		}
	}
	
	/**
	* $type is either gmt or tz
	*/
	function setup_timezone($timezone,$type) {
		if($type == 'TZ') {
			preg_match('/.*(-|\+)(\d*):(\d*)/i', $timezone,$matches);
			switch($matches[3]) {
				case '30':
					$point = '.5';
					break;
				default:
					$point = '';
					break;
			}
			return $matches[1].$matches[2].$point;
		} else {
			return FALSE;
		}
	}
	
}
?>