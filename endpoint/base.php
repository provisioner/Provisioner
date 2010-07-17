<?PHP
abstract class endpoint_base {
		
	public static $brand_name = "undefined";
	public static $family_line = "undefined";
        public static $modules_path = "endpoint/";

	public $brand;
	public $family;

	public $mac;
	public $ext;
	public $secret;
	public $description;
	public $srvip;
	public $timezone;
	public $lines;
	public $config_files_override;

        public static function get_modules_path() {
            return self::$modules_path;
        }

        public static function set_modules_path($path) {
            self::$modules_path = $path;
        }



	//Open configuration files and return the data from the file
	function open_config_file($cfg_file){		
		//if there is no configuration file over ridding the default then load up $contents with the file's information, where $key is the name of the default configuration file
		if(!isset($this->config_files_override[$cfg_file])) {
			$hd_file=self::$modules_path. static::$brand_name ."/". static::$family_line ."/".$cfg_file;
			//always use 'rb' says php.net
			$handle = fopen($hd_file, "rb");
			$contents = fread($handle, filesize($hd_file));
			fclose($handle);
			return($contents);
		} else {			
			return($this->config_files_override[$cfg_file]);
		}
	}
	
	function parse_config_file($file_contents,$keep_unknown=FALSE) {
		$family_data = $this->xml2array(self::$modules_path. static::$brand_name ."/". static::$family_line ."/family_data.xml");

		if(is_array($family_data['data']['model_list'])) {
			$key = $this->arraysearchrecursive($this->model, $family_data, "model");
			$line_total = $family_data['data']['model_list'][$key[2]]['lines'];
		} else {
			$line_total = $family_data['data']['model_list']['lines'];
		}
		
		if(!($line_total > 0)) {
			die("INVALID MODEL");
		} 
				
		$data = $this->parse_lines($line_total,$file_contents,$keep_unknown=FALSE);
		$data = $this->parse_config_values($data);
		
		return $data;
	}

	function parse_lines($line_total,$file_contents,$keep_unknown=FALSE) {
		//Find line looping data		
		$pattern = "/{line_loop}(.*?){\/line_loop}/si";
	    while(preg_match($pattern, $file_contents, $matches)) {
			$i = 1;
			$parsed = "";
			while($i <= $line_total) {
				if(isset($this->ext['line'][$i])) {						
					$parsed_2 = $this->replace_static_variables($matches[1],$i,TRUE);
					$parsed .= $this->parse_config_values($parsed_2,TRUE,$i);
				}
				$i++;
			}
			$file_contents = preg_replace($pattern, $parsed, $file_contents, 1);
		}
		
		
		
		$i = 1;
		while($i <= $line_total) {
			if(isset($this->ext['line'][$i])) {
				$file_contents = $this->replace_static_variables($file_contents,$i,FALSE);
			}
			$i++;
		}
		
		$file_contents = $this->replace_static_variables($file_contents,"GLOBAL");
				
		return($file_contents);
	}

