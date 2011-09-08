<?
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

/**
 * Fix arrays so that they don't return an empty array if the array is empty
 * Instead we will return an empty string
 * Also we fix the problem of wanting to get single arrays with keys but not getting them
 * @param array $array
 * @return array
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

$_REQUEST['prodmod'] = isset($_REQUEST['brand']) ? $_REQUEST['id'] : '';
$_REQUEST['id'] = isset($_REQUEST['brand']) ? $_REQUEST['brand'] : $_REQUEST['id'];

if(!isset($_REQUEST['brand'])) {
	$product_list = xml2array('http://www.provisioner.net/repo/endpoint/'.$_REQUEST['id'].'/brand_data.xml');
	$product_list = fix_single_array_keys($product_list['data']['brands']['family_list']['family']);
	$out[0]['optionValue'] = "";
	$out[0]['optionDisplay'] = "";
	$i = 1;
	foreach($product_list as $list) {
		$family_list = xml2array('http://www.provisioner.net/repo/endpoint/'.$_REQUEST['id'].'/'.$list['directory'].'/family_data.xml');
		$family_list = fix_single_array_keys($family_list['data']['model_list']);
		foreach($family_list as $model_l) {
			$out[$i]['optionValue'] = $list['directory'].'+'.$model_l['model'];
			$out[$i]['optionDisplay'] = $model_l['model'];
			$i++;
		}
		$out[$i]['optionValue'] = "--";
		$out[$i]['optionDisplay'] = "--";
		$i++;
	}
} else {
	$list = explode('+',$_REQUEST['prodmod']);
	$model = $list[1];
	$product = $list[0];
	$family_list = xml2array('http://www.provisioner.net/repo/endpoint/'.$_REQUEST['id'].'/'.$product.'/family_data.xml');
	$key = arraysearchrecursive($model,$family_list['data']['model_list'],'model');
	$count = isset($family_list['data']['model_list'][$key[0]]['lines']) ? $family_list['data']['model_list'][$key[0]]['lines'] : '1';
	for($i=0;$i <= $count; $i++) {
		$out[$i]['optionValue'] = $i+1;
		$out[$i]['optionDisplay'] = $i+1;
	}
}
echo json_encode($out);
?>