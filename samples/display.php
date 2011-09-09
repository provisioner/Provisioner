<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$brand = $_REQUEST['brand'];
$product_model = explode('+',$_REQUEST['model_demo']);
$mac = isset($_REQUEST['mac']) ? $_REQUEST['mac'] : '';
$server = isset($_REQUEST['server']) ? $_REQUEST['server'] : '';
$timezone = isset($_REQUEST['timezone']) ? $_REQUEST['timezone'] : '';
$proxyserver = isset($_REQUEST['proxyserver']) ? $_REQUEST['proxyserver'] : '';

$product = $product_model[0];
$model = $product_model[1];

$json_data = json_decode(file_get_contents('http://www.provisioner.net/repo/xml2json.php?request=data&brand='.$brand.'&product='.$product.'&model='.$model),true);

$html_array = generate_gui_html($json_data,$_REQUEST['regs']);


?>
<form name="form1" method="post" action="process.php">
<?php
foreach($html_array as $sections) {
	echo "<h1>".$sections['title']."</h1>";
	foreach($sections['data'] as $html_els) {
		switch($html_els['type']) {
			case 'input':
				echo $html_els['description'].': <input type="text" name="'.$html_els['key'].'" value="'.fix_single_array_keys($html_els['value'],TRUE).'"/><br />';
				break;
			case 'break':
				echo '<br/>';
				break;
			case 'list':
				echo $html_els['description']."<select name='".$html_els['key']."'>";
				foreach($html_els['data'] as $list) {
					  $selected = ($html_els['value'] == $list['value']) ? 'selected' : '';
					  echo '<option value="'.$list['value'].'" '.$selected.'>'.$list['description'].'</option>';
				}
				echo "</select><br />";
				break;
			case 'radio':
				echo $html_els['description'].':';
				foreach($html_els['data'] as $list) {
					$checked = isset($list['checked']) ? 'checked' : '';
					echo '|<input type="radio" name="'.$list['key'].'" value="'.$list['key'].'" '.$checked.'/>'.$list['description'];
				}
				echo '<br />';
				break;
			case 'checkbox':
				echo $html_els['description'].': <input type="checkbox" name="'.$html_els['key'].'" value="'.$html_els['key'].'"/><br />';
				break;	
		}
	}
}
?>
<input type="hidden" id="brand" name="brand" value="<?php echo $brand;?>" />
<input type="hidden" id="product" name="product" value="<?php echo $product;?>" />
<input type="hidden" id="model" name="model" value="<?php echo $model;?>" />
<input type="hidden" id="mac" name="mac" value="<?php echo $mac;?>" />
<input type="hidden" id="server" name="server" value="<?php echo $server;?>" />
<input type="hidden" id="timezone" name="timezone" value="<?php echo $timezone;?>" />
<input type="hidden" id="proxyserver" name="proxyserver" value="<?echo $proxyserver;?>">
<input type="submit" value="Submit" />
</form>
<?php
//echo generate_gui_html();

/**
 * Function xml2array has a bad habit of returning blank xml values as empty arrays.
 * Also if the xml children only loops once then the array is put into a normal array (array[variable]).
 * However if it loops more than once then it is put into a counted array (array[0][variable])
 * We fix that issue here by returning blank values on empty arrays or always returning array[0]
 * @param array $array
 * @return mixed
 * @author Karl Anderson
 */
function fix_single_array_keys($array,$variable=FALSE) {
	if(is_array($array) && $variable) {
		return $array[0];
	}
	
    if (!is_array($array) && !$variable) {
		$array_n[0] = $array;
        return $array_n;
    }

    if((empty($array[0])) AND (!empty($array))) {
        $array_n[0] = $array;
        return($array_n);
    }

    return empty($array) ? '' : $array;
}

/**
 * Generates the Visual Display for the end user
 * @param <type> $cfg_data
 * @param <type> $custom_cfg_data
 * @param <type> $admin
 * @param <type> $user_cfg_data
 * @return <type>
 */
