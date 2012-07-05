<?PHP
/*
This file, when run from the web, creates all the needed packages in the releases folder and also generates http://www.provisioner.net/releases
*/
//This is not for any 'scary' security measures, it's just so I can prevent robots from running the script all the time.
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Provisioner.net"');
    header('HTTP/1.0 401 Unauthorized');
	die('no');
} else {
	if(($_SERVER['PHP_AUTH_USER'] != 'maint') && ($_SERVER['PHP_AUTH_PW'] != 'maint')) {
		die('no');
	}
}

if(file_exists('/var/www/html/includes/update_wiki.php')) {
	include('/var/www/html/includes/update_wiki.php');
	function updatew($page,$text,$message) {
		update_wiki($page,$text,$message);
	}
} else {
	function updatew($page,$text,$message) {
	}
}

global $force;
$force = isset($_REQUEST['force']) ? TRUE : FALSE;

set_time_limit(0);
define("MODULES_DIR", "/var/www/repo/endpoint");
define("RELEASE_DIR", "/var/www/html/release/v3");
define("ROOT_DIR", "/var/www/repo");
define("FIRMWARE_DIR", "/var/www/firmware");
define("BRANCH", "master");

file_put_contents(RELEASE_DIR.'/update_status', '1');

echo "======PROVISIONER.NET REPO MAINTENANCE SCRIPT======\n\n\n\n";

$supported_phones = array();
$master_xml = array();

echo "<pre>";

if(isset($_REQUEST['commit_message'])) {
	$c_message = $_REQUEST['commit_message'];
} else {
	$c_message = "PACKAGER: ".file_get_contents('/var/www/html/c_message.txt');
}
if(!isset($_REQUEST['dont_push'])) {
	echo "===GIT Information===\n";
	echo "COMMIT MESSAGE: ".$c_message."\n";
	echo "Pulling GIT Master Repo......\n";
	exec("git pull origin ".BRANCH, $output);
	echo "GIT REPORTED: \n";
	foreach($output as $data) {
		echo "\t".$data . "\n";
	}
	echo "Revision information is as follows: " . file_get_contents(ROOT_DIR . "/.git/FETCH_HEAD");
	echo "=====================\n\n";
}

echo "======RUNNING PHPUNIT TESTS======\n";
exec('phpunit --log-json tests.json tests/phpunittests');
if(file_exists('tests.json')) {
	$tests = preg_split("/}{/i", file_get_contents('tests.json'));
	$test_suite = '';
	$testsc = 0;
	foreach($tests as $data) {
		$data = str_replace("}", "", str_replace("{", "", $data));
		$data = json_decode("{".$data."}",TRUE);
		$total_tests = isset($data['tests']) ? $data['tests'] : $total_tests;
		if($data['event'] == 'test') {
			if($data['status'] == 'pass') {
				echo "\tPassed Test '".$data['test']."'\n";
				$testsc++;
			} else {
				die("\tFailed Test '".$data['test']."'");
			}
		}
		$test_suite = isset($data['test']) ? $data['test'] : $test_suite;
		
	}
	unlink('tests.json');
	if($testsc != $total_tests) {
		die("Failed ".$test_suite." test!");
	} else {
		echo "Passed all Tests!\n\n";
	}
} else {
	echo "PHPUNIT Tests Inconclusive\n\n";
}

echo "Starting Processing of Directories\n";

foreach (glob(MODULES_DIR."/*", GLOB_ONLYDIR) as $filename) {
	flush_buffers();
    if(file_exists($filename."/brand_data.json")) {
		$brand_xml = file2json($filename."/brand_data.json");
		$old_brand_timestamp = $brand_xml['data']['brands']['last_modified'];
		echo "==============".$brand_xml['data']['brands']['name']."==============\n";
		echo "Found brand_data.json in ". $filename ." continuing...\n";
		echo "\tAttempting to parse data into array....";
		$excludes = "";
		flush_buffers();
		if(!empty($brand_xml)) {
			if(!empty($brand_xml['data']['brands']['brand_id'])) {
				echo "Looks Good...Moving On\n";
				$key = $brand_xml['data']['brands']['brand_id'];
				$master_xml['brands'][$key]['name'] =  $brand_xml['data']['brands']['name'];
				$master_xml['brands'][$key]['directory'] =  $brand_xml['data']['brands']['directory'];
				create_brand_pkg($master_xml['brands'][$key]['directory'],$brand_xml['data']['brands']['version'],$brand_xml['data']['brands']['name'],$old_brand_timestamp,$c_message);
			} else {
				echo "\n\tError with the XML in file (brand_id is blank?): ". $filename."/brand_data.json";
			}
		} else {
			echo "\n\tError with the XML in file: ". $filename."/brand_data.json";
		}
		echo "\n\n";
	}
}

