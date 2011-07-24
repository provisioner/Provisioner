<?php
define("PROVISIONER_PATH", "");
$request = isset($_REQUEST['request']) ? $_REQUEST['request'] : '';
$brand = isset($_REQUEST['brand']) ? $_REQUEST['brand'] : '';
$product = isset($_REQUEST['product']) ? $_REQUEST['product'] : '';
$model = isset($_REQUEST['model']) ? $_REQUEST['model'] : '';
$show_array = isset($_REQUEST['show_array']) ? TRUE : FALSE;
$hide_loops = isset($_REQUEST['hide_loops']) ? TRUE : FALSE;

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

switch($request) {
	case 'list':
		$list_array = array();
		if(file_exists(PROVISIONER_PATH."endpoint/master.xml")) {
			$master = xml2array(PROVISIONER_PATH."endpoint/master.xml");
			foreach($master['data']['brands'] as $brand_list) {
				if(file_exists(PROVISIONER_PATH."endpoint/".$brand_list['directory']."/brand_data.xml")) {
					$brand_data = array();
					$brand_data = xml2array(PROVISIONER_PATH."endpoint/".$brand_list['directory']."/brand_data.xml");
					$family_list_array = fix_single_array_keys($brand_data['data']['brands']['family_list']['family']);
					foreach($family_list_array as $family_list) {
						if(file_exists(PROVISIONER_PATH."endpoint/".$brand_list['directory']."/".$family_list['directory']."/family_data.xml")) {
							$family_data = array();
							$family_data = xml2array(PROVISIONER_PATH."endpoint/".$brand_list['directory']."/".$family_list['directory']."/family_data.xml");
							$model_list_array = fix_single_array_keys($family_data['data']['model_list']);
							foreach($model_list_array as $model_list) {
								$list_array[$brand_list['directory']][$family_list['directory']][] = $model_list['model'];
							}
						}
					}
					
				}
			}
			if($show_array) {
				echo "<pre>";
				print_r($list_array);
			} else {
				echo json_encode($list_array);
			}		
		} else {
			die('cant find master.xml');
		}
		break;
	case 'data':
		if(!empty($brand) && empty($product) && empty($model)) {
			if(file_exists(PROVISIONER_PATH."endpoint/".$brand."/brand_data.xml")) {
				$temp = xml2array(PROVISIONER_PATH."endpoint/".$brand."/brand_data.xml");
				if($show_array) {
					echo "<pre>";
					print_r($temp['data']['brands']);
				} else {
					echo json_encode($temp['data']['brands']);
				}
			} else {
				die('No brand_data.xml for '.$brand);
			}
		} elseif(!empty($brand) && !empty($product) && empty($model)) {
			if(file_exists(PROVISIONER_PATH."endpoint/".$brand."/".$product."/family_data.xml")) {
				$temp = xml2array(PROVISIONER_PATH."endpoint/".$brand."/".$product."/family_data.xml");
				if($show_array) {
					echo "<pre>";
					print_r($temp['data']);
				} else {
					echo json_encode($temp['data']);
				}
			} else {
				die('No family_data.xml for '.$product);
			}
		} elseif(!empty($brand) && !empty($product) && !empty($model)) {
			if(file_exists(PROVISIONER_PATH."endpoint/".$brand."/".$product."/family_data.xml")) {
				$data = array();
				$temp = xml2array(PROVISIONER_PATH."endpoint/".$brand."/".$product."/family_data.xml");
				$test = arraysearchrecursive($model,$temp,'model');
				if(!$test) {
					die('cant find model');
				}
				$data['lines'] = $temp['data']['model_list'][$test[2]]['lines'];
				$files = fix_single_array_keys($temp['data']['model_list'][$test[2]]['template_data']['files']);
				foreach($files as $files_data) {
					if(file_exists(PROVISIONER_PATH."endpoint/".$brand."/".$product."/".$files_data)) {
						$temp_files_data = xml2array(PROVISIONER_PATH."endpoint/".$brand."/".$product."/".$files_data);
						$categories = fix_single_array_keys($temp_files_data['template_data']['category']);
						foreach($categories as $cat_data) {
							$subcategories = fix_single_array_keys($cat_data['subcategory']);
							foreach($subcategories as $subcat_data) {
								$items = fix_single_array_keys($subcat_data['item']);
								if($hide_loops) {
									$items_loop = array();
									foreach($items as $loop_data) {
										if($loop_data['type'] == 'loop') {
											$z = 0;
											for($i = $loop_data['loop_start']; $i <= $loop_data['loop_end']; $i++) {
												foreach($loop_data['data']['item'] as $item_loop) {
													$items_loop[$z] = $item_loop;
													$items_loop[$z]['description'] = str_replace('{$count}', $i, $items_loop[$z]['description']);
													//$items_loop[$z]['variable'] = str_replace('_', '_'.$i.'_', $items_loop[$z]['variable']);
													$items_loop[$z]['default_value'] = fix_single_array_keys($items_loop[$z]['default_value']);
													$items_loop[$z]['loop'] = 'TRUE';
													$items_loop[$z]['loop_count'] = $i;
													$z++;
												}
											}
											unlink($items);
											$items = $items_loop;
										} elseif($loop_data['type'] == 'loop_line_options') {
											$z = 0;
											for($i = 1; $i <= $data['lines']; $i++) {
												foreach($loop_data['data']['item'] as $item_loop) {
													$items_loop[$z] = $item_loop;
													$items_loop[$z]['description'] = str_replace('{$count}', $i, $items_loop[$z]['description']);													
													//$items_loop[$z]['variable'] = str_replace('_', '_'.$i.'_', $items_loop[$z]['variable']);
													$items_loop[$z]['default_value'] = fix_single_array_keys($items_loop[$z]['default_value']);
													$items_loop[$z]['default_value'] = str_replace('{$count}', $i, $items_loop[$z]['default_value']);
													$items_loop[$z]['line_loop'] = 'TRUE';
													$items_loop[$z]['line_count'] = $i;
													$z++;
												}
											}
											unlink($items);
											$items = $items_loop;
										}
									}
								}
								if(array_key_exists($subcat_data['name'],$data['data'][$cat_data['name']])) {
									//array_merge
								} else {
									$data['data'][$cat_data['name']][$subcat_data['name']] = $items;	
								}
							}
						}
						
					}
				}
				if($show_array) {
					echo "<pre>";
					print_r($data);
				} else {
					echo json_encode($data);
				}
			} else {
				die('No family_data.xml for '.$product);
			}
		}
		break;
	default:
		die('nothing defined');
}