	/*
	-Send this function the contents of a text file to $file_contents and then send an array to $custom_cfg_data
	The key of the array is the name of the variable in the text file between "{$" and "}"
	Then return the contents of the file with the values merged into it
	-The $keep_unknown variable tells this function to ignore(TRUE) or blank out(FALSE) variables that it finds in $file_contents but can not find in $variables_array
	*/
	function parse_config_values($file_contents,$keep_unknown=FALSE,$line="GLOBAL") {		
		$family_data = $this->xml2array(self::$modules_path. static::$brand_name ."/". static::$family_line ."/family_data.xml");

		if(is_array($family_data['data']['model_list'])) {
			$key = $this->arraysearchrecursive($this->model, $family_data, "model");
			$template_data_list = $family_data['data']['model_list'][$key[2]]['template_data'];
		} else {
			$template_data_list = $family_data['data']['model_list']['template_data'];
		}
		
		$template_data = array();
		if(is_array($template_data_list['files'])) {
			foreach($template_data_list['files'] as $files) {
				if(file_exists(self::$modules_path. static::$brand_name ."/". static::$family_line ."/".$files)) {
					$template_data_multi = $this->xml2array(self::$modules_path. static::$brand_name ."/". static::$family_line ."/".$files);
					$template_data_multi = $this->fix_single_array_keys($template_data_multi['template_data']['item']);					
					$template_data = array_merge($template_data, $template_data_multi);
				}
			}
		} else {
			if(file_exists(self::$modules_path. static::$brand_name ."/". static::$family_line ."/".$template_data_list['files'])) {
				$template_data = $this->xml2array(self::$modules_path. static::$brand_name ."/". static::$family_line ."/".$template_data_list['files']);
				$template_data = $this->fix_single_array_keys($template_data_multi['template_data']['item']);
			}
			
		}
		
		if(file_exists(self::$modules_path. static::$brand_name ."/". static::$family_line ."/template_data_custom.xml")) {
			$template_data_multi = $this->xml2array(self::$modules_path. static::$brand_name ."/". static::$family_line ."/template_data_custom.xml");
			$template_data_multi = $this->fix_single_array_keys($template_data_multi['template_data']['item']);
			$template_data = array_merge($template_data, $template_data_multi);
		}
		
		if(file_exists(self::$modules_path. static::$brand_name ."/". static::$family_line ."/template_data_".$this->model."_custom.xml")) {
			$template_data_multi = $this->xml2array(self::$modules_path. static::$brand_name ."/". static::$family_line ."/template_data_".$this->model."_custom.xml");
			$template_data_multi = $this->fix_single_array_keys($template_data_multi['template_data']['item']);
			$template_data = array_merge($template_data, $template_data_multi);
		}		
		

		
		//Find all matched variables in the text file between "{$" and "}"
		preg_match_all('/[{\$](.*?)[}]/i',$file_contents,$match);
		//Result without brackets (but with the $ variable identifier)
		$no_brackets = array_values(array_unique($match[1]));
		//Result with brackets
		$brackets = array_values(array_unique($match[0]));
		
		
		//loop though each variable found in the text file
		foreach($no_brackets as $variables) {
			$variables = str_replace("$","",$variables);
						
			//Users can set defaults within template files with pipes, they will over-ride whatever is in the XML file.
			if(strstr($variables,"|")) {
				$variables = explode("|",$variables);
				$default = $variables[1];
				$variables = $variables[0];
			} else {
				unset($default);
			}
			
			if(strstr($variables,".")) {
				$variables = explode(".",$variables);
				$line = $variables[2];
				$variables = $variables[0];
			}
						
			//If the variable we found in the text file exists in the variables array then replace the variable in the text file with the value under our key
			if (($line == "GLOBAL") AND (isset($this->xml_variables['line']['global'][$variables]['value']))) {
				$this->xml_variables['line']['global'][$variables]['value'] = htmlspecialchars($this->xml_variables['line']['global'][$variables]['value']);

				$this->xml_variables['line']['global'][$variables]['value'] = $this->replace_static_variables($this->xml_variables['line']['global'][$variables]['value']);
			
				if (isset($default)) {
					$file_contents=str_replace('{$'.$variables.'|'.$default.'}', $this->xml_variables['line']['global'][$variables]['value'],$file_contents);
				} else {
					$file_contents=str_replace('{$'.$variables.'}', $this->xml_variables['line']['global'][$variables]['value'],$file_contents);
				}
		
			} elseif(($line != "GLOBAL") AND (isset($this->xml_variables['line'][$line][$variables]['value']))) {
				$this->xml_variables['line'][$line][$variables]['value'] = htmlspecialchars($this->xml_variables['line'][$line][$variables]['value']);

				$this->xml_variables['line'][$line][$variables]['value'] = $this->replace_static_variables($this->xml_variables['line'][$line][$variables]['value']);
			
				if (isset($default)) {
					$file_contents=str_replace('{$'.$variables.'|'.$default.'}', $this->xml_variables['line'][$line][$variables]['value'],$file_contents);
				} else {
					$file_contents=str_replace('{$'.$variables.'}', $this->xml_variables['line'][$line][$variables]['value'],$file_contents);
				}
			} else {
				if(!$keep_unknown) {
					//read default template values here, blank unknowns or arrays (which are blanks anyways)
					$key1 = $this->arraysearchrecursive('$'.$variables, $template_data, 'variable');

					if (isset($default)) {
						$file_contents=str_replace('{$'.$variables.'|'.$default.'}', $default,$file_contents);
					}elseif((isset($template_data[$key1[0]]['default_value'])) AND (!is_array($template_data[$key1[0]]['default_value']))) {
						$file_contents=str_replace('{$'.$variables.'}', $template_data[$key1[0]]['default_value'],$file_contents);
					} else {
						$file_contents=str_replace('{$'.$variables.'}', "",$file_contents);
					}
				}
			}
		}
		
		
		return $file_contents;
		
	}
	
	function replace_static_variables($contents,$line="GLOBAL",$looping=TRUE) {
		$contents=str_replace('{$srvip}', $this->srvip, $contents,$count);		
		$contents=str_replace('{$mac}', $this->mac, $contents,$count);
		$contents=str_replace('{$model}', $this->model, $contents,$count);
		$contents=str_replace('{$gmtoff}', $this->timezone, $contents,$count);
		$contents=str_replace('{$gmthr}', $this->timezone, $contents,$count);
		$contents=str_replace('{$timezone}', $this->timezone, $contents,$count);
		
		if(($line != "GLOBAL") AND ($looping == TRUE)) {
			$contents = str_replace('{$line}',$line,$contents,$count);
			$contents=str_replace('{$ext}', $this->ext['line'][$line], $contents,$count);
			$contents=str_replace('{$displayname}', $this->displayname['line'][$line], $contents,$count);
			$contents=str_replace('{$secret}', $this->secret['line'][$line], $contents,$count);
			$contents=str_replace('{$pass}', $this->secret['line'][$line], $contents,$count);
		} elseif (($line != "GLOBAL") AND ($looping == FALSE)) {
			$contents=str_replace('{$line.line.'.$line.'}',$line,$contents,$count);
			$contents=str_replace('{$ext.line.'.$line.'}', $this->ext['line'][$line], $contents,$count);
			$contents=str_replace('{$displayname.line.'.$line.'}', $this->displayname['line'][$line], $contents,$count);
			$contents=str_replace('{$secret.line.'.$line.'}', $this->secret['line'][$line], $contents,$count);
			$contents=str_replace('{$pass.line.'.$line.'}', $this->secret['line'][$line], $contents,$count);
		}

		return($contents);
	}
	