copy(ROOT_DIR."/autoload.php",ROOT_DIR."/setup.php");
$endpoint_max[0] = filemtime(ROOT_DIR."/autoload.php");
$endpoint_max[1] = filemtime(MODULES_DIR."/base.php");
$endpoint_mac[2] = filemtime(MODULES_DIR."/global_template_data.json");

$endpoint_max = max($endpoint_max);

exec("tar zcf ".RELEASE_DIR."/provisioner_net.tgz --exclude .svn -C ".ROOT_DIR."/ setup.php endpoint/base.php endpoint/global_template_data.json");

unlink(ROOT_DIR."/setup.php");

$html = "== Provisioner.net Library Releases == \n == Note: This page is edited by an outside script and can not be edited == \n Latest ''Commit Message: ".$c_message."''\n";

$master_array = file2json(MODULES_DIR.'/master.json');
$master_array['data']['last_modified'] = $endpoint_max;
$master_array['data']['version'] = '3';
$master_array['data']['brands'] = array_values($master_xml['brands']);

file_put_contents(MODULES_DIR.'/master.json',json_format(json_encode($master_array)));

$html .= "Provisoner.net Package (Last Modified: ".date('m/d/Y',$endpoint_max)." at ".date("G:i",$endpoint_max)."): ";
$html .= " [http://www.provisioner.net/release/v3/provisioner_net.tgz provisioner_net.tgz]\n\n";

copy(MODULES_DIR."/master.json", RELEASE_DIR."/master.json");

$html .= "Master List File: ";
$html .= "[http://www.provisioner.net/release/v3/master.json master.json]\n";

$html .= "== Brand Packages == \n".$brands_html;

updatew('Releases',$html,$c_message);

$fp = fopen('/var/www/data/pages/supported.txt', 'w');
$html2 = "==This is the list of Supported Phones== \n == Note: This page is edited by an outside script and can not be edited == \n";

//array_multisort($supported_phones);

foreach($supported_phones as $key => $data2) {
	$html2 .= "===".$key."===\n";
	foreach($data2 as $data) {
		foreach($data as $more_data) {
			$html2 .= "* ".$more_data."\n";
		}
	}
}
fwrite($fp, $html2);
fclose($fp);

updatew('Supported',$html2,$c_message);

unlink('cookie.txt');

if(!isset($_REQUEST['dont_push'])) {
	echo "===GIT Information===\n";

	echo "Running Git Add -A, Status:\n";
	exec("git add -A",$output);
	foreach($output as $data) {
		echo "\t".$data . "\n";
	}

	echo "Running Git Commit, Status:\n";
	exec('git commit -m "'.$c_message.'"',$output);
	foreach($output as $data) {
		echo "\t".$data . "\n";
	}

	echo "Running Git Push, Status:\n";
	exec("git push origin ".BRANCH,$output);
	foreach($output as $data) {
		echo "\t".$data . "\n";
	}

	echo "=====================\n\n";
}
if(!isset($_REQUEST['dont_push'])) {
	file_put_contents('/var/www/html/sync_check', '1');
}

file_put_contents(RELEASE_DIR.'/update_status', '0');

echo "\nDone!";