function generate_gui_html($cfg_data,$max_lines=3) {
    //take the data out of the database and turn it back into an array for use

	

    $template_variables_array = array();
    $group_count = 0;
    $variables_count = 0;

	for($a=1;$a <= $max_lines; $a++) {
	    $template_variables_array[$group_count]['title'] = "Line Information for Line ".$a;

		//Username (Auth Name)
		$key = "line_static|".$a."|ext";
		$items = array("variable" => "ext","default_value" => "", "description" => "Username/Auth [STATIC]", "type" => "input");
	    $template_variables_array[$group_count]['data'][$variables_count] = generate_form_data($variables_count,$items,$key);
	    $template_variables_array[$group_count]['data'][$variables_count]['looping'] = TRUE;
		$variables_count++;
		//Secret
		$key = "line_static|".$a."|secret";
		$items = array("variable" => "secret","default_value" => "", "description" => "Secret/Password [STATIC]", "type" => "input");		
	    $template_variables_array[$group_count]['data'][$variables_count] = generate_form_data($variables_count,$items,$key);
	    $template_variables_array[$group_count]['data'][$variables_count]['looping'] = TRUE;
		$variables_count++;
		//Display Name
		$key = "line_static|".$a."|displayname";
		$items = array("variable" => "displayname","default_value" => "", "description" => "Display Name [STATIC]", "type" => "input");
	    $template_variables_array[$group_count]['data'][$variables_count] = generate_form_data($variables_count,$items,$key);
	    $template_variables_array[$group_count]['data'][$variables_count]['looping'] = TRUE;
		$variables_count++;
	
		$key = "";
		$items = array("type" => "break");
	    $template_variables_array[$group_count]['data'][$variables_count] = generate_form_data($variables_count,$items,$key);
	    $template_variables_array[$group_count]['data'][$variables_count]['looping'] = TRUE;
		$variables_count++;
		$group_count++;
	}

	$line_count = 1;
    foreach($cfg_data['data'] as $key => $data) {
		$template_variables_array[$group_count]['title'] = $key;
		$variables_count = 0;
		foreach($data as $key2 => $data2) {
			foreach($data2 as $key3 => $data3) {
				preg_match('/(.*)\|(.*)/i',$key3,$matches);				
				$type = $matches[1];
				$variable = $matches[2];
				switch($type) {
					case "lineloop":
						if($line_count <= $max_lines) {
							$variables_count = 0;
							foreach($data3 as $items) {
								$a = $items['line_loop'];
								if(isset($items['description'])) {
									$items['description'] = str_replace('{$count}',$a,$items['description']);
									$key = $type."|".$a."|".str_replace('$','',$items['variable']);
								}
								$items[$variables_count] = $items;
								$template_variables_array[$group_count]['data'][$variables_count] = generate_form_data($variables_count,$items,$key);
								$template_variables_array[$group_count]['data'][$variables_count]['looping'] = TRUE;
								$variables_count++;
							}
						}
						$line_count++;
						break;
					case "loop":
						foreach($data3 as $items) {
							$a = $items['loop_count'];
							if(isset($items['description'])) {
								$items['description'] = str_replace('{$count}',$a,$items['description']);
								$key = $type."|".$a."|".str_replace('$','',$items['variable']);
							}
							$items[$variables_count] = $items;
							$template_variables_array[$group_count]['data'][$variables_count] = generate_form_data($variables_count,$items,$key);
							$template_variables_array[$group_count]['data'][$variables_count]['looping'] = TRUE;
							$variables_count++;
						}
						break;
					case "option":
						if(isset($data3['description'])) {
							$data3['description'] = str_replace('{$count}',$a,$data3['description']);
							$key = $type."|".str_replace('$','',$data3['variable']);
							$template_variables_array[$group_count]['data'][$variables_count] = generate_form_data($variables_count,$data3,$key);
							$variables_count++;
						}
					default:
						//echo $type."<br />";
						break;
				}
			}
		}
		$group_count++;
    }
    return($template_variables_array);
}

/**
 * Generate an array that will get parsed as HTML from an array of values from XML
 * @param int $i
 * @param array $cfg_data
 * @param string $key
 * @param array $custom_cfg_data
 * @return array
 */
