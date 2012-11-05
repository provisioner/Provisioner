<?PHP

/**
 * Base Class for Provisioner
 *
 * @author Darren Schreiber & Andrew Nagy & Jort Bloem
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 *
 */

abstract class endpoint_base {

    function __construct($model) {
		$this->model = $model;
        $this->root_dir = empty($this->root_dir) ? dirname(__FILE__) . "/" : $this->root_dir;
		
		// Correct timezone is required or PHP freaks out
		date_default_timezone_set('America/Los_Angeles');

		//Pre PHP 5.4
		foreach (explode(" ", "NONE DEPTH STATE_MISMATCH CTRL_CHAR SYNTAX UTF8") AS $key => $value) {
		    $value = "JSON_ERROR_$value";
		    if (!defined($value))
		        define($value, $key);
		}
		
		if (!function_exists('json_last_error')) {
		    function json_last_error() {
		        return JSON_ERROR_NONE;
		    }
		}
		
		$this->brand_data = $this->file2json($this->root_dir . '/' . $this->brand_name.'/brand_data.json');
		$this->family_data = $this->file2json($this->root_dir . '/' . $this->brand_name.'/'.$this->family_line.'/family_data.json');
		$loc = $this->arraysearchrecursive($this->model,$this->family_data['model_list'],'model');
		if(!$loc) { throw new Exception('Could not find model'); }
		
		$this->max_lines = $this->family_data['model_list'][$loc[0]]['lines'];
		$this->merge_template_files();
		
		// Load Twig
		$loader = new Twig_Loader_Filesystem($this->root_dir . "/");
		$this->twig = new Twig_Environment($loader);
    }

    /**
     * generate_all_files()
     * Generates all files listed in the 'configuration_files' option
     * @author Andrew Nagy
     */
    public function generate_all_files() {
		$output_array = array();
		foreach($this->family_data['configuration_files'] as $filename) {
			$output_array[$filename] = $this->generate_one_file($filename);
		}
		return $output_array;
    }

    /**
	*
	*
	*
	*/
	public function generate_one_file($filename) {
		$this->initalize();
		if(!file_exists($this->root_dir . "/" . $this->brand_name."/".$this->family_line."/".$filename)) { throw new Exception("Missing Configuration File: ".$file); }
		//Load template into twig
		$template = $this->twig->loadTemplate($this->brand_name."/".$this->family_line."/".$filename);

	    // Generate template using these settings
	    $result = $template->render($this->settings);
	
		return $result;
	}

	private function initalize() {
		//Do this first
		$this->settings = $this->merge_arrays($this->template_defaults,$this->settings);
		
		//Parse Line Hooks
		foreach($this->settings['lines'] as $key => $line_data) {
			$this->settings['lines'][$key] = $this->parse_lines_hook($line_data,$this->max_lines);
			//die('ok');
		}
	}
	
	protected function parse_lines_hook($line_data, $line_total) {
		return($line_data);
	}

    private function merge_template_files() {
	
		$file_data = $this->file2json($this->root_dir . '/global_template_data.json');
		$this->template_data = isset($this->template_data) ? $this->merge_arrays($this->template_data, $file_data) : $file_data;
	
		$loc = $this->arraysearchrecursive($this->family_line,$this->brand_data['family_list'],'directory');
		if($loc) {
			foreach($this->brand_data['family_list'][$loc[0]]['template_data'] as $template_file) {
				$file_data = $this->file2json($this->root_dir . '/' . $this->brand_name.'/'.$template_file);
				//$this->template_data = isset($this->template_data) ? $this->merge_arrays($this->template_data, $file_data) : $file_data;
			}
		}
	
		$loc = $this->arraysearchrecursive($this->model,$this->family_data['model_list'],'model');
		foreach($this->family_data['model_list'][$loc[0]]['template_data'] as $template_file) {
			$file_data = $this->file2json($this->root_dir . '/' . $this->brand_name.'/'.$this->family_line.'/'.$template_file);
			//$this->template_data = isset($this->template_data) ? $this->merge_arrays($this->template_data, $file_data) : $file_data;
		}
		
		//Loop through all 'categories/templates'
		foreach($this->template_data as $template_name => $template) {
			$this->template_defaults = $this->construct_defaults($template['items']);
		}		
    }

	private function construct_defaults($template_array,$loop_count=NULL) {
		$out_array = array();
		foreach($template_array as $variable => $data) {	
			if(!isset($data['type'])) { throw new Exception('Template File is missing type setting for '. $variable); }
			if(isset($out_array['variable'])) { throw new Exception($variable. ' name clash'); }
			switch($data['type']) {
				case "loop":
					for($i=$data['start'];$i<=$data['quantity'];$i++) {
						$out_array[$variable][$i] = $this->construct_defaults($data['loop_data'],$i);
					}
					break;
				case "group":
					$out_array[$variable] = $this->construct_defaults($data['items']);
					break;
				case "line_loop":
					for($i=0;$i<$this->max_lines;$i++) {
						$out_array[$variable][$i] = $this->construct_defaults($data['loop_data']);
						$out_array[$variable][$i]['line'] = $i + 1;
					}
					break;
				default:
					if(!isset($data['default_value'])) { throw new Exception('Default Value Not set for '. $variable); }
					$out_array[$variable] = !isset($loop_count) ? $data['default_value'] : $data['default_value'][$loop_count];
					break;
			}
		}
		return($out_array);
	}

    /**
     * NOTE: Wherever possible, try $this->DateTimeZone->getOffset(new DateTime) FIRST, which takes Daylight savings into account, too.
     * Turns a string like PST-7 or UTC+1 into a GMT offset in seconds
     * @param Send this a timezone like PST-7
     * @return Offset from GMT, in seconds (eg. -25200, =3600*-7)
     * @author Jort Bloem
     */
    private function get_gmtoffset($timezone) {
        # Divide the timezone up into it's 3 interesting parts; the sign (+/-), hours, and if they exist, minutes.
        # note that matches[0] is the entire matched string, so these 3 parts are $matches[1], [2] and [3].
        preg_match('/([\-\+])([\d]+):?(\d*)/', $timezone, $matches);
        # $matches is now an array; $matches[1] is the sign (+ or -); $matches[2] is number of hours, $matches[3] is minutes (or empty)
        return intval($matches[1] . "1") * ($matches[2] * 3600 + $matches[3] * 60);
    }

    /**
     * Turns an integer like -3600 (seconds) into a GMT offset like GMT-1
     * @param Time offset in seconds, like 3600 or -25200 or -27000
     * @return timezone (eg. GMT+1 or GMT-7 or GMT-7:30)
     * @author Jort Bloem
     */
    private function get_timezone($offset) {
        if ($offset < 0) {
            $result = "GMT-";
            $offset = abs($offset);
        } else {
            $result = "GMT+";
        }
        $result.=(int) ($offset / 3600);
        if ($result % 3600 > 0) {
            $result.=":" . (($offset % 3600) / 60);
        } else {
            $result.=":00";
        }
        return $result;
    }

    /**
     * Setup and fill in timezone data
     * @author Jort Bloem
     */
    protected function setup_timezone() {
        if (isset($this->DateTimeZone) && is_object($this->DateTimeZone)) {
            //We set this to allow phones to use Automatic DST
            $gmt_dst_fix = !$this->use_system_dst && date('I') ? 3600 : 0;
            $this->timezone = array(
                'gmtoffset' => $this->DateTimeZone->getOffset(new DateTime) - $gmt_dst_fix,
                'timezone' => $this->get_timezone($this->DateTimeZone->getOffset(new DateTime) - $gmt_dst_fix)
            );
        } else {
            throw new Exception('You Must define a valid DateTimeZone object');
        }
    }

    function file2json($file) {
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $data = json_decode($json, TRUE);
            $error = json_last_error();
            if ($error === JSON_ERROR_NONE) {
                return($data);
            } else {
                $errors = array(// Taken from http://www.php.net/manual/en/function.json-last-error.php
                    JSON_ERROR_NONE => 'No error has occurred',
                    JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
                    JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
                    JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
                    JSON_ERROR_SYNTAX => 'Syntax error',
                    JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
                );
                if (array_key_exists($error, $errors)) {
                    $error = $errors[$error];
                } else {
                    $error = "Unknown error $error";
                }
                throw new Exception("Could not decode $file: $error");
            }
        } else {
            throw new Exception("Could not load: " . $file);
        }
    }

    /**
     * Search Recursively through an array
     * @param string $Needle
     * @param array $Haystack
     * @param string $NeedleKey
     * @param boolean $Strict
     * @param array $Path
     * @return array
     */
    private function arraysearchrecursive($Needle, $Haystack, $NeedleKey="", $Strict=false, $Path=array()) {
        if (!is_array($Haystack))
            return false;
        foreach ($Haystack as $Key => $Val) {
            if (is_array($Val) &&
                    $SubPath = $this->arraysearchrecursive($Needle, $Val, $NeedleKey, $Strict, $Path)) {
                $Path = array_merge($Path, Array($Key), $SubPath);
                return $Path;
            } elseif ((!$Strict && $Val == $Needle &&
                    $Key == (strlen($NeedleKey) > 0 ? $NeedleKey : $Key)) ||
                    ($Strict && $Val === $Needle &&
                    $Key == (strlen($NeedleKey) > 0 ? $NeedleKey : $Key))) {
                $Path[] = $Key;
                return $Path;
            }
        }
        return false;
    }
    
    private function sys_get_temp_dir() {
        if (!empty($_ENV['TMP'])) {
            return realpath($_ENV['TMP']);
        }
        if (!empty($_ENV['TMPDIR'])) {
            return realpath($_ENV['TMPDIR']);
        }
        if (!empty($_ENV['TEMP'])) {
            return realpath($_ENV['TEMP']);
        }
        $tempfile = tempnam(uniqid(rand(), TRUE), '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
            return realpath(dirname($tempfile));
        }
    }

	/*
	 * Recursively merge two arrays, overwriting any keys that match with the second array
	 */
	private function merge_arrays($original_array, $new_array) {
		foreach($new_array as $key => $Value) {
			if(array_key_exists($key, $original_array) && is_array($Value)) {
				$original_array[$key] = $this->merge_arrays($original_array[$key], $new_array[$key]);
			} else {
				$original_array[$key] = $Value;
			}
		}
		return $original_array;
	}

	public function import_settings($filename) {
		$file_data = $this->file2json($filename);

	    // TO DO: Loop through recursively the entire array and look for any "quantity" specifications. If they exist
	    // make copies of the items

	    $this->settings = isset($this->settings) ? $this->merge_arrays($this->settings, $file_data) : $file_data;

	    return TRUE;
	}

}

if (!class_exists('InvalidArgumentException')) {
    class InvalidArgumentException extends Exception {       
    }
}

class InvalidObjectException extends Exception { 
}
