<?php
$it = new RecursiveDirectoryIterator(dirname(__FILE__));
$display = Array ( 'json');
foreach(new RecursiveIteratorIterator($it) as $file)
{
    if ( In_Array ( SubStr ( $file, StrrPos ( $file, '.' ) + 1 ), $display ) == true && basename($file) != 'master.json' ) {
		$data = file2json($file);
		switch($data) {
			case basename($file) == 'brand_data.json' && isset($data['data']['brands']) :
				echo $file . ": Brand File\n";
				//echo json_encode($data['data']['brands'], JSON_PRETTY_PRINT);
				break;
			case basename($file) == 'family_data.json' && isset($data['data'])  :
				echo $file . ": Family File\n";
				$final = array();
				$final = $data['data'];
				if(!is_array($final['configuration_files'])) {
					$final['configuration_files'] = explode(",",$final['configuration_files']);
				}
				$final['firmware_ver'] = empty($final['firmware_ver']) ? null : $final['firmware_ver'];
				$final['firmware_pkg'] = (empty($final['firmware_pkg']) OR $final['firmware_pkg'] == 'NULL') ? null : $final['firmware_pkg'];
				$final['firmware_md5sum'] = empty($final['firmware_md5sum']) ? null : $final['firmware_md5sum'];
				//echo json_encode($final, JSON_PRETTY_PRINT);
				foreach($final['configuration_files'] as $conf_file) {
					$f = dirname($file) . '/' . $conf_file;
					if(!file_exists($f)) { die('Missing '.$f); }
					$contents = file_get_contents($f);
					$pattern = "/{loop_(.*?)}(.*?){\/loop_(.*?)}/si";
					while (preg_match($pattern, $contents, $matches)) {
						$middle = $matches[2];
						while(preg_match('/{(\$[^{]+?)[}]/i', $middle, $matches2)) {
							if($matches2[1] == '$number') {
								$middle = preg_replace('/{(\$[^{]+?)[}]/i', '{{ count }}', $middle, 1);
							} else {
								$middle = preg_replace('/{(\$[^{]+?)[}]/i', '{{ loop.'.str_replace('$','',$matches2[1]).' }}', $middle, 1);
							}
						}
						$parsed = "{% for count, loop in ".$matches[3]." %}".$middle."{% endfor %}";
						$contents = preg_replace($pattern, $parsed, $contents, 1);
					}
					
					$pattern = "/{line_loop}(.*?){\/line_loop}/si";
					while (preg_match($pattern, $contents, $matches)) {
						$middle = $matches[1];
						while(preg_match('/{(\$[^{]+?)[}]/i', $middle, $matches2)) {
							if(($matches2[1] == '$number') OR ($matches2[1] == '$count')) {
								$middle = preg_replace('/{(\$[^{]+?)[}]/i', '{{ count }}', $middle, 1);
							} else {
								$middle = preg_replace('/{(\$[^{]+?)[}]/i', '{{ line.'.str_replace('$','',$matches2[1]).' }}', $middle, 1);
							}
						}
						$parsed = "{% for line in lines %}".$middle."{% endfor %}";
						$contents = preg_replace($pattern, $parsed, $contents, 1);
					}
					
					while(preg_match('/{(\$[^{]+?)[}]/i', $contents, $matches)) {
						$contents = preg_replace('/{(\$[^{]+?)[}]/i', '{{ '.str_replace('$','',$matches[1]).' }}', $contents, 1);	
					}
					
					print_r($contents);
					
					//file_put_contents($f,$contents);
				}
				break;
			case isset($data['template_data']) :
				echo $file . ": Template File\n";
				if(!isset($data['template_data']['category'][0]['subcategory'][0]['name'])) { die(print_r($data['template_data']['category'][0]['subcategory'][0])); }
				$template_name = $data['template_data']['category'][0]['name'];
				$template_description = "";
				$final = array();
				$final[$template_name]['description'] = $template_description;
				foreach($data['template_data']['category'][0]['subcategory'][0]['item'] as $data) {
					if($data['type'] != 'break') {
						if($data['type'] != 'loop' AND $data['type'] != 'line_loop' AND $data['type'] != 'group' AND $data['type'] != 'loop_line_options' AND $data['type'] != 'header') {
							if(!isset($data['variable'])) { die($data['type']); }
							$name = str_replace('$','',$data['variable']);
							unset($data['variable']);
							$final[$template_name]['items'][$name] = $data;
						} elseif($data['type'] == 'loop') {
							$start = isset($data['loop_start']) ? $data['loop_start'] : '0';
							$quantity = isset($data['loop_end']) ? $data['loop_end'] : '0';
							$loop_data = array();
							foreach($data['data']['item'] as $subdata) {
								if($subdata['type'] != 'break' AND $subdata['type'] != 'none') {
									if(preg_match('/\$(.*)_(.*)/i',$subdata['variable'],$matches)) {
										$name = $matches[1];
										$loop_data[$matches[2]]['description'] = str_replace('}',' }}', str_replace('{$','{{ ', $subdata['description']));
										$loop_data[$matches[2]]['type'] = $subdata['type'];
										if(isset($subtype['data'])) {
											$loop_data[$matches[2]]['data'] = $subtype['data'];
										}
									} else {
										die('error');
									}
								}
							}
							$final[$template_name]['items'][$name]['quantity'] = $quantity;
							$final[$template_name]['items'][$name]['start'] = $start;
							$final[$template_name]['items'][$name]['type'] = 'loop';
							$final[$template_name]['items'][$name]['loop_data'] = $loop_data;
						} elseif($data['type'] == 'line_loop') {
							die('no');
						}
						
					} 
				}
				//echo json_encode($final, JSON_PRETTY_PRINT);
				break;
		}
	}
}


function file2json($file) {
    if (file_exists($file)) {
        $json = file_get_contents($file);
        $data = json_decode($json, TRUE);
        $error = json_last_error();
        if ($error === JSON_ERROR_NONE) {
            return($data);
        } else {
            $errors = array(// Taken from http://www.php.net/manual/en/function.json-last-error.php
                JSON_ERROR_NONE => 'No error has occurred',
                JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
                JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
                JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
                JSON_ERROR_SYNTAX => 'Syntax error',
                JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
            );
            if (array_key_exists($error, $errors)) {
                $error = $errors[$error];
            } else {
                $error = "Unknown error $error";
            }
            throw new Exception("Could not decode $file: $error");
        }
    } else {
        throw new Exception("Could not load: " . $file);
    }
}