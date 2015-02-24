<?
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

function file2json($file) {
	$data = file_get_contents($file);
	return(json_decode($data, TRUE));
}

$_REQUEST['prodmod'] = isset($_REQUEST['brand']) ? $_REQUEST['id'] : '';
$_REQUEST['id'] = isset($_REQUEST['brand']) ? $_REQUEST['brand'] : $_REQUEST['id'];

if(!isset($_REQUEST['brand'])) {
	$product_list = file2json('http://repo.provisioner.net/endpoint/'.$_REQUEST['id'].'/brand_data.json');
	$product_list = $product_list['data']['brands']['family_list'];
	$out[0]['optionValue'] = "";
	$out[0]['optionDisplay'] = "";
	$i = 1;
	foreach($product_list as $list) {
		$family_list = file2json('http://repo.provisioner.net/endpoint/'.$_REQUEST['id'].'/'.$list['directory'].'/family_data.json');
		$family_list = $family_list['data']['model_list'];
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
	$family_list = file2json('http://repo.provisioner.net/endpoint/'.$_REQUEST['id'].'/'.$product.'/family_data.json');
	$key = arraysearchrecursive($model,$family_list['data']['model_list'],'model');
	$count = isset($family_list['data']['model_list'][$key[0]]['lines']) ? $family_list['data']['model_list'][$key[0]]['lines'] : '1';
	for($i=0;$i <= $count; $i++) {
		$out[$i]['optionValue'] = $i+1;
		$out[$i]['optionDisplay'] = $i+1;
	}
}
echo json_encode($out);