function generate_form_data ($i,$cfg_data,$key=NULL) {
    switch ($cfg_data['type']) {
        case "input":
            $template_variables_array['type'] = "input";
            $template_variables_array['max_chars'] = isset($cfg_data['max_chars']) ? $cfg_data['max_chars'] : '';
            $template_variables_array['key'] = $key;
            $template_variables_array['value'] = isset($cfg_data['default_value']) && !empty($cfg_data['default_value']) ? $cfg_data['default_value'] : '';
            $template_variables_array['description'] = $cfg_data['description'];
            break;
        case "radio":
            $template_variables_array['type'] = "radio";
            $template_variables_array['key'] = $key;
            $template_variables_array['description'] = $cfg_data['description'];
            $template_variables_array['value'] = isset($cfg_data['default_value']) && !empty($cfg_data['default_value']) ? $cfg_data['default_value'] : '';
            $z = 0;
            while($z < count($cfg_data['data'])) {
                $template_variables_array['data'][$z]['key'] = $key;
                $template_variables_array['data'][$z]['value'] = $cfg_data['data'][$z]['value'];
                $template_variables_array['data'][$z]['description'] = $cfg_data['data'][$z]['text'];
                $z++;
            }
            break;
        case "list":
            $template_variables_array['type'] = "list";
            $template_variables_array['key'] = $key;
            $template_variables_array['description'] = $cfg_data['description'];
            $template_variables_array['value'] = isset($cfg_data['default_value']) && !empty($cfg_data['default_value']) ? $cfg_data['default_value'] : '';
            $z = 0;
            while($z < count($cfg_data['data'])) {
                $template_variables_array['data'][$z]['value'] = $cfg_data['data'][$z]['value'];
                $template_variables_array['data'][$z]['description'] = $cfg_data['data'][$z]['text'];
                if (isset($cfg_data['data'][$z]['disable'])) {
                    $cfg_data['data'][$z]['disable'] = str_replace('{$count}', $z, $cfg_data['data'][$z]['disable']);
                    $template_variables_array['data'][$z]['disables'] = explode(",", $cfg_data['data'][$z]['disable']);
                }
                if (isset($cfg_data['data'][$z]['enable'])) {
                    $cfg_data['data'][$z]['enable'] = str_replace('{$count}', $z, $cfg_data['data'][$z]['enable']);
                    $template_variables_array['data'][$z]['enables'] = explode(",", $cfg_data['data'][$z]['enable']);
                }
                $z++;
            }
            break;
        case "checkbox":
            $template_variables_array['type'] = "checkbox";
            $template_variables_array['key'] = $key;
            $template_variables_array['description'] = $cfg_data['description'];
            $template_variables_array['value'] = isset($cfg_data['default_value']) && !empty($cfg_data['default_value']) ? $cfg_data['default_value'] : '';
            $z = 0;
            while($z < count($cfg_data['data'])) {
                $template_variables_array['data'][$z]['key'] = $key;
                $template_variables_array['data'][$z]['value'] = $cfg_data['data'][$z]['value'];
                $template_variables_array['data'][$z]['description'] = $cfg_data['data'][$z]['text'];
                $z++;
            }
            break;
        case "file";
            $template_variables_array['type'] = "file";
            $template_variables_array['value'] = isset($cfg_data['default_value']) && !empty($cfg_data['default_value']) ? $cfg_data['default_value'] : '';
            if(isset($cfg_data['max_chars'])) {
                $template_variables_array['max_chars'] = $cfg_data['max_chars'];
            }
            $template_variables_array['key'] = $key;
            $template_variables_array['value'] = '';
            $template_variables_array['description'] = $cfg_data['description'];
            break;
        case "textarea":
            $template_variables_array['type'] = "textarea";
            $template_variables_array['value'] = isset($cfg_data['default_value']) && !empty($cfg_data['default_value']) ? $cfg_data['default_value'] : '';
            if(isset($cfg_data['max_chars'])) {
                $template_variables_array['max_chars'] = $cfg_data['max_chars'];
            }
            $template_variables_array['key'] = $key;
            $template_variables_array['value'] = '';
            $template_variables_array['description'] = $cfg_data['description'];
            break;
        case "break":
            $template_variables_array['type'] = "break";
           break;
        default:
            $template_variables_array['type'] = "NA";
            break;
    }

if(isset($cfg_data['description_attr']['tooltip'])) {
    $template_variables_array['tooltip'] = $cfg_data['description_attr']['tooltip'];
}
    return($template_variables_array);
}

/**
 * Save template from the template view pain
 * @param int $id Either the MAC ID or Template ID
 * @param int $custom Either 0 or 1, it determines if $id is MAC ID or Template ID
 * @param array $variables The variables sent from the form. usually everything in $_REQUEST[]
 * @return string Location of area to return to in Endpoint Manager
 */