/************
* FUNCTIONS ONLY BELOW HERE!
*
*
*
*************/
function create_brand_pkg($rawname,$version,$brand_name,$old_brand_timestamp,$c_message) {	
	global $brands_html, $supported_phones, $force;

	$pkg_name = $rawname;
	
	if(!file_exists(RELEASE_DIR."/".$rawname)) {
		mkdir(RELEASE_DIR."/".$rawname);
		
	}
	$family_max_array = array(); //Clear family array
	$z = 0;	
	foreach (glob(MODULES_DIR."/".$rawname."/*", GLOB_ONLYDIR) as $family_folders) {
		flush_buffers();
		if(file_exists($family_folders."/family_data.json")) {
			$family_xml = file2json($family_folders."/family_data.json");
			$old_firmware_ver = $family_xml['data']['firmware_ver'];
			echo "\n\t==========".$family_xml['data']['name']."==========\n";
			echo "\tFound family_data.json in ". $family_folders ."\n";

			$b = 0;
			foreach($family_xml['data']['model_list'] as $data) {
				$supported_phones[$brand_name][$z][$b] = $data['model'];
				$b++;
			}
			
			$i=0;
			
			$dir_iterator = new RecursiveDirectoryIterator($family_folders."/");
			$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
			
			foreach ($iterator as $family_files) {
				if((!is_dir($family_files)) && (dirname($family_files) != $family_folders."/firmware") && (dirname($family_files) != $family_folders."/json")) {
					$path_parts = pathinfo($family_files);
					if((basename($family_files) != "family_data.json")) {
						$files_array[$i] = filemtime($family_files);
						echo "\t\tParsing File: ".basename($family_files)."|".$files_array[$i]."\n";
						$i++;
					}
				} 
			}
			
			$family_max = max($files_array);
			$family_max_array[$z] = $family_max;
			echo "\t\t\tTotal Family Timestamp: ". $family_max ."\n";
						
			if(file_exists(FIRMWARE_DIR."/".$rawname."/".$family_xml['data']['directory']."/firmware")) {		
				echo "\t\tFound Firmware Folder in ".$family_xml['data']['directory']."\n";
				$firmware_files_array = array(); //Clear firmware array
				flush_buffers();
				$x=0;
				foreach (glob(FIRMWARE_DIR."/".$rawname."/".$family_xml['data']['directory']."/firmware/*") as $firmware_files) {
					flush_buffers();
					if(!is_dir($firmware_files)) {
						$firmware_files_array[$x] = filemtime($firmware_files);
						echo "\t\t\t\tParsing File: ".basename($firmware_files)."|".$firmware_files_array[$x]."\n";
						$x++;
					}
				}
				
				$firmware_max = max($firmware_files_array);
                echo "\t\t\t\t\tTotal Firmware Timestamp: ". $firmware_max ."\n";

				if(($force) OR ($firmware_max != $old_firmware_ver)) {
					echo "\t\t\tFirmware package has changed...\n";
					echo "\t\t\tCreating Firmware Package\n";
					exec("tar zcf ".RELEASE_DIR."/".$rawname."/".$family_xml['data']['directory']."_firmware.tgz --exclude .svn -C ".FIRMWARE_DIR."/".$rawname."/".$family_xml['data']['directory']." firmware");
					$firmware_md5 = md5_file(RELEASE_DIR."/".$rawname."/".$family_xml['data']['directory']."_firmware.tgz");
				
					echo "\t\t\tPackage MD5 SUM: ".$firmware_md5."\n";
				
					echo "\t\t\tAdding Firmware Package Information to family_data.json File\n";
				
					if($firmware_max > $family_max) {
						echo "\t\t\tFirmware Timestamp is newer than Family Timestamp, updating Family Timestamp to match\n";
						$family_max = $firmware_max;
						$family_max_array[$z] = $family_max;
					}
                
					$family_array = file2json($family_folders."/family_data.json");
					
					$family_array['data']['firmware_ver'] = $firmware_max;
					$family_array['data']['firmware_md5sum'] = $firmware_md5;
					$family_array['data']['firmware_pkg'] = $family_xml['data']['directory']."_firmware.tgz";
					
					file_put_contents($family_folders."/family_data.json",json_format(json_encode($family_array)));					
				} else {
					echo "\t\t\tFirmware has not changed, not updating package\n";
				}
			}
			
			$z++;
			
			echo "\tComplete..Continuing..\n";
			
			$family_list[] = array('id' => $family_xml['data']['id'], 
			'name' => $family_xml['data']['name'], 
			'directory' => $family_xml['data']['directory'],
			'description' => $family_xml['data']['description'],
			'changelog' => $family_xml['data']['changelog'],
			'last_modified' => $family_xml['data']['last_modified']);
	    }
	}	
	echo "\n\t==========".$brand_name."==========\n";
	echo "\tCreating Completed Package\n";
	
	$fp = fopen(MODULES_DIR."/".$rawname."/brand_data.json", 'r');
	$contents = fread($fp, filesize(MODULES_DIR."/".$rawname."/brand_data.json"));
	fclose($fp);
	
	$brand_array = file2json(MODULES_DIR."/".$rawname."/brand_data.json");
	
	$brand_array['data']['brands']['family_list'] = array();
	$brand_array['data']['brands']['family_list'] = $family_list;
	
	$brand_array['data']['brands']['package'] = $pkg_name;
	
	$i=0;
	foreach (glob(MODULES_DIR."/".$rawname."/*") as $brand_files) {
		if((!is_dir($brand_files)) AND (basename($brand_files) != "brand_data.json") AND (basename($brand_files) != "brand_data.json")) {
			$brand_files_array[$i] = filemtime($brand_files);
			echo "\t\tParsing File: ".basename($brand_files)."|".$brand_files_array[$i]."\n";
			$i++;
		}
	}
	$brand_max = max($brand_files_array);
	$temp = max($family_max_array);
	$brand_max = max($brand_max,$temp);
	echo "\t\t\tTotal Brand Timestamp: ".$brand_max."\n";
	
	if(($force) OR ($brand_max != $old_brand_timestamp)) {
		$brand_array['data']['brands']['last_modified'] = $brand_max;
		$brand_array['data']['brands']['changelog'] = $c_message;
		$brand_array['data']['brands']['package'] = $pkg_name.".tgz";
		
		file_put_contents(MODULES_DIR."/".$rawname."/brand_data.json",json_format(json_encode($brand_array)));
	
		copy(MODULES_DIR."/".$rawname."/brand_data.json", RELEASE_DIR."/".$rawname."/".$rawname.".json");
	
		mkdir(RELEASE_DIR."/".$rawname."/");
		exec("tar zcf ".RELEASE_DIR."/".$rawname."/".$pkg_name.".tgz --exclude .svn --exclude firmware -C ".MODULES_DIR." ".$rawname);
		$brand_md5 = md5_file(RELEASE_DIR."/".$rawname."/".$pkg_name.".tgz");
		echo "\t\tPackage MD5 SUM: ".$brand_md5."\n";
		
		$brand_array['data']['brands']['md5sum'] = $brand_md5;
	
		file_put_contents(MODULES_DIR."/".$rawname."/brand_data.json",json_format(json_encode($brand_array)));
		copy(MODULES_DIR."/".$rawname."/brand_data.json", RELEASE_DIR."/".$rawname."/".$rawname.".json");
	
		$brands_html .= "==== ".$rawname." (Last Modified: ".date('m/d/Y',$brand_max)." at ".date("G:i",$brand_max).") ====\n";
		$brands_html .= "XML File: [http://www.provisioner.net/release/v3/".$rawname."/".$rawname.".json ".$rawname.".json]\n\n";
		$brands_html .= "Package File: [http://www.provisioner.net/release/v3/".$rawname."/".$pkg_name.".tgz ".$pkg_name.".tgz]\n";
		
		echo "\tComplete..Continuing..\n";
	} else {
		$brands_html .= "==== ".$rawname." (Last Modified: ".date('m/d/Y',$brand_max)." at ".date("G:i",$brand_max).") ====\n";
		$brands_html .= "XML File: [http://www.provisioner.net/release/v3/".$rawname."/".$rawname.".json ".$rawname.".json]\n\n";
		$brands_html .= "Package File: [http://www.provisioner.net/release/v3/".$rawname."/".$pkg_name.".tgz ".$pkg_name.".tgz]\n";
		echo "\tNothing changed! Aborting Package Creation!\n";
	}
}