	function fix_single_array_keys($array) {
		if(empty($array[0])) {
			$array_n[0] = $array;
			return($array_n);
		} else {
			return($array);
		}
		
	}
	
	function arraysearchrecursive($Needle,$Haystack,$NeedleKey="",$Strict=false,$Path=array()) {
	  if(!is_array($Haystack))
	    return false;
	  foreach($Haystack as $Key => $Val) {
	    if(is_array($Val)&&
	       $SubPath=$this->arraysearchrecursive($Needle,$Val,$NeedleKey,$Strict,$Path)) {
	      $Path=array_merge($Path,Array($Key),$SubPath);
	      return $Path;
	    }
	    elseif((!$Strict&&$Val==$Needle&&
	            $Key==(strlen($NeedleKey)>0?$NeedleKey:$Key))||
	            ($Strict&&$Val===$Needle&&
	             $Key==(strlen($NeedleKey)>0?$NeedleKey:$Key))) {
	      $Path[]=$Key;
	      return $Path;
	    }
	  }
	  return false;
	}
	
	function xml2array($url, $get_attributes = 1, $priority = 'tag')
	{
		$contents = "";
		if (!function_exists('xml_parser_create'))
		{
			return array ();
		}
		$parser = xml_parser_create('');
		if(!($fp = @ fopen($url, 'rb')))
		{
			return array ();
		}
		while(!feof($fp))
		{
			$contents .= fread($fp, 8192);
		}
		fclose($fp);
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);
		if(!$xml_values)
		{
			return; //Hmm...
		}
		$xml_array = array ();
		$parents = array ();
		$opened_tags = array ();
		$arr = array ();
		$current = & $xml_array;
		$repeated_tag_index = array (); 
		foreach ($xml_values as $data)
		{
			unset ($attributes, $value);
			extract($data);
			$result = array ();
			$attributes_data = array ();
			if (isset ($value))
			{
				if($priority == 'tag')
				{
					$result = $value;
				}
				else
				{
					$result['value'] = $value;
				}
			}
			if(isset($attributes) and $get_attributes)
			{
				foreach($attributes as $attr => $val)
				{
					if($priority == 'tag')
					{
						$attributes_data[$attr] = $val;
					}
					else
					{
						$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
					}
				}
			}
			if ($type == "open")
			{ 
				$parent[$level -1] = & $current;
				if(!is_array($current) or (!in_array($tag, array_keys($current))))
				{
					$current[$tag] = $result;
					if($attributes_data)
					{
						$current[$tag . '_attr'] = $attributes_data;
					}
					$repeated_tag_index[$tag . '_' . $level] = 1;
					$current = & $current[$tag];
				}
				else
				{
					if (isset ($current[$tag][0]))
					{
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						$repeated_tag_index[$tag . '_' . $level]++;
					}
					else
					{ 
						$current[$tag] = array($current[$tag],$result); 
						$repeated_tag_index[$tag . '_' . $level] = 2;
						if(isset($current[$tag . '_attr']))
						{
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset ($current[$tag . '_attr']);
						}
					}
					$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
					$current = & $current[$tag][$last_item_index];
				}
			}
			else if($type == "complete")
			{
				if(!isset ($current[$tag]))
				{
					$current[$tag] = $result;
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if($priority == 'tag' and $attributes_data)
					{
						$current[$tag . '_attr'] = $attributes_data;
					}
				}
				else
				{
					if (isset ($current[$tag][0]) and is_array($current[$tag]))
					{
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						if ($priority == 'tag' and $get_attributes and $attributes_data)
						{
							$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
						$repeated_tag_index[$tag . '_' . $level]++;
					}
					else
					{
						$current[$tag] = array($current[$tag],$result); 
						$repeated_tag_index[$tag . '_' . $level] = 1;
						if ($priority == 'tag' and $get_attributes)
						{
							if (isset ($current[$tag . '_attr']))
							{ 
								$current[$tag]['0_attr'] = $current[$tag . '_attr'];
								unset ($current[$tag . '_attr']);
							}
							if ($attributes_data)
							{
								$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
					}
				}
			}
			else if($type == 'close')
			{
				$current = & $parent[$level -1];
			}
		}
		return ($xml_array);
	}
	
}

