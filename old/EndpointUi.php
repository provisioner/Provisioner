<?php

class EndpointUi {
	public static $moduleDir = '';

	public static function findModules() {
		// This list will come from whatever directories exist inside $moduleDir that contain
		// We will scrape info from brand_data.xml in each directory

		return array('yealink');
	}

	public static function JsList() {
		// Return a list of JS files to include, relative paths to this file
		$js = array();

		$modules = self::findModules();

		foreach ($modules as $module) {
			$js[] = self::$moduleDir . $module . '/js/' . $module . '.js';
		}

		return $js;
	}

	public static function CssList() {
		// Return a list of CSS files to include, relative paths to this file
		$css = array();

                $modules = self::findModules();
                
                foreach ($modules as $module) {
                        $css[] = self::$moduleDir . $module . '/css/' . $module . '.css';
                }

                return $css;
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

    function arraysearchrecursivemulti($Needle, $Haystack, $NeedleKey="", $Strict=false, $Path=array()) {
        if (!is_array($Haystack))
            return false;
		$i = 0;
		$final = array();
        foreach ($Haystack as $Key => $Val) {
            if (is_array($Val) &&
                    $SubPath = $this->arraysearchrecursivemulti($Needle, $Val, $NeedleKey, $Strict, $Path)) {
                $Path = array_merge($Path, Array($Key), $SubPath);
				$final[$i] = $Path;
				$Path = array();
				$i++;
                //return $Path;
            } elseif ((!$Strict && $Val == $Needle &&
                    $Key == (strlen($NeedleKey) > 0 ? $NeedleKey : $Key)) ||
                    ($Strict && $Val === $Needle &&
                    $Key == (strlen($NeedleKey) > 0 ? $NeedleKey : $Key))) {
                $Path[] = $Key;
				$final[$i] = $Path;
				$Path = array();
				$i++;
                //return $Path;
            }
        }
		if(!empty($final)) {
			return $final;
		} else {
			return false;
		}
    }

	function generate_gui_html($cfg_data,$custom_cfg_data=NULL, $admin=FALSE, $user_cfg_data=NULL) {

	    $count = count($cfg_data);
	
	    //Check to see if there is a custom template for this phone already listed in the endpointman_mac_list database
	    if (isset($custom_cfg_data)) {
	        $custom_cfg_data = unserialize($custom_cfg_data);
	    } else {
	        //No custom template so let's pull the default values for this model into the custom_cfg_data array and populate it from there so that we won't have to make two completely different functions below
	        foreach($cfg_data as $key => $data) {
	            if(($data['type'] != 'group') && ($data['type'] != 'break')) {
	                $key_default = str_replace('$','',$data['variable']);
	                if(!is_array($data['default_value'])) {
	                    $custom_cfg_data[$key_default]['value'] = $data['default_value'];
	                } else {
	                    $custom_cfg_data[$key_default]['value'] = "";
	                }
	            }
	        }
	    }
	    if(isset($user_cfg_data)) {
	        $user_cfg_data = unserialize($user_cfg_data);
	    }

	    $template_variables_array = array();

	    $group_count = 0;
	    //Fill the html form data with values from either the database or the default values to display to the end user
	    for($i=0;$i<$count;$i++) {
	        if(array_key_exists('variable',$cfg_data[$i])) {
	            $key = str_replace('$','',$cfg_data[$i]['variable']);
	        } else {
	            $key = "";
	        }
	        if(($admin) OR (isset($custom_cfg_data[$key]['ari']))) {
	            //Checks to see if values are defined in the database, if not then we assume this is a new option and we need a default value here!
	            if(!isset($custom_cfg_data[$key]['value'])) {
	                //xml2array will take values that have no data and turn them into arrays, we want to avoid the word 'array' as a default value, so we blank it out here if we are an array
	                if((array_key_exists('default_value',$cfg_data[$i])) AND (is_array($cfg_data[$i]['default_value']))) {
	                    $custom_cfg_data[$key]['value'] = "";
	                } elseif((array_key_exists('default_value',$cfg_data[$i])) AND (!is_array($cfg_data[$i]['default_value']))) {
	                    $custom_cfg_data[$key]['value'] = $cfg_data[$i]['default_value'];
	                }
	            }
	            if ($cfg_data[$i]['type'] == "group") {
	                $group_count++;
	                $template_variables_array[$group_count]['title'] = $cfg_data[$i]['description'];
	                $variables_count = 0;
	            } elseif ($cfg_data[$i]['type'] == "input") {
	                if((!$admin) && (isset($user_cfg_data[$key]['value']))) {
	                    $custom_cfg_data[$key]['value'] = $user_cfg_data[$key]['value'];
	                }
	                $template_variables_array[$group_count]['data'][$variables_count]['type'] = "input";
	                $template_variables_array[$group_count]['data'][$variables_count]['key'] = $key;
	                $template_variables_array[$group_count]['data'][$variables_count]['value'] = $custom_cfg_data[$key]['value'];
	                $template_variables_array[$group_count]['data'][$variables_count]['description'] = $cfg_data[$i]['description'];
	            } elseif ($cfg_data[$i]['type'] == "radio") {
	                if((!$admin) && (isset($user_cfg_data[$key]['value']))) {
	                    $custom_cfg_data[$key]['value'] = $user_cfg_data[$key]['value'];
	                }
	                $num = $custom_cfg_data[$key]['value'];
	                $template_variables_array[$group_count]['data'][$variables_count]['type'] = "radio";
	                $template_variables_array[$group_count]['data'][$variables_count]['key'] = $key;
	                $template_variables_array[$group_count]['data'][$variables_count]['description'] = $cfg_data[$i]['description'];
	                $z = 0;
	                while($z < count($cfg_data[$i]['data'])) {
	                    $template_variables_array[$group_count]['data'][$variables_count]['data'][$z]['key'] = $key;
	                    $template_variables_array[$group_count]['data'][$variables_count]['data'][$z]['value'] = $cfg_data[$i]['data'][$z]['value'];
	                    $template_variables_array[$group_count]['data'][$variables_count]['data'][$z]['description'] = $cfg_data[$i]['data'][$z]['text'];
	                    if ($cfg_data[$i]['data'][$z]['value'] == $num) {
	                        $template_variables_array[$group_count]['data'][$variables_count]['data'][$z]['checked'] = 'checked';
	                    }
	                    $z++;
	                }
	            } elseif ($cfg_data[$i]['type'] == "list") {
	                if((!$admin) && (isset($user_cfg_data[$key]['value']))) {
	                    $custom_cfg_data[$key]['value'] = $user_cfg_data[$key]['value'];
	                }
	                $num = $custom_cfg_data[$key]['value'];
	                $template_variables_array[$group_count]['data'][$variables_count]['type'] = "list";
	                $template_variables_array[$group_count]['data'][$variables_count]['key'] = $key;
	                $template_variables_array[$group_count]['data'][$variables_count]['description'] = $cfg_data[$i]['description'];
	                $z = 0;
	                while($z < count($cfg_data[$i]['data'])) {
	                    $template_variables_array[$group_count]['data'][$variables_count]['data'][$z]['value'] = $cfg_data[$i]['data'][$z]['value'];
	                    $template_variables_array[$group_count]['data'][$variables_count]['data'][$z]['description'] = $cfg_data[$i]['data'][$z]['text'];
	                    if ($cfg_data[$i]['data'][$z]['value'] == $num) {
	                        $template_variables_array[$group_count]['data'][$variables_count]['data'][$z]['selected'] = 'selected';
	                    }
	                    $z++;
	                }
	            } elseif ($cfg_data[$i]['type'] == "break") {
	                $template_variables_array[$group_count]['data'][$variables_count]['type'] = "break";
	            }
	            if(($global_cfg['enable_ari']) AND ($admin) AND ($cfg_data[$i]['type'] != "break") AND ($cfg_data[$i]['type'] != "group")) {
	                $template_variables_array[$group_count]['data'][$variables_count]['aried'] = 1;
	                $template_variables_array[$group_count]['data'][$variables_count]['ari']['key'] = $key;
	                if(isset($custom_cfg_data[$key]['ari'])) {
	                    $template_variables_array[$group_count]['data'][$variables_count]['ari']['checked'] = "checked";
	                }
	            }
	            $variables_count++;
	        }
	    }

	    return($template_variables_array);
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

	function generate_html($var){
			if( isset( $var["template_editor"] ) ){
				$counter1 = 0;
				foreach( $var["template_editor"] as $key1 => $value1 ){ 
		?>
		                    <div class="panel">
		                        <div class="panel-wrapper">
		                            <h2 class="title"><?php echo $value1["title"];?></h2>
		                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
							<?php
				if( isset( $value1["data"] ) ){
					$counter2 = 0;
					foreach( $value1["data"] as $key2 => $value2 ){ 
		?>
		                                <tr>
		                                    <td nowrap>
								<?php
					if( $value2["type"] == 'input' ){
		?>
		                                        <label><?php echo $value2["description"];?>: <input type='text' name='<?php echo $value2["key"];?>' value='<?php echo $value2["value"];?>' size="40"></label>
								<?php
					}
						elseif( $value2["type"] == 'radio' ){
		?>
									<?php echo $value2["description"];?>
									<?php
							if( isset( $value2["data"] ) ){
								$counter3 = 0;
								foreach( $value2["data"] as $key3 => $value3 ){ 
		?>
		                                        <label><?php echo $value3["description"];?>: <input type='radio' name='<?php echo $value3["key"];?>' value='<?php echo $value3["value"];?>' <?php
								if( array_key_exists('checked',$value3) ){
		?><?php echo $value3["checked"];?><?php
								}
		?>></label>
									<?php
									$counter3++;
								}
							}
		?>
								<?php
						}
							elseif( $value2["type"] == 'list' ){
		?>
									<?php echo $value2["description"];?> <select name='<?php echo $value2["key"];?>'>
									<?php
								if( isset( $value2["data"] ) ){
									$counter3 = 0;
									foreach( $value2["data"] as $key3 => $value3 ){ 
		?>
		                                            <option value='<?php echo $value3["value"];?>' <?php
									if( array_key_exists('selected',$value3) ){
		?><?php echo $value3["selected"];?><?php
									}
		?>><?php echo $value3["description"];?></option>
									<?php
										$counter3++;
									}
								}
		?>
		                                        </select>
								<?php
							}
		?>
		                                    </td><td width="90%">
								<?php
							if( isset($value2["aried"]) ){
		?>
		                                        <label><input type='checkbox' name='ari_<?php echo $value2["ari"]["key"];?>' <?php
								if( isset($value2["ari"]["checked"]) ){
		?><?php echo $value2["ari"]["checked"];?><?php
								}
		?>><?=_('End User Editable (Through ARI Module)')?></label>
								<?php
							}
		?>
		                                    </td>
		                                </tr>
								<?php
							if( $value2["type"] == 'break' ){
		?>
		                                <tr>
		                                    <td>&nbsp;</td>
		                                    <td>&nbsp;</td>
		                                </tr>
								<?php
							}
		?>
							<?php
								$counter2++;
							}
						}
		?>
		                            </table>
		                        </div>
		                    </div>
				<?php
							$counter1++;
						}
					}
		
	}
}

