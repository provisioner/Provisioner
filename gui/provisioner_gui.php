<?PHP

class provisioner_gui {
	private $base;
	
	function __construct($base) {
		$this->base = $base;
	}
	
	function generate_textual_form($model,$product,$brand,$hide_array = array()) {
		$template_array = $this->generate_complete_array($model,$product,$brand);
		$html = array();
		foreach($template_array['data'] as $category => $subs) {
			foreach($subs as $subcategories => $its) {
				foreach($its as $kitems => $items) {
					if(!in_array($kitems,$hide_array)) {
						if(preg_match('/^option\|(.*)/i',$kitems)) {
							$html[$category][$subcategories][$kitems] = $this->convert2html($kitems,$items[0]);
						}
						if(preg_match('/^loop\|(.*)/i',$kitems)) {
							foreach($items as $loop_key => $loop_data) {
								$key = $kitems."|".$loop_key;
								$html[$category][$subcategories][$key] = $this->convert2html($key,$loop_data);;
							}
						}
						if(preg_match('/^lineloop\|(.*)/i',$kitems)) {
							foreach($items as $loop_key => $loop_data) {
								$split = preg_split('/(_)/i',$kitems);
								$line = $split[1];
								$key = "lineloop|".$line."|".$loop_key;
								$html[$category][$subcategories][$key] = $this->convert2html($key,$loop_data);;
							}
						}
						if(preg_match('/^break/',$kitems)) {
							$html[$category][$subcategories][] = "<br />";
						}
					}
				}
			}
		}
		return($html);
	}
	
	function generate_complete_array($model,$product,$brand) {
		if (file_exists($this->base . "/" . $brand . "/" . $product . "/family_data.json")) {
			$data = array();
			$fd_json = $this->file2json($this->base . "/" . $brand . "/" . $product . "/family_data.json");
            $model_location = $this->arraysearchrecursive($model, $fd_json, 'model');
            if (!$model_location) {
                throw new Exception('cant find model');
            }
			
			$model_information = $fd_json['data']['model_list'][$model_location[2]];

            $data['phone_data']['brand'] = $brand;
            $data['phone_data']['product'] = $product;
            $data['phone_data']['model'] = $model;
            $data['lines'] = $model_information['lines'];
            $files = $model_information['template_data'];
            array_unshift($files, "/../../global_template_data.json");
			$b = 0;
			foreach ($files as $files_data) {
                if (file_exists($this->base . "/" . $brand . "/" . $product . "/" . $files_data)) {
				    $template_data = $this->file2json($this->base . "/" . $brand . "/" . $product . "/" . $files_data);
					foreach($template_data['template_data']['category'] as $category) {
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
											$items_loop[$var_nam][]['type'] = 'break';
	                                    }
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
	                                    $items_fin = array_merge($items_fin, $items_loop);
	                                    break;
	                                case 'break':
                                    	$items_fin['break'][] = 'break';
	                                    break;
	                                default:
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
		}
		return($data);
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
	private function arraysearchrecursive($Needle, $Haystack, $NeedleKey="", $Strict=false, $Path=array()) {
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
	
	private function convert2html($key,$data) {
		$html_return = '';
		switch($data['type']) {
			case 'input':
				$value = isset($data['value']) && !empty($data['value']) ? $data['value'] : $data['default_value'];
				$html_return = $data['description'].': <input type="text" name="'.$key.'" value="'.$value.'"/><br />';
				break;
			case 'break':
				$html_return = '<br/>';
				break;
			case 'list':
				$html_return = $data['description'].": <select name='".$key."'>";
				$value = isset($data['value']) && !empty($data['value']) ? $data['value'] : $data['default_value'];
				foreach($data['data'] as $list) {
					  $selected = ($value == $list['value']) ? 'selected' : '';
					  $html_return .= '<option value="'.$list['value'].'" '.$selected.'>'.$list['text'].'</option>';
				}
				$html_return .= "</select><br />";
				break;
			case 'radio':
				$html_return = $data['description'].':';
				foreach($data['data'] as $list) {
					$checked = isset($list['checked']) ? 'checked' : '';
					$html_return .= '|<input type="radio" name="'.$key.'" value="'.$key.'" '.$checked.'/>'.$list['description'];
				}
				$html_return .= '<br />';
				break;
			case 'checkbox':
				$value = isset($data['value']) && !empty($data['value']) ? $data['value'] : $data['default_value'];
				$checked = $value ? 'checked' : '';
				$html_return = $data['description'].': <input type="checkbox" name="'.$key.'" '.$checked.'/><br />';
				break;	
			default:
				break;
		}
		return($html_return);
	}
	
	private function file2json($file) {
	    if (file_exists($file)) {
	        $data = file_get_contents($file);
	        return(json_decode($data, TRUE));
	    } else {
	        die('cant find file');
	    }
	}
}