function save_template($id, $custom, $variables) {
    //Custom Means specific to that MAC
    //This function is reversed. Not sure why
    if($custom != "0") {
        $sql = "SELECT endpointman_model_list.max_lines, endpointman_product_list.config_files, endpointman_mac_list.*, endpointman_product_list.id as product_id, endpointman_product_list.long_name, endpointman_model_list.template_data, endpointman_product_list.cfg_dir, endpointman_brand_list.directory FROM endpointman_brand_list, endpointman_mac_list, endpointman_model_list, endpointman_product_list WHERE endpointman_mac_list.id=".$id." AND endpointman_mac_list.model = endpointman_model_list.id AND endpointman_model_list.brand = endpointman_brand_list.id AND endpointman_model_list.product_id = endpointman_product_list.id";
    } else {
        $sql = "SELECT endpointman_model_list.max_lines, endpointman_brand_list.directory, endpointman_product_list.cfg_dir, endpointman_product_list.config_files, endpointman_product_list.long_name, endpointman_model_list.template_data, endpointman_model_list.id as model_id, endpointman_template_list.* FROM endpointman_brand_list, endpointman_product_list, endpointman_model_list, endpointman_template_list WHERE endpointman_product_list.id = endpointman_template_list.product_id AND endpointman_brand_list.id = endpointman_product_list.brand AND endpointman_template_list.model_id = endpointman_model_list.id AND endpointman_template_list.id = ".$id;
    }

    //Load template data
    //$row = db->getRow($sql, array(), DB_FETCHMODE_ASSOC);

    $cfg_data = unserialize($row['template_data']);
    $count = count($cfg_data);

    $custom_cfg_data_ari = array();

    foreach($cfg_data as $data) {
        $data = fix_single_array_keys($data['category']);
        foreach($data as $cats) {
            $cats = fix_single_array_keys($cats['subcategory']);
            foreach($cats as $subcats) {
                $items = fix_single_array_keys($subcats['item']);
                foreach($items as $config_options) {
                    if(array_key_exists('variable',$config_options)) {
                        $temping = str_replace('$','',$config_options['variable']);
                        $temping_ari = "ari_" . $temping;
                        if(array_key_exists($temping, $_REQUEST)) {
                            $custom_cfg_data[$temping] = $_REQUEST[$temping];
                            if(array_key_exists($temping_ari, $_REQUEST)) {
                                if($_REQUEST[$temping_ari] == "on") {
                                    $custom_cfg_data_ari[$temping] = 1;
                                }
                            }
                        }
                    } elseif ($config_options['type'] == 'loop') {
                        $loop_start = $config_options['loop_start'];
                        $loop_end = $config_options['loop_end'];
                        $variables_count = 0;
                        for($a=$loop_start;$a<=$loop_end;$a++) {
                            foreach($config_options['data']['item'] as $items) {
                                if(isset($items['description'])) {
                                    $items['description'] = str_replace('{$count}',$a,$items['description']);
                                    $temping = "loop|".str_replace('$','',$items['variable'])."_".$a;
                                    $temping_ari = "ari_" . $temping;
                                    if(array_key_exists($temping, $_REQUEST)) {
                                        $custom_cfg_data[$temping] = $_REQUEST[$temping];
                                        if(array_key_exists($temping_ari, $_REQUEST)) {
                                            if($_REQUEST[$temping_ari] == "on") {
                                                $custom_cfg_data_ari[$temping] = 1;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } elseif ($config_options['type'] == 'loop_line_options') {
                        for($a=1;$a<=$row['max_lines'];$a++) {
                            foreach($config_options['data']['item'] as $items) {
                                if(isset($items['description'])) {
                                    $items['description'] = str_replace('{$count}',$a,$items['description']);
                                    $temping = "line|".$a."|".str_replace('$','',$items['variable']);
                                    $temping_ari = "ari_" . $temping;
                                    if(array_key_exists($temping, $_REQUEST)) {
                                        $custom_cfg_data[$temping] = $_REQUEST[$temping];
                                        if(array_key_exists($temping_ari, $_REQUEST)) {
                                            if($_REQUEST[$temping_ari] == "on") {
                                                $custom_cfg_data_ari[$temping] = 1;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    $config_files = explode(",",$row['config_files']);

    $i = 0;
    while($i < count($config_files)) {
        $config_files[$i] = str_replace(".","_",$config_files[$i]);
        if(isset($_REQUEST[$config_files[$i]])) {
            $_REQUEST[$config_files[$i]] = explode("_",$_REQUEST[$config_files[$i]], 2);
            $_REQUEST[$config_files[$i]] = $_REQUEST[$config_files[$i]][0];
            if($_REQUEST[$config_files[$i]] > 0) {
                $config_files_selected[$config_files[$i]] = $_REQUEST[$config_files[$i]];
            }
        }
        $i++;
    }

    if(!isset($config_files_selected)) {
        $config_files_selected = "";
    } else {
        $config_files_selected = serialize($config_files_selected);
    }
    $custom_cfg_data_temp['data'] = $custom_cfg_data;
    $custom_cfg_data_temp['ari'] = $custom_cfg_data_ari;
    $save = serialize($custom_cfg_data_temp);

    if($custom == "0") {
        $sql = 'UPDATE endpointman_template_list SET config_files_override = \''.addslashes($config_files_selected).'\', global_custom_cfg_data = \''.addslashes($save).'\' WHERE id ='.$id;
        $location = "template_manager";
    } else {
        $sql = 'UPDATE endpointman_mac_list SET config_files_override = \''.addslashes($config_files_selected).'\', template_id = 0, global_custom_cfg_data = \''.addslashes($save).'\' WHERE id ='.$id;
        $location = "devices_manager";
    }

    //db->query($sql);

    $phone_info = array();

    if($custom != 0) {
        $phone_info = get_phone_info($id);
        if(isset($_REQUEST['epm_reboot'])) {
            prepare_configs($phone_info);
        } else {
            prepare_configs($phone_info,FALSE);
        }
    } else {
        $sql = 'SELECT id FROM endpointman_mac_list WHERE template_id = '.$id;
        //$phones = db->getAll($sql, array(), DB_FETCHMODE_ASSOC);
        foreach($phones as $data) {
            $phone_info = get_phone_info($data['id']);
            if(isset($_REQUEST['epm_reboot'])) {
                prepare_configs($phone_info);
            } else {
                prepare_configs($phone_info,FALSE);
            }
        }
    }

    if(isset($_REQUEST['silent_mode'])) {
        echo '<script language="javascript" type="text/javascript">window.close();</script>';
    } else {
        return($location);
    }

}

/**
 * xml2array() will convert the given XML text to an array in the XML structure.
 * @author http://www.php.net/manual/en/function.xml-parse.php#87920
 * @param sting $url the XML url (usually a local file)
 * @param boolean $get_attributes 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
 * @param string $priority Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
 * @return array The parsed XML in an array form.
 */
function xml2array($url, $get_attributes = 1, $priority = 'tag') {
    $contents = "";
    if (!function_exists('xml_parser_create')) {
        return array ();
    }
    $parser = xml_parser_create('');
    if(!($fp = @ fopen($url, 'rb'))) {
        return array ();
    }
    while(!feof($fp)) {
        $contents .= fread($fp, 8192);
    }
    fclose($fp);
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);
    if(!$xml_values) {
        return; //Hmm...
    }
    $xml_array = array ();
    $parents = array ();
    $opened_tags = array ();
    $arr = array ();
    $current = & $xml_array;
    $repeated_tag_index = array ();
    foreach ($xml_values as $data) {
        unset ($attributes, $value);
        extract($data);
        $result = array ();
        $attributes_data = array ();
        if (isset ($value)) {
            if($priority == 'tag') {
                $result = $value;
            }
            else {
                $result['value'] = $value;
            }
        }
        if(isset($attributes) and $get_attributes) {
            foreach($attributes as $attr => $val) {
                if($priority == 'tag') {
                    $attributes_data[$attr] = $val;
                }
                else {
                    $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                }
            }
        }
        if ($type == "open") {
            $parent[$level -1] = & $current;
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) {
                $current[$tag] = $result;
                if($attributes_data) {
                    $current[$tag . '_attr'] = $attributes_data;
                }
                $repeated_tag_index[$tag . '_' . $level] = 1;
                $current = & $current[$tag];
            }
            else {
                if (isset ($current[$tag][0])) {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    $repeated_tag_index[$tag . '_' . $level]++;
                }
                else {
                    $current[$tag] = array($current[$tag],$result);
                    $repeated_tag_index[$tag . '_' . $level] = 2;
                    if(isset($current[$tag . '_attr'])) {
                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                        unset ($current[$tag . '_attr']);
                    }
                }
                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                $current = & $current[$tag][$last_item_index];
            }
        }
        else if($type == "complete") {
            if(!isset ($current[$tag])) {
                $current[$tag] = $result;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                if($priority == 'tag' and $attributes_data) {
                    $current[$tag . '_attr'] = $attributes_data;
                }
            }
            else {
                if (isset ($current[$tag][0]) and is_array($current[$tag])) {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    if ($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level]++;
                }
                else {
                    $current[$tag] = array($current[$tag],$result);
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $get_attributes) {
                        if (isset ($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset ($current[$tag . '_attr']);
                        }
                        if ($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                }
            }
        }
        else if($type == 'close') {
            $current = & $parent[$level -1];
        }
    }
    return ($xml_array);
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
function arraysearchrecursive($Needle, $Haystack, $NeedleKey="", $Strict=false, $Path=array()) {
    if (!is_array($Haystack))
        return false;
    foreach ($Haystack as $Key => $Val) {
        if (is_array($Val) &&
                $SubPath = arraysearchrecursive($Needle, $Val, $NeedleKey, $Strict, $Path)) {
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