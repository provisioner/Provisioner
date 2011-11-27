<?php
require_once('samples/json.php');
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$debug = isset($_REQUEST['debug']) ? TRUE : FALSE;

echo "<pre>";
foreach(findall_xml() as $file) {
    out("Checking File: ".$file);
    $contents = file_get_contents($file);
    @$a = simplexml_load_string($contents);
    if(($a!==FALSE) AND (basename($file) != 'dialnow.xml') AND (basename($file) != 'SEP$mac.cnf.xml')) {
        $xml_data = xml2array($file);
        if((isset($xml_data['data'])) OR (isset($xml_data['template_data']))) {
            out("Parsing File: ".$file);
            mode($file,$xml_data);
        } else {
            out("Ignoring File: ".$file);
        }
    }
}

function mode($file,$xml_data) {
    global $debug,$mode;
    $xml_data = cleanup_array($xml_data);
        
    switch ($mode) {
        case "removexml":
            break;
        case "cleanup":
            $file = str_replace('.xml', '.json', $file);
            if(file_exists($file)) {
                out('Removing File: '.$file);
                unlink($file);
            }
            break;
        case "simulate":
            echo "\n";
            print_r($xml_data);
            echo json_encode($xml_data, JSON_PRETTY_PRINT);
            echo "\n";
            break;
        case "run":
        default:
            $file = str_replace('.xml', '.json', $file);
            $data = json_encode($xml_data, JSON_PRETTY_PRINT);
            file_put_contents($file, $data);
            break;
    }
}

function cleanup_array($data) {
    if(isset($data['data']['brands']['family_list']['family'][0])) {
        foreach($data['data']['brands']['family_list']['family'] as $key => $list) {
            unset($list['version']);
            $list['description'] = empty($list['description']) ? '' : $list['description'];
            $list['changelog'] = empty($list['changelog']) ? '' : $list['changelog'];
            $data['data']['brands']['family_list']['family'][$key] = $list;
        }
        $temp = array();
        $temp = $data['data']['brands']['family_list']['family'];
        $data['data']['brands']['family_list'] = array();
        $data['data']['brands']['family_list'] = $temp;
    } elseif(isset($data['data']['brands']['family_list']['family']) AND is_array($data['data']['brands']['family_list']['family'])) {
        $temp = array();
        $temp = $data['data']['brands']['family_list']['family'];
        $data['data']['brands']['family_list'] = array();
        $data['data']['brands']['family_list'][0] = $temp;
    }
    
    if(isset($data['data']['brands']['oui_list']['oui'][0])) {
        $temp = array();
        $temp = $data['data']['brands']['oui_list']['oui'];
        $data['data']['brands']['oui_list'] = array();
        if(is_array($temp)) {
            $data['data']['brands']['oui_list'] = $temp;
        } else {
            $data['data']['brands']['oui_list'][0] = $temp;
        }
    }
    
    if(isset($data['template_data']['category']['subcategory']['item']['type'])) {
        $temp = array();
        $temp = $data['template_data']['category']['subcategory']['item'];
        $data['template_data']['category']['subcategory']['item'] = array();
        $data['template_data']['category']['subcategory']['item'][0] = $temp;
    }
    
    if((isset($data['data']['model_list'])) AND (is_array($data['data']['model_list']))) {
        foreach($data['data']['model_list'] as $key4 => $list4) {
            if(is_array($list4['template_data']['files'])) {
                foreach($list4['template_data']['files'] as $key3 => $list3) {
                    $data['data']['model_list'][$key4]['template_data']['files'][$key3] = str_replace('.xml', '.json', $list3);
                }
                $temp = array();
                $temp = $data['data']['model_list'][$key4]['template_data']['files'];
                $data['data']['model_list'][$key4]['template_data'] = array();
                $data['data']['model_list'][$key4]['template_data'] = $temp;
            } else {
                $files = $data['data']['model_list'][$key4]['template_data']['files'];
                $data['data']['model_list'][$key4]['template_data'] = array();
                $data['data']['model_list'][$key4]['template_data'][0] = str_replace('.xml','.json', $files);
            }
        }
    }
    unset($data['data']['brands']['version']);
            
    if(isset($data['data']['model_list'])) {
        unset($data['data']['package']);
        unset($data['data']['version']);
        unset($data['data']['last_modified']);
        $data['data']['description'] = !empty($data['data']['description']) ? $data['data']['description'] : '';
        $data['data']['firmware_md5sum'] = !empty($data['data']['firmware_md5sum']) ? $data['data']['firmware_md5sum'] : '';
        $data['data']['firmware_ver'] = !empty($data['data']['firmware_ver']) ? $data['data']['firmware_ver'] : '';
        $data['data']['changelog'] = !empty($data['data']['changelog']) ? $data['data']['changelog'] : '';
    }
    
    if(isset($data['template_data'])) {
        $temp = array();
        $temp = $data['template_data']['category']['subcategory'];
        $data['template_data']['category']['subcategory'] = array();
        unset($data['template_data']['category']['subcategory']);
        $data['template_data']['category']['subcategory'][0] = $temp;
        
        $temp = array();
        $temp = $data['template_data']['category'];
        $data['template_data']['category'] = array();
        unset($data['template_data']['category']);
        $data['template_data']['category'][0] = $temp;
    }
    
    if(isset($data['template_data']['category'][0]['subcategory'][0]['item'])) {
        foreach($data['template_data']['category'][0]['subcategory'][0]['item'] as $key2 => $list2) {
            if(isset($list2['default_value'])) {
                $list2['default_value'] = empty($list2['default_value']) ? '' : $list2['default_value'];
                $data['template_data']['category'][0]['subcategory'][0]['item'][$key2] = $list2;
            }
            if(isset($list2['data']['item'])) {
                foreach($list2['data']['item'] as $key9 => $list9) {
                    if(isset($list9['default_value'])) {
                        $list9['default_value'] = empty($list9['default_value']) ? '' : $list9['default_value'];
                        $data['template_data']['category'][0]['subcategory'][0]['item'][$key2]['data']['item'][$key9] = $list9;
                    }
                }
            }
        }
    }

    return($data);
}

function out($message) {
    echo $message . "\n";
}

function findall_xml() {
    $xmls = array();
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__FILE__)),RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $path) {
        $pathinfo = pathinfo($path);
        if(isset($pathinfo['extension']) AND $pathinfo['extension'] == 'xml') {
            $xmls[] = (string)$path;
        }
    }
    return($xmls);
}

function xml2array($url, $get_attributes = 1, $priority = 'tag', $array_tags=array()) {
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
                if (in_array($tag,$array_tags)) {
                    $current[$tag][0] = $result;
                    $repeated_tag_index[$tag . '_' . $level]=1;
                    $current = & $current[$tag][0];
                } else {
                    $current[$tag] = $result;
                    if ($attributes_data) {
                            $current[$tag . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    $current = & $current[$tag];
               }
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