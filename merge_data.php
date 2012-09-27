<?php
/**
 * JSON Merge/Retrieve Script
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 *
 */
require('includes/json.php');
define("PROVISIONER_PATH", "");
$request = isset($_REQUEST['request']) ? $_REQUEST['request'] : '';
$brand = isset($_REQUEST['brand']) ? $_REQUEST['brand'] : '';
$product = isset($_REQUEST['product']) ? $_REQUEST['product'] : '';
$model = isset($_REQUEST['model']) ? $_REQUEST['model'] : '';
$show_array = isset($_REQUEST['show_array']) ? TRUE : FALSE;
$hide_loops = isset($_REQUEST['hide_loops']) ? TRUE : FALSE;
$pp = isset($_REQUEST['pp']) ? TRUE : FALSE;

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// The following CORS headers do not offer a proper/complete CORS solution, but they will work for the time being -Jon
header("Access-Control-Allow-Origin: *"); // CORS header #1
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // CORS header #2
header("Access-Control-Expose-Headers: Content-Type, Location");
header("Access-Control-Allow-Headers: Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since,  X-File-Name, Cache-Control, If-Match");

switch ($request) {
    case 'list':
        $list_array = array();
        if (file_exists(PROVISIONER_PATH . "endpoint/master.json")) {
            $master = file2json(PROVISIONER_PATH . "endpoint/master.json");
            foreach ($master['data']['brands'] as $brand_list) {
                if (file_exists(PROVISIONER_PATH . "endpoint/" . $brand_list['directory'] . "/brand_data.json")) {
                    $brand_data = array();
                    $brand_data = file2json(PROVISIONER_PATH . "endpoint/" . $brand_list['directory'] . "/brand_data.json");
                    $family_list_array = $brand_data['data']['brands']['family_list'];
                    foreach ($family_list_array as $family_list) {
                        if (file_exists(PROVISIONER_PATH . "endpoint/" . $brand_list['directory'] . "/" . $family_list['directory'] . "/family_data.json")) {
                            $family_data = array();
                            $family_data = file2json(PROVISIONER_PATH . "endpoint/" . $brand_list['directory'] . "/" . $family_list['directory'] . "/family_data.json");
                            $model_list_array = $family_data['data']['model_list'];
                            foreach ($model_list_array as $model_list) {
                                $list_array[$brand_list['directory']][$family_list['directory']][] = $model_list['model'];
                            }
                        }
                    }
                }
            }
            if ($show_array) {
                echo "<pre>";
                print_r($list_array);
            } else {
                echo json_encode($list_array);
            }
        } else {
            jsondie('cant find master.json');
        }
        break;
    case 'data':
        if (!empty($brand) && empty($product) && empty($model)) {
            if (file_exists(PROVISIONER_PATH . "endpoint/" . $brand . "/brand_data.json")) {
                $temp = file2json(PROVISIONER_PATH . "endpoint/" . $brand . "/brand_data.json");
                if ($show_array) {
                    echo "<pre>";
                    print_r($temp['data']['brands']);
                } else {
                    echo json_encode($temp['data']['brands']);
                }
            } else {
                jsondie('No brand_data.json for ' . $brand);
            }
        } elseif (!empty($brand) && !empty($product) && empty($model)) {
            if (file_exists(PROVISIONER_PATH . "endpoint/" . $brand . "/" . $product . "/family_data.json")) {
                $temp = file2json(PROVISIONER_PATH . "endpoint/" . $brand . "/" . $product . "/family_data.json");
                if ($show_array) {
                    echo "<pre>";
                    print_r($temp['data']);
                } else {
                    echo json_encode($temp['data']);
                }
            } else {
                jsondie('No family_data.json for ' . $product);
            }
        } elseif (!empty($brand) && !empty($product) && !empty($model)) {
            if (file_exists(PROVISIONER_PATH . "endpoint/" . $brand . "/" . $product . "/family_data.json")) {
                $data = array();
                $temp = file2json(PROVISIONER_PATH . "endpoint/" . $brand . "/" . $product . "/family_data.json");
                $test = arraysearchrecursive($model, $temp, 'model');
                if (!$test) {
                    jsondie('cant find model');
                }
                $data['phone_data']['brand'] = $brand;
                $data['phone_data']['product'] = $product;
                $data['phone_data']['model'] = $model;
                $data['admin'] = TRUE;
                $data['lines'] = $temp['data']['model_list'][$test[2]]['lines'];
                $files = $temp['data']['model_list'][$test[2]]['template_data'];
                array_unshift($files, PROVISIONER_PATH."../../global_template_data.json");
                foreach ($files as $files_data) {
                    if (($files_data != 'line_options_22.json') AND (file_exists(PROVISIONER_PATH . "endpoint/" . $brand . "/" . $product . "/" . $files_data))) {
                        $temp_files_data = file2json(PROVISIONER_PATH . "endpoint/" . $brand . "/" . $product . "/" . $files_data);                        
                        foreach($temp_files_data['template_data']['category'] as $category) {
                            $category_name = $category['name'];
                            foreach($category['subcategory'] as $subcategory) {
                                $subcategory_name = $subcategory['name'];
                                $items_fin = array();
                                $items_loop = array();
                                foreach($subcategory['item'] as $item) {
                                    switch($item['type']) {
                                        case 'loop_line_options':
                                            for ($i = 1; $i <= $data['lines']; $i++) {
                                                $var_nam = "lineloop|line_" . $i;
                                                foreach ($item['data']['item'] as $item_loop) {
                                                    if ($item_loop['type'] != 'break') {
                                                        $z = str_replace("\$", "", $item_loop['variable']);
                                                        $items_loop[$var_nam][$z] = $item_loop;
                                                        $items_loop[$var_nam][$z]['description'] = str_replace('{$count}', $i, $items_loop[$var_nam][$z]['description']);
                                                        $items_loop[$var_nam][$z]['default_value'] = $items_loop[$var_nam][$z]['default_value'];
                                                        $items_loop[$var_nam][$z]['default_value'] = str_replace('{$count}', $i, $items_loop[$var_nam][$z]['default_value']);
                                                        $items_loop[$var_nam][$z]['line_loop'] = TRUE;
                                                        $items_loop[$var_nam][$z]['line_count'] = $i;
														
                                                    }
												}
                                            }
                                            //unset($items[$key]);
                                            $items_fin = array_merge($items_fin, $items_loop);
                                            break;
                                        case 'loop':
                                            for ($i = $item['loop_start']; $i <= $item['loop_end']; $i++) {
                                                $name = explode("_", $item['data']['item'][0]['variable']);
                                                $var_nam = "loop|" . str_replace("\$", "", $name[0]) . "_" . $i;
                                                foreach ($item['data']['item'] as $item_loop) {
                                                    if ($item_loop['type'] != 'break') {
                                                        $z_tmp = explode("_", $item_loop['variable']);
                                                        $z = $z_tmp[1];
                                                        $items_loop[$var_nam][$z] = $item_loop;
                                                        $items_loop[$var_nam][$z]['description'] = str_replace('{$count}', $i, $items_loop[$var_nam][$z]['description']);
                                                        $items_loop[$var_nam][$z]['variable'] = str_replace('_', '_' . $i . '_', $items_loop[$var_nam][$z]['variable']);
                                                        $items_loop[$var_nam][$z]['default_value'] = isset($items_loop[$var_nam][$z]['default_value']) ? $items_loop[$var_nam][$z]['default_value'] : '';
                                                        $items_loop[$var_nam][$z]['loop'] = TRUE;
                                                        $items_loop[$var_nam][$z]['loop_count'] = $i;
                                                    }
                                                }
                                            }
                                            //unset($items[$key]);
                                            $items_fin = array_merge($items_fin, $items_loop);
                                            break;
                                        case 'break':
                                            break;
                                        default:
                                            //unset($items[$key]);
                                            $var_nam = "option|" . str_replace("\$", "", $item['variable']);
                                            $items_fin[$var_nam][] = $item;
                                            break;
                                            
                                    }
                                }
                                if(isset($data['data'][$category_name][$subcategory_name])) {
                                    $old_sc = $data['data'][$category_name][$subcategory_name];
                                    $sub_cat_data[$category_name][$subcategory_name] = array();
                                    $sub_cat_data[$category_name][$subcategory_name] = array_merge($old_sc,$items_fin);
                                } else {
                                    $sub_cat_data[$category_name][$subcategory_name] = $items_fin;
                                }
                            }
                            if (isset($data['data'][$category_name])) {
                                $old_c = $data['data'][$category_name];
                                $new_c = $sub_cat_data[$category_name];
                                $sub_cat_data[$category_name] = array();
                                $data['data'][$category_name] = array_merge($old_c,$new_c);
                            } else {
                                $data['data'][$category_name] = $sub_cat_data[$category_name];
                            }
                        }
                    }
                }
                if ($show_array) {
                    echo "<pre>";
                    print_r($data);
                } else {
                    echo json_encode($data);
                }
            } else {
                jsondie('No family_data.json for ' . $product);
            }
        }
        break;
    default:
        jsondie('nothing defined');
        break;
}

function jsondie($message) {
    echo json_encode(array('status' => $message));
    die();
}

function file2json($file) {
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return(json_decode($data, TRUE));
    } else {
        die('cant find file');
    }
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