//FUNCTIONS BELOW------------------
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

/**
 * Function xml2array has a bad habit of returning blank xml values as empty arrays.
 * Also if the xml children only loops once then the array is put into a normal array (array[variable]).
 * However if it loops more than once then it is put into a counted array (array[0][variable])
 * We fix that issue here by returning blank values on empty arrays or always returning array[0]
 * @param array $array
 * @return mixed
 * @author Karl Anderson
 */
function fix_single_array_keys($array) {
    if (!is_array($array)) {
        return $array;
    }

    if((empty($array[0])) AND (!empty($array))) {
        $array_n[0] = $array;

        return($array_n);
    }

    return empty($array) ? '' : $array;

    /*
    if((empty($array[0])) AND (!empty($array))) {
        $array_n[0] = $array;
        return($array_n);
    } elseif(!empty($array)) {
        return($array);
    //This is so stupid?! PHP gets confused.
    } elseif($array == '0') {
        return($array);
    } else {
        return("");
    }
     * *
    */
}

function xml2array($url, $get_attributes = 1, $priority = 'tag')
{
    $contents = "";
    if (!function_exists('xml_parser_create'))
    {
        return array ();
    }
    $parser = xml_parser_create('');
    if (!($fp = @ fopen($url, 'rb')))
    {
        return array ();
    }
    while (!feof($fp))
    {
        $contents .= fread($fp, 8192);
    }
    fclose($fp);
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);
    if (!$xml_values)
        return; //Hmm...
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
            if ($priority == 'tag')
                $result = $value;
            else
                $result['value'] = $value;
        }
        if (isset ($attributes) and $get_attributes)
        {
            foreach ($attributes as $attr => $val)
            {
                if ($priority == 'tag')
                    $attributes_data[$attr] = $val;
                else
                    $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }
        if ($type == "open")
        { 
            $parent[$level -1] = & $current;
            if (!is_array($current) or (!in_array($tag, array_keys($current))))
            {
                $current[$tag] = $result;
                if ($attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
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
                    $current[$tag] = array (
                        $current[$tag],
                        $result
                    ); 
                    $repeated_tag_index[$tag . '_' . $level] = 2;
                    if (isset ($current[$tag . '_attr']))
                    {
                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                        unset ($current[$tag . '_attr']);
                    }
                }
                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                $current = & $current[$tag][$last_item_index];
            }
        }
        elseif ($type == "complete")
        {
            if (!isset ($current[$tag]))
            {
                $current[$tag] = $result;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                if ($priority == 'tag' and $attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
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
                    $current[$tag] = array (
                        $current[$tag],
                        $result
                    ); 
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
        elseif ($type == 'close')
        {
            $current = & $parent[$level -1];
        }
    }
    return ($xml_array);
}