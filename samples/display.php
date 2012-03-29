<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!isset($_REQUEST['model_demo'])) { die('must select model!'); }

$brand = $_REQUEST['brand'];
$product_model = explode('+',$_REQUEST['model_demo']);
$mac = isset($_REQUEST['mac']) ? $_REQUEST['mac'] : '';
$server = isset($_REQUEST['server']) ? $_REQUEST['server'] : '';
$timezone = isset($_REQUEST['timezone']) ? $_REQUEST['timezone'] : '';
$proxyserver = isset($_REQUEST['proxyserver']) ? $_REQUEST['proxyserver'] : '';

$product = $product_model[0];
$model = $product_model[1];

$json_data = json_decode(file_get_contents('http://repo.provisioner.net/merge_data.php?request=data&brand='.$brand.'&product='.$product.'&model='.urlencode($model)),true);
$html_array = generate_gui_html($json_data,$_REQUEST['regs']);
?>
<form name="form1" method="post" action="process.php">
<?php
foreach($html_array as $sections) {
	echo "<h1>".$sections['title']."</h1>";
	foreach($sections['data'] as $html_els) {
		switch($html_els['type']) {
			case 'input':
				$html_els['value'] = ($html_els['key'] == 'option|mac') ? $mac : $html_els['value'];
				echo $html_els['description'].': <input type="text" name="'.$html_els['key'].'" value="'.$html_els['value'].'"/><br />';
				if($html_els['key'] == 'option|mac') { echo "<br />"; };
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
				$checked = $html_els['value'] ? 'checked' : '';
				echo $html_els['description'].': <input type="checkbox" name="'.$html_els['key'].'" '.$checked.'/><br />';
				break;	
			default:
				break;
		}
	}
}
?>
<input type="hidden" id="brand" name="brand" value="<?php echo $brand;?>" />
<input type="hidden" id="product" name="product" value="<?php echo $product;?>" />
<input type="hidden" id="model" name="model" value="<?php echo $model;?>" />
<input type="hidden" id="mac" name="mac" value="<?php echo $mac;?>" />
<input type="hidden" id="timezone" name="timezone" value="<?php echo $timezone;?>" />
<input type="submit" value="Submit" />
</form>
<?php
/**
 * Generates the Visual Display for the end user
 * @param <type> $cfg_data
 * @param <type> $custom_cfg_data
 * @param <type> $admin
 * @param <type> $user_cfg_data
 * @return <type>
 */
function generate_gui_html($cfg_data,$max_lines=1) {
    //take the data out of the database and turn it back into an array for use

    $template_variables_array = array();
    $group_count = 0;
    $variables_count = 0;

	$globals = $cfg_data['data']['globals'];
	//unset($cfg_data['data']['globals']);
	for($a=1;$a <= $max_lines; $a++) {
	    $template_variables_array[$group_count]['title'] = "Line Information for Line ".$a;
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
					case "option":
						if(isset($data3[0]['description'])) {
							$data3[0]['description'] = str_replace('{$count}',$a,$data3[0]['description']);
							$key = $type."|".str_replace('$','',$data3[0]['variable']);
							$template_variables_array[$group_count]['data'][$variables_count] = generate_form_data($variables_count,$data3[0],$key);
							$variables_count++;
						}
						break;
					case "lineloop":
						if($line_count <= $max_lines) {
							foreach($data3 as $items) {
								$a = $items['line_count'];
								if(isset($items['description'])) {
									$items['description'] = str_replace('{$count}',$a,$items['description']);
									$key = $type."|".$a."|".str_replace('$','',$items['variable']);
								}
								$items[$variables_count] = $items;
								
								if($items['variable'] == '$line_enabled') {
									$items['default_value'] = TRUE;
								}
								$template_variables_array[$group_count]['data'][$variables_count] = generate_form_data($variables_count,$items,$key);
								$template_variables_array[$group_count]['data'][$variables_count]['looping'] = TRUE;
								$variables_count++;
							}
						}
						$template_variables_array[$group_count]['data'][$variables_count]['type'] = 'break';
						$variables_count++;
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
        $data = $data['category'];
        foreach($data as $cats) {
            $cats = $cats['subcategory'];
            foreach($cats as $subcats) {
                $items = $subcats['item'];
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

function file2json($file) {
    $data = file_get_contents($file);
    return(json_decode($data, TRUE));
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