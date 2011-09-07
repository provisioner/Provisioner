<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$brand = $_REQUEST['brand'];
$product_model = explode('+',$_REQUEST['model_demo']);

$product = $product_model[0];
$model = $product_model[1];
$family_data = xml2array('../endpoint/'.$brand.'/'.$product.'/family_data.xml');
$found = arraysearchrecursive($model, $family_data['data']['model_list'], 'model');
$key = $found[0];
$files = fix_single_array_keys($family_data['data']['model_list'][$key]['template_data']['files']);
$template_data_array = array();
foreach($files as $data) {
    if(file_exists('../endpoint/'.$brand.'/'.$product.'/'.$data)) {
        $template_data_xml = xml2array('../endpoint/'.$brand.'/'.$product.'/'.$data);
        $template_data_xml = fix_single_array_keys($template_data_xml['template_data']);
        $template_data_array = array_merge($template_data_array, $template_data_xml);
    }
}
if(empty($template_data_array)) {
    die("No Template Data Found");
}
$html_array = generate_gui_html($template_data_array,NULL, TRUE, NULL,$_REQUEST['registrations']);
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
					  echo '<option value="'.$list['value'].'">'.$list['description'].'</option>';
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
function generate_gui_html($cfg_data,$custom_cfg_data=NULL, $admin=TRUE, $user_cfg_data=NULL,$max_lines=3,$ext=NULL) {
    //take the data out of the database and turn it back into an array for use

    $count = count($cfg_data);

    //Check to see if there is a custom template for this phone already listed in the endpointman_mac_list database
    if (!empty($custom_cfg_data)) {
        $custom_cfg_data = unserialize($custom_cfg_data);
        if(array_key_exists('data', $custom_cfg_data)) {
            $custom_cfg_data_ari = $custom_cfg_data['ari'];
            $custom_cfg_data = $custom_cfg_data['data'];
        } else {
            $custom_cfg_data_ari = array();
        }
    } else {
        $custom_cfg_data = array();
        $custom_cfg_data_ari = array();
    }
    if(isset($user_cfg_data)) {
        $user_cfg_data = unserialize($user_cfg_data);
    }

    $template_variables_array = array();
    $group_count = 0;
    $variables_count = 0;

    foreach($cfg_data as $data) {
        $data = fix_single_array_keys($data['category']);
        foreach($data as $cats) {
            //We force the start of a new 'section' by increasing group_count and resetting variables_count to zero
            if($cats['name'] != 'lines') {
                $key = arraysearchrecursive($cats['name'], $template_variables_array, 'title');
                if(is_array($key)) {
                    $group_count == $key[0];
                    $num = count(fix_single_array_keys($template_variables_array[$group_count]['data']));
                    $variables_count == $num;
                } else {
                    if($admin) {
                        $group_count++;
                        $variables_count = 0;
                    }
                }                    
                $template_variables_array[$group_count]['title'] = $cats['name'];
            }
            $cats = fix_single_array_keys($cats['subcategory']);
            foreach($cats as $subcats) {
                $items = fix_single_array_keys($subcats['item']);
                foreach($items as $config_options) {
                    if($admin) {
                        //Administration View Only
                        switch ($config_options['type']) {
                            case "loop_line_options":
                                for($a=1;$a <= $max_lines; $a++) {
                                    $group_count++;
                                    $variables_count = 0;
                                    $template_variables_array[$group_count]['title'] = "Line Options for Line ".$a;
                                    foreach($config_options['data']['item'] as $items) {
                                        if(isset($items['description'])) {
                                            $items['description'] = str_replace('{$count}',$a,$items['description']);
                                            $key = "line|".$a."|".str_replace('$','',$items['variable']);
                                            if(array_key_exists($key,$custom_cfg_data)) {
                                                $custom_cfg_data[$key] = $custom_cfg_data[$key];
                                            } else {
                                                $custom_cfg_data[$key] = str_replace('{$count}', $a, fix_single_array_keys($items['default_value']));
                                            }
                                        }
                                        $items[$variables_count] = $items;
                                        $template_variables_array[$group_count]['data'][$variables_count] = generate_form_data($variables_count,$items,$key,$custom_cfg_data,$admin,$user_cfg_data,$custom_cfg_data_ari);
                                        $template_variables_array[$group_count]['data'][$variables_count]['looping'] = TRUE;
                                        $variables_count++;
                                    }
                                }
                                continue 2;
                            case "loop":
                                //We force the start of a new 'section' by increasing group_count and resetting variables_count to zero
                                $loop_start = $config_options['loop_start'];
                                $loop_end = $config_options['loop_end'];
                                for($a=$loop_start;$a<=$loop_end;$a++) {
                                    foreach($config_options['data']['item'] as $items) {
                                        if(isset($items['description'])) {
                                            $items['description'] = str_replace('{$count}',$a,$items['description']);
											$items['default_value'] = isset($items['default_value']) ? $items['default_value'] : '';
                                            $key = "loop|".str_replace('$','',$items['variable'])."_".$a;
                                            if(array_key_exists($key,$custom_cfg_data)) {
                                                $custom_cfg_data[$key] = $custom_cfg_data[$key];
                                            } else {
                                                $custom_cfg_data[$key] = str_replace('{$count}', $a, fix_single_array_keys($items['default_value']));
                                            }
                                        }
                                        $items[$variables_count] = $items;
                                        $template_variables_array[$group_count]['data'][$variables_count] = generate_form_data($variables_count,$items,$key,$custom_cfg_data,$admin,$user_cfg_data,$custom_cfg_data_ari);
                                        $template_variables_array[$group_count]['data'][$variables_count]['looping'] = TRUE;
                                        $variables_count++;
                                    }
                                }
                                continue 2;
                        }
                    } else {


                    }
                    //Both Views
                    switch ($config_options['type']) {
                        case "break":
                            $template_variables_array[$group_count]['data'][$variables_count] = generate_form_data($variables_count,$config_options,$key,$custom_cfg_data,$admin,$user_cfg_data,$custom_cfg_data_ari);
                            $variables_count++;
                            break;
                        default:
                            if(array_key_exists('variable',$config_options)) {
                                $key = str_replace('$','',$config_options['variable']);
                                //TODO: Move this into the sync function
                                //Checks to see if values are defined in the database, if not then we assume this is a new option and we need a default value here!
                                if(!isset($custom_cfg_data[$key])) {
                                    //xml2array will take values that have no data and turn them into arrays, we want to avoid the word 'array' as a default value, so we blank it out here if we are an array
                                    if((array_key_exists('default_value',$config_options)) AND (is_array($config_options['default_value']))) {                                
                                        $custom_cfg_data[$key] = "";
                                    } elseif((array_key_exists('default_value',$config_options)) AND (!is_array($config_options['default_value']))) {
                                        $custom_cfg_data[$key] = $config_options['default_value'];
                                    }
                                }
                                if((!$admin) AND (isset($custom_cfg_data_ari[$key]))) {
                                    $custom_cfg_data[$key] = $user_cfg_data[$key];
                                    $template_variables_array[$group_count]['data'][$variables_count] = generate_form_data($variables_count,$config_options,$key,$custom_cfg_data,$admin,$user_cfg_data,$custom_cfg_data_ari);
                                    $variables_count++;
                                } elseif($admin) {
                                    $template_variables_array[$group_count]['data'][$variables_count] = generate_form_data($variables_count,$config_options,$key,$custom_cfg_data,$admin,$user_cfg_data,$custom_cfg_data_ari);
                                    $variables_count++;
                                }
                            }
                            break;
                    }
                    continue;
                }
            }
        }
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
function generate_form_data ($i,$cfg_data,$key=NULL,$custom_cfg_data=NULL,$admin=FALSE,$user_cfg_data=NULL,$custom_cfg_data_ari=NULL) {
    switch ($cfg_data['type']) {
        case "input":
            if((!$admin) && (isset($user_cfg_data[$key]))) {
                $custom_cfg_data[$key] = $user_cfg_data[$key];
            }
            $template_variables_array['type'] = "input";
            if(isset($cfg_data['max_chars'])) {
                $template_variables_array['max_chars'] = $cfg_data['max_chars'];
            }
            $template_variables_array['key'] = $key;
            $template_variables_array['value'] = $custom_cfg_data[$key];
            $template_variables_array['description'] = $cfg_data['description'];
            break;
        case "radio":
            if((!$admin) && (isset($user_cfg_data[$key]))) {
                $custom_cfg_data[$key] = $user_cfg_data[$key];
            }
            $num = $custom_cfg_data[$key];
            $template_variables_array['type'] = "radio";
            $template_variables_array['key'] = $key;
            $template_variables_array['description'] = $cfg_data['description'];
            $z = 0;
            while($z < count($cfg_data['data'])) {
                $template_variables_array['data'][$z]['key'] = $key;
                $template_variables_array['data'][$z]['value'] = $cfg_data['data'][$z]['value'];
                $template_variables_array['data'][$z]['description'] = $cfg_data['data'][$z]['text'];
                if ($cfg_data['data'][$z]['value'] == $num) {
                    $template_variables_array['data'][$z]['checked'] = 'checked';
                }
                $z++;
            }
            break;
        case "list":
            if((!$admin) && (isset($user_cfg_data[$key]))) {
                $custom_cfg_data[$key] = $user_cfg_data[$key];
            }
            $num = $custom_cfg_data[$key];
            $template_variables_array['type'] = "list";
            $template_variables_array['key'] = $key;
            $template_variables_array['description'] = $cfg_data['description'];
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
                if ($cfg_data['data'][$z]['value'] == $num) {
                    $template_variables_array['data'][$z]['selected'] = 'selected';
                }
                $z++;
            }
            break;
        case "checkbox":
            if((!$admin) && (isset($user_cfg_data[$key]))) {
                $custom_cfg_data[$key] = $user_cfg_data[$key];
            }
            $num = $custom_cfg_data[$key];
            $template_variables_array['type'] = "checkbox";
            $template_variables_array['key'] = $key;
            $template_variables_array['description'] = $cfg_data['description'];
            $z = 0;
            while($z < count($cfg_data['data'])) {
                $template_variables_array['data'][$z]['key'] = $key;
                $template_variables_array['data'][$z]['value'] = $cfg_data['data'][$z]['value'];
                $template_variables_array['data'][$z]['description'] = $cfg_data['data'][$z]['text'];
                if ($cfg_data['data'][$z]['value'] == $num) {
                    $template_variables_array['data'][$z]['checked'] = 'checked';
                }
                $z++;
            }
            break;
        case "file";
            if((!$admin) && (isset($user_cfg_data[$key]))) {
                $custom_cfg_data[$key] = $user_cfg_data[$key];
            }
            $template_variables_array['type'] = "file";
            if(isset($cfg_data['max_chars'])) {
                $template_variables_array['max_chars'] = $cfg_data['max_chars'];
            }
            $template_variables_array['key'] = $key;
            $template_variables_array['value'] = $custom_cfg_data[$key];
            $template_variables_array['description'] = $cfg_data['description'];
            break;
        case "textarea":
            if((!$admin) && (isset($user_cfg_data[$key]))) {
                $custom_cfg_data[$key] = $user_cfg_data[$key];
            }
            $template_variables_array['type'] = "textarea";
            if(isset($cfg_data['max_chars'])) {
                $template_variables_array['max_chars'] = $cfg_data['max_chars'];
            }
            $template_variables_array['key'] = $key;
            $template_variables_array['value'] = $custom_cfg_data[$key];
            $template_variables_array['description'] = $cfg_data['description'];
            break;
        case "break":
           if($admin) {
                $template_variables_array['type'] = "break";
           } else {
                $template_variables_array['type'] = "NA";
           }
           break;
        default:
            $template_variables_array['type'] = "NA";
            break;
    }

if(isset($cfg_data['description_attr']['tooltip'])) {
    $template_variables_array['tooltip'] = $cfg_data['description_attr']['tooltip'];
}

    if(($admin) AND ($cfg_data['type'] != "break") AND ($cfg_data['type'] != "group")) {
       
        $template_variables_array['aried'] = 1;
        $template_variables_array['ari']['key'] = $key;
        if(isset($custom_cfg_data_ari[$key])) {
            $template_variables_array['ari']['checked'] = "checked";
        }
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