function flush_buffers(){
    ob_end_flush();
    //ob_flush();
    flush();
    ob_start();
}

function file2json($file) {
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return(json_decode($data, TRUE));
    } else {
        die('cant find file');
    }
}

function json_format($json) 
{ 
    $tab = "  "; 
    $new_json = ""; 
    $indent_level = 0; 
    $in_string = false; 

    $json_obj = json_decode($json); 

    if($json_obj === false) 
        return false; 

    $json = json_encode($json_obj); 
    $len = strlen($json); 

    for($c = 0; $c < $len; $c++) 
    { 
        $char = $json[$c]; 
        switch($char) 
        { 
            case '{': 
            case '[': 
                if(!$in_string) 
                { 
                    $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1); 
                    $indent_level++; 
                } 
                else 
                { 
                    $new_json .= $char; 
                } 
                break; 
            case '}': 
            case ']': 
                if(!$in_string) 
                { 
                    $indent_level--; 
                    $new_json .= "\n" . str_repeat($tab, $indent_level) . $char; 
                } 
                else 
                { 
                    $new_json .= $char; 
                } 
                break; 
            case ',': 
                if(!$in_string) 
                { 
                    $new_json .= ",\n" . str_repeat($tab, $indent_level); 
                } 
                else 
                { 
                    $new_json .= $char; 
                } 
                break; 
            case ':': 
                if(!$in_string) 
                { 
                    $new_json .= ": "; 
                } 
                else 
                { 
                    $new_json .= $char; 
                } 
                break; 
            case '"': 
                if($c > 0 && $json[$c-1] != '\\') 
                { 
                    $in_string = !$in_string; 
                } 
            default: 
                $new_json .= $char; 
                break;                    
        } 
    } 

    return $new_json; 
}