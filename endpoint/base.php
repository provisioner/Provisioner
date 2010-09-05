<?PHP

/**
 * Base Class for Provisioner
 *
 * @author Darren Schreiber & Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
abstract class endpoint_base {

    public static $modules_path = "endpoint/";
    
    public $brand_name = "undefined";
    public $family_line = "undefined";

    public $config_files_override;

    public $mac;            // Device mac address
	public $model;			// Model of phone, must match the model name inside of the famil_data.xml file in each family folder.
    public $description;    // Generic description
    public $timezone;       // Global timezone var
    public $server;         // Contains an array of valid server IPs & ports, in case phones support backups
	public $proxy;			// Contains an array of valid proxy IPs & ports
    public $lines;          // Individual line settings
    public $options;        // Misc. options for phones
	public $root_dir = "";		//need to define the root directory for the location of the library (/var/www/html/)
	public $engine;			//Can be asterisk or freeswitch. This is for the reboot commands.
	public $system;			//unix or windows or bsd. etc
	public $directory_structure = array();	//Directory structure to create as an array
	public $protected_files = array();	//array list of file to NOT over-write on every config file build. They are protected.
	public $copy_files = array();		//array of files or directories to copy. Directories will be recursive

    // Old
    /**
     *
     * @var string
     * @deprecated
     */
    public $ext;
    public $secret;

    public static function get_modules_path() {
        return self::$modules_path;
    }

    public static function set_modules_path($path) {
        self::$modules_path = $path;
    }

	function reboot() {
		
	}
	
	/**
     * Turns a string like PST-7 or UTC+1 into a GMT offset by stripping out Characters and replacing + and -
     * @param Send this something like PST-7
     * @return Offset (eg. -3600)
     */
	function get_gmtoffset($timezone) {
		$timezone = str_replace(":", ".", $timezone);
		$timezone = str_replace("30", "5", $timezone);
		$timezone = (int)$timezone;		
		if(strrchr($timezone,'+')) {
			$offset = $num * 3600;
		} elseif(strrchr($timezone,'-')) {
			$offset = $num * -3600;
		}
		return($offset);
	}
	
	/**
     * Turns a string like PST-7 or UTC+1 into a GMT offset by stripping out Characters and replacing + and -
     * @param Send this something like -3600
     * @return timezone (eg. +7 or +7:30)
     */
	function get_timezone($offset) {
		$timezone = $offset / 3600;
		if($timezone < 0) {
			$timezone = '-'.$timezone;
		} else {
			$timezone = '+'.$timezone;
		}
		$timezone = str_replace(".", ":", $timezone);
		$timezone = str_replace("5", "30", $timezone);
		return($timezone);
	}
	
	/**
     * Determines the type of timezone information we are working with, -3600 (gmtoffset) or -7:30 (timezone)
     * @param Send this something like PST-7 or -36000
     * @return Returns either GMTOFFSET or TIMEZONE
     */
	function determine_tz_type($timezone) {
		if(($timezone <= -3600) or ($timezone >= 3600)) {
			$type = 'GMTOFFSET';
		} else {
			$type = 'TIMEZONE';
		}
	}
	
	/**
     * Returns Abbreviated Timezone
     * @param Send this something like -3600
     * @return PST
     */
	function get_abbreviated_tz() {
		$dateTime = new DateTime(); 
		$dateTime->setTimeZone(new DateTimeZone('America/Los_Angeles')); 
		return $dateTime->format('T');
	}

    /**
     * Takes the name of a local configuration file and either returns that file from the hard drive as a string or takes the string from the array and returns that as a string
     * @param string $filename Configuration File name
     * @return string Full Configuration File (From Hard Drive or Array)
     * @example
     * <code>
     * 	$full_file = $this->open_config_file("local_file.cfg");
     * </code>
     */
    function open_config_file($filename) {
        //if there is no configuration file over ridding the default then load up $contents with the file's information, where $key is the name of the default configuration file
        if (!isset($this->config_files_override[$filename])) {
            $hd_file = $this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/" . $filename;
            //always use 'rb' says php.net
            $handle = fopen($hd_file, "rb");
			if(filesize($hd_file) > 0) {
            	$contents = fread($handle, filesize($hd_file));
			} else {
				$contents = "";
			}
            fclose($handle);
            return($contents);
        } else {
            return($this->config_files_override[$filename]);
        }
    }

    /**
     * This will parse configuration values that are either {$variable}, {$variable|default}, {$variable.line.num}, or {$variable.line.num|default}
     * It will determine the line ammount and then run the function to parse lines and then run parse config values (to replace any remaining values)
     * @param string $file_contents full contents of the configuration file
     * @param boolean $keep_unknown Keep Unknown variables as {$variable} instead of erasing them (blanking the space), can be used to parse these variables later
     * @param integer $lines The total number of lines for this model, NULL if defining a model
     * @param integer $specific_line The specific line number to manipulate. If no line number set then assume All Lines
     * @return string Full Contents of the configuration file (After Parsing)
     */
    function parse_config_file($file_contents, $keep_unknown=FALSE, $lines=NULL, $specific_line='ALL') {
        $family_data = $this->xml2array($this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/family_data.xml");

        //Get number of lines for this model from the family_data.xml file
        if (is_array($family_data['data']['model_list'])) {
            $key = $this->arraysearchrecursive($this->model, $family_data, "model");
            $line_total = $family_data['data']['model_list'][$key[2]]['lines'];
        } else {
            $line_total = $family_data['data']['model_list']['lines'];
        }


        if (($line_total <= 0) AND (!isset($lines))) {
			//There is no max number of lines for this phone. We default to 1 to be safe
            $line_total = 1;
        } elseif ((isset($lines)) AND ($lines > 0)) {
            $line_total = $lines;
        }

        $file_contents = $this->parse_lines($line_total, $file_contents, $keep_unknown = FALSE, $specific_line);
		$file_contents = $this->parse_loops($line_total,$file_contents, $keep_unknown = FALSE, $specific_line);
        $file_contents = $this->parse_config_values($file_contents);

        return $file_contents;
    }

    function parse_loops($line_total, $file_contents, $keep_unknown=FALSE, $specific_line='ALL') {
        //Find line looping data betwen {line_loop}{/line_loop}
        $pattern = "/{loop_(.*?)}(.*?){\/loop_(.*?)}/si";
        while (preg_match($pattern, $file_contents, $matches)) {
			if(isset($this->options[$matches[3]])) {
				$count = count($this->options[$matches[3]]);
				$parsed = "";
				if($count) {
					foreach($this->options[$matches[3]] as $number => $data) {
						$data['number'] = $number;
						$parsed .= $this->parse_config_values($matches[2], FALSE, "GLOBAL", $data);
					}
				}
				$file_contents = preg_replace($pattern, $parsed, $file_contents, 1);
			}
		}
		return($file_contents);
	}

    /**
     * Parse each individual line through use of {$variable.line.num} or {line_loop}{/line_loop}
     * @param string $line_total Total Number of Lines on the specific Phone
     * @param string $file_contents Full Contents of the configuration file
     * @param boolean $keep_unknown Keep Unknown variables as {$variable} instead of erasing them (blanking the space), can be used to parse these variables later
     * @param integer $specific_line The specific line number to manipulate. If no line number set then assume All Lines
     * @return string Full Contents of the configuration file (After Parsing)
     */
    function parse_lines($line_total, $file_contents, $keep_unknown=FALSE, $specific_line='ALL') {
        //Find line looping data betwen {line_loop}{/line_loop}
        $pattern = "/{line_loop}(.*?){\/line_loop}/si";
        while (preg_match($pattern, $file_contents, $matches)) {
            $i = 1;
            $parsed = "";
            //If specific line is set to ALL then loop through all lines
            if ($specific_line == "ALL") {
                while ($i <= $line_total) {
                    if (isset($this->lines[$i]['secret'])) {
                        $parsed_2 = $this->replace_static_variables($matches[1], $i, TRUE);
                        $parsed .= $this->parse_config_values($parsed_2, TRUE, $i);
                    }
                    $i++;
                }
            //If Specific Line is set to a number greater than 0 then only process the loop for that line
            } else {
                $parsed_2 = $this->replace_static_variables($matches[1], $specific_line, TRUE);
                $parsed = $this->parse_config_values($parsed_2, TRUE, $specific_line);
            }
            $file_contents = preg_replace($pattern, $parsed, $file_contents, 1);
        }


        //If secret is set for said line then assume it's active and pull it's data but only if said line can be used on the phone
        //This will replace {$variable.line.num}
        $i = 1;
        while ($i <= $line_total) {
            if (isset($this->lines[$i]['secret'])) {
                $file_contents = $this->replace_static_variables($file_contents, $i, FALSE);
            }
            $i++;
        }

        $file_contents = $this->replace_static_variables($file_contents, "GLOBAL");

        return($file_contents);
    }

    /**
     *
     * @param string $file_contents
     * @param boolean $keep_unknown
     * @param string $specific_line
     * @return string
     */

    function parse_config_values($file_contents, $keep_unknown=FALSE, $specific_line="GLOBAL", $options=NULL) {
		if(!isset($options)) {
			$options=$this->options;
		}
        $family_data = $this->xml2array($this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/family_data.xml");

        if (is_array($family_data['data']['model_list'])) {
            $key = $this->arraysearchrecursive($this->model, $family_data, "model");
            if ($key === FALSE) {
                die("You need to specify a valid model. Or change how this function works (line 110 of base.php)");
            } else {
                $template_data_list = $family_data['data']['model_list'][$key[2]]['template_data'];
            }
        } else {
            $template_data_list = $family_data['data']['model_list']['template_data'];
        }

        $template_data = array();
        $template_data_multi = "";
        if (is_array($template_data_list['files'])) {
            foreach ($template_data_list['files'] as $files) {
                if (file_exists($this->root_dir.self::$modules_path . $this->brand_name . "/" . $this->family_line . "/" . $files)) {
                    $template_data_multi = $this->xml2array($this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/" . $files);
                    $template_data_multi = $this->fix_single_array_keys($template_data_multi['template_data']['item']);
                    $template_data = array_merge($template_data, $template_data_multi);
                }
            }
        } else {
            if (file_exists($this->root_dir.self::$modules_path . $this->brand_name . "/" . $this->family_line . "/" . $template_data_list['files'])) {
                $template_data_multi = $this->xml2array($this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/" . $template_data_list['files']);
                $template_data = $this->fix_single_array_keys($template_data_multi['template_data']['item']);
            }
        }

        if (file_exists($this->root_dir.self::$modules_path . $this->brand_name . "/" . $this->family_line . "/template_data_custom.xml")) {
            $template_data_multi = $this->xml2array($this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/template_data_custom.xml");
            $template_data_multi = $this->fix_single_array_keys($template_data_multi['template_data']['item']);
            $template_data = array_merge($template_data, $template_data_multi);
        }

        if (file_exists($this->root_dir.self::$modules_path . $this->brand_name . "/" . $this->family_line . "/template_data_" . $this->model . "_custom.xml")) {
            $template_data_multi = $this->xml2array($this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/template_data_" . $this->model . "_custom.xml");
            $template_data_multi = $this->fix_single_array_keys($template_data_multi['template_data']['item']);
            $template_data = array_merge($template_data, $template_data_multi);
        }

        //Find all matched variables in the text file between "{$" and "}"
        preg_match_all('/[{\$](.*?)[}]/i', $file_contents, $match);
        //Result without brackets (but with the $ variable identifier)
        $no_brackets = array_values(array_unique($match[1]));
        //Result with brackets
        $brackets = array_values(array_unique($match[0]));


        //loop though each variable found in the text file
        foreach ($no_brackets as $variables) {
            $variables = str_replace("$", "", $variables);

            //Users can set defaults within template files with pipes, they will over-ride whatever is in the XML file.
            if (strstr($variables, "|")) {
                $original_variable = $variables;
                $variables = explode("|", $variables);
                $default = $variables[1];
                $variables = $variables[0];
                if (strstr($variables, ".")) {
                    $original_variable = $variables;
                    $variables = explode(".", $variables);
                    $specific_line = $variables[2];
                    $variables = $variables[0];
                } else {
                    $original_variable = $variables;
                }
            } else {
                unset($default);
                $original_variable = $variables;
                if (strstr($variables, ".")) {
                    $original_variable = $variables;
                    $variables = explode(".", $variables);
                    $specific_line = $variables[2];
                    $variables = $variables[0];
                }
            }

            //If the variable we found in the text file exists in the variables array then replace the variable in the text file with the value under our key
            if (($specific_line == "GLOBAL") AND (isset($options[$variables]))) {
                $options[$variables] = htmlspecialchars($options[$variables]);
                $options[$variables] = $this->replace_static_variables($options[$variables]);
                if (isset($default)) {
                    $file_contents = str_replace('{$' . $original_variable . '|' . $default . '}', $options[$variables], $file_contents);
                } else {
                    $file_contents = str_replace('{$' . $original_variable . '}', $options[$variables], $file_contents);
                }
            } elseif (($specific_line != "GLOBAL") AND (isset($this->lines[$specific_line][$variables]))) {
	
				
                $this->lines[$specific_line][$variables] = htmlspecialchars($this->lines[$specific_line][$variables]);

              	$this->lines[$specific_line][$variables] = $this->replace_static_variables($this->lines[$specific_line][$variables]);
                if (isset($default)) {
                    $file_contents = str_replace('{$' . $original_variable . '|' . $default . '}', $this->lines[$specific_line][$variables], $file_contents);
                } else {
                    $file_contents = str_replace('{$' . $original_variable . '}', $this->lines[$specific_line][$variables], $file_contents);
                }
            } else {
                if (!$keep_unknown) {
                    //read default template values here, blank unknowns or arrays (which are blanks anyways)
                    $key1 = $this->arraysearchrecursive('$' . $variables, $template_data, 'variable');
                    if (isset($default)) {
                        $file_contents = str_replace('{$' . $original_variable . '|' . $default . '}', $default, $file_contents);
                    } elseif ((isset($template_data[$key1[0]]['default_value'])) AND (!is_array($template_data[$key1[0]]['default_value']))) {
                        $file_contents = str_replace('{$' . $original_variable . '}', $template_data[$key1[0]]['default_value'], $file_contents);
                    } else {
                        $file_contents = str_replace('{$' . $original_variable . '}', "", $file_contents);
                    }
                }
            }
        }


        return $file_contents;
    }

    /**
     *
     * @param string $contents
     * @param string $specific_line
     * @param boolean $looping
     * @return string
     */

    function replace_static_variables($contents, $specific_line="GLOBAL", $looping=TRUE) {
        foreach($this->server as $key => $servers) {
            $contents = str_replace('{$server.ip.'.$key.'}', $servers['ip'], $contents);
            $contents = str_replace('{$server.port.'.$key.'}', $servers['port'], $contents);
        }
		
		if(isset($this->proxy)) {
			foreach($this->proxy as $key => $proxies) {
				$contents = str_replace('{$proxy.ip.'.$key.'}', $proxies['ip'], $contents);
				$contents = str_replace('{$proxy.port'.$key.'}', $proxies['port'], $contents);
			}
		}
        $contents = str_replace('{$mac}', $this->mac, $contents);
        $contents = str_replace('{$model}', $this->model, $contents);
        $contents = str_replace('{$gmtoff}', $this->timezone, $contents);
        $contents = str_replace('{$gmthr}', $this->timezone, $contents);
        $contents = str_replace('{$timezone}', $this->timezone, $contents);

        if (($specific_line != "GLOBAL") AND ($looping == TRUE)) {
            $contents = str_replace('{$line}', $specific_line, $contents);
            $contents = str_replace('{$ext}', $this->lines[$specific_line]['ext'], $contents);
            $contents = str_replace('{$displayname}', $this->lines[$specific_line]['displayname'], $contents);
            $contents = str_replace('{$secret}', $this->lines[$specific_line]['secret'], $contents);
            $contents = str_replace('{$pass}', $this->lines[$specific_line]['secret'], $contents);
        } elseif (($specific_line != "GLOBAL") AND ($looping == FALSE)) {
            $contents = str_replace('{$line.line.' . $specific_line . '}', $specific_line, $contents);
            $contents = str_replace('{$ext.line.' . $specific_line . '}', $this->lines[$specific_line]['ext'], $contents);
            $contents = str_replace('{$displayname.line.' . $specific_line . '}', $this->lines[$specific_line]['displayname'], $contents);
            $contents = str_replace('{$secret.line.' . $specific_line . '}', $this->lines[$specific_line]['secret'], $contents);
            $contents = str_replace('{$pass.line.' . $specific_line . '}', $this->lines[$specific_line]['secret'], $contents);
        } elseif ($specific_line == 'GLOBAL') {
	        //Find all matched variables in the text file between "{$" and "}"
	        preg_match_all('/[{\$](.*?)[}]/i', $contents, $match);
	        //Result without brackets (but with the $ variable identifier)
	        $no_brackets = array_values(array_unique($match[1]));
	        //Result with brackets
	        $brackets = array_values(array_unique($match[0]));
	        //loop though each variable found in the text file
	        foreach ($no_brackets as $variables) {
	            $variables = str_replace("$", "", $variables);
				$original_variable = $variables;
	            if (strstr($variables, ".")) {
	                $original_variable = $variables;
	                $variables = explode(".", $variables);
	                $specific_line = $variables[2];
	                $variables = $variables[0];
					switch ($variables) {
						case "ext":
							$contents = str_replace('{$ext.line.' . $specific_line . '}', $this->lines[$specific_line]['ext'], $contents);
							break;
						case "displayname":
			            	$contents = str_replace('{$displayname.line.' . $specific_line . '}', $this->lines[$specific_line]['displayname'], $contents);
							break;
					}
	            }
			}
		}

        return($contents);
    }

    /**
     *
     * @param array $array_old
     * @param array $array_new
     * @return array
     * @deprecated
     */
    function array_merge_check($array_old, $array_new) {
        if (is_array($array_old)) {
            return(array_merge($array_old, $array_new));
        } else {
            return($array_new);
        }
    }

    /**
     * Function xml2array has a bad habbit of returning blank xml values as empty arrays.
     * Also if the xml children only loops once then the array is put into a normal array (array[variable]).
     * However if it loops more than once then it is put into a counted array (array[0][variable])
     * We fix that issue here by returning blank values on empty arrays or always returning array[0]
     * @param array $array
     * @return mixed
     */
    function fix_single_array_keys($array) {
            if((empty($array[0])) AND (!empty($array))) {
                    $array_n[0] = $array;
                    return($array_n);
            } elseif(!empty($array)) {
                    return($array);
            } else {
                    return("");
            }
    }

    /**
     *
     * @param string $Needle
     * @param array $Haystack
     * @param string $NeedleKey
     * @param boolean $Strict
     * @param array $Path
     * @return array
     */
    function arraysearchrecursive($Needle, $Haystack, $NeedleKey="", $Strict=false, $Path=array()) {
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

    /**
     *
     * @param <type> $url
     * @param <type> $get_attributes
     * @param <type> $priority
     * @return <type>
     */
    function xml2array($url, $get_attributes = 1, $priority = 'tag') {
        $contents = "";
        if (!function_exists('xml_parser_create')) {
            return array();
        }
        $parser = xml_parser_create('');
        if (!($fp = @ fopen($url, 'rb'))) {
            return array();
        }
        while (!feof($fp)) {
            $contents .= fread($fp, 8192);
        }
        fclose($fp);
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);
        if (!$xml_values) {
            return; //Hmm...
        }
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();
        $current = & $xml_array;
        $repeated_tag_index = array();
        foreach ($xml_values as $data) {
            unset($attributes, $value);
            extract($data);
            $result = array();
            $attributes_data = array();
            if (isset($value)) {
                if ($priority == 'tag') {
                    $result = $value;
                } else {
                    $result['value'] = $value;
                }
            }
            if (isset($attributes) and $get_attributes) {
                foreach ($attributes as $attr => $val) {
                    if ($priority == 'tag') {
                        $attributes_data[$attr] = $val;
                    } else {
                        $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                    }
                }
            }
            if ($type == "open") {
                $parent[$level - 1] = & $current;
                if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
                    $current[$tag] = $result;
                    if ($attributes_data) {
                        $current[$tag . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    $current = & $current[$tag];
                } else {
                    if (isset($current[$tag][0])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array($current[$tag], $result);
                        $repeated_tag_index[$tag . '_' . $level] = 2;
                        if (isset($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = & $current[$tag][$last_item_index];
                }
            } else if ($type == "complete") {
                if (!isset($current[$tag])) {
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributes_data) {
                        $current[$tag . '_attr'] = $attributes_data;
                    }
                } else {
                    if (isset($current[$tag][0]) and is_array($current[$tag])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array($current[$tag], $result);
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $get_attributes) {
                            if (isset($current[$tag . '_attr'])) {
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }
                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                    }
                }
            } else if ($type == 'close') {
                $current = & $parent[$level - 1];
            }
        }
        return ($xml_array);
    }

}