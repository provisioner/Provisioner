#!/usr/bin/php
<?php
/**
 * Demo CSV Parser Script for Provisioner
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
if (PHP_SAPI !== 'cli') 
{ 
   die('This is a CLI Script only!');
}
$options = getopt("s:");
$settings_file = isset($options['s']) ? $options['s'] : 'settings.ini';
if(file_exists($settings_file)) {
	$settings = parse_ini_file($settings_file);
} else {
	die("Settings File ".$settings_file." does not exist!\n");
}

if(!file_exists($settings['provisioner_lib'])) {
	die('The Provisioner Library Location is incorrect. Please fix the setting in '.$settings_file."\n");
}

if(!file_exists($settings['configuration_output'])) {
	die('The Configuration Output Location is incorrect. Please fix the setting in '.$settings_file."\n");
} else {
	if(!is_writable($settings['configuration_output'])) {
		die('The Configuration Output Location is not writable. Please fix the setting in '.$settings_file."\n");
	}
}

if(!file_exists($settings['csv_file'])) {
	die('The CSV File setting is incorrect (File does not exist!). Please fix the setting in '.$settings_file."\n");
} else {
	if(!is_readable($settings['csv_file'])) {
		die('The CSV File setting is incorrect (File is not readable!). Please fix the setting in '.$settings_file."\n");
	}
}
date_default_timezone_set($settings['timezone']);
define('PROVISIONER_BASE', $settings['provisioner_lib']);
define('WRITE_PATH', $settings['configuration_output']);
define('CSV_FILE', $settings['csv_file']);
$write = TRUE;

//Start the Processing now
$contents = file_get_contents(CSV_FILE);
$line_contents = explode("\n",$contents);
$key_list = str_getcsv($line_contents[0]);
unset($line_contents[0]);
$i = 0;
foreach($line_contents as $data) {
	$info = str_getcsv($data);
	foreach($info as $key2 => $data2) {
		$key = $key_list[$key2];
		$phone_data[$i][$key] = $data2;
	}
	$i++;
}
//Load and Parse CSV file

//Parse Each Phone
foreach($phone_data as $info_new) {
	require_once(PROVISIONER_BASE.'autoload.php');
	require_once(PROVISIONER_BASE.'includes/json.php');

	$brand = $info_new['brand'];
	$family = $info_new['product'];
	$model = $info_new['model'];

	$class = "endpoint_" . $brand . "_" . $family . '_phone';

	$endpoint = new $class();

	//Mac Address
	$endpoint->mac = $info_new['mac'];

	//have to because of versions less than php5.3
	$endpoint->brand_name = $brand;
	$endpoint->family_line = $family;
	$endpoint->model = $model; //Phone Model (Please reference family_data.xml in the family directory for a list of recognized models)

	//Processor Information, tagged to the top of most configuration files
	$endpoint->processor_info = "CLI Provisioner .01 Beta";

	//Timezone
	if (!class_exists("DateTimeZone")) { require_once(PROVISIONER_BASE.'includes/timezone.php'); }
	try{
		$tz = isset($settings['timezone']) ? $settings['timezone'] : 'US/Pacific';
		$endpoint->DateTimeZone = new DateTimeZone($tz);
	} catch (Exception $e) {
		die('Error in Timezone Class, Trying US/Pacific default: '.$e."\n");
		try{
			$endpoint->DateTimeZone = new DateTimeZone('US/Pacific');
		} catch (Exception $e) {
			die('Error in Timezone Class: '.$e."\n");
		}
	}
	
	//Provide alternate Configuration file instead of the one from the hard drive
	//$endpoint->config_files_override['$mac.cfg'] = "{\$srvip}\n{\$admin_pass|0}\n{\$test.line.1}";

    $endpoint->settings['provision']['type'] = isset($settings['provision_type']) ? $settings['provision_type'] : 'file';
    $endpoint->settings['provision']['protocol'] = isset($settings['provision_protocol']) ? $settings['provision_protocol'] : 'tftp';
    $endpoint->settings['provision']['path'] = isset($settings['provision_server']) ? $settings['provision_server'] : '';
    $endpoint->settings['provision']['encryption'] = FALSE;

	$endpoint->settings['network']['dhcp'] = TRUE;
	$endpoint->settings['network']['ipv4'] = '';
	$endpoint->settings['network']['ipv6'] = '';
	$endpoint->settings['network']['subnet'] = '255.255.255.0';
	$endpoint->settings['network']['gateway'] = '';
	$endpoint->settings['network']['vlan']['id'] = '';
	$endpoint->settings['network']['vlan']['qos'] = '';

	$endpoint->settings['line'][0]['displayname'] = $info_new['displayname'];
	$endpoint->settings['line'][0]['username'] = $info_new['username'];
	$endpoint->settings['line'][0]['secret'] = $info_new['secret'];
	$endpoint->settings['line'][0]['line'] = 1;
	$endpoint->settings['line'][0]['server_host'] = $info_new['server'];
	$endpoint->settings['line'][0]['server_port'] = 5060;

	$endpoint->settings['ntp'] = $info_new['server'];

	// Because every brand is an extension (eventually) of endpoint, you know this function will exist regardless of who it is
	$returned_data = $endpoint->generate_all_files();
	
	//Create Directory Structure (If needed)
	if (isset($endpoint->directory_structure)) {
	    foreach ($endpoint->directory_structure as $data) {
	        $dir = WRITE_PATH . '/' . $data;
	        if (!file_exists($dir)) {
	            if (!@mkdir($dir, 0755)) {
	                //could not make folder.
	            }
	        }
	    }
	}
	//Copy Files/Directories (If needed)
	if (isset($endpoint->copy_files)) {
		foreach ($endpoint->copy_files as $data) {
			if (((file_exists(WRITE_PATH . '/' . $data)) AND (!in_array($data, $endpoint->protected_files))) OR (!file_exists(WRITE_PATH . '/' . $data))) {
				if (is_dir(PROVISIONER_BASE . "endpoint/" . $brand . "/" . $family . "/" . $data)) {
					if (!file_exists(WRITE_PATH . '/' . $data)) {
						if (!@mkdir(WRITE_PATH . '/' . $data, 0666)) {
							//could not create dir
						}
					}
					$dir_iterator = new RecursiveDirectoryIterator(PROVISIONER_BASE . "endpoint/" . $brand . "/" . $family . "/" . $data . "/");
					$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
					// could use CHILD_FIRST if you so wish
					foreach ($iterator as $file) {
						if (is_dir($file)) {
							$dir = str_replace(PROVISIONER_BASE . "endpoint/" . $brand . "/" . $family . "/" . $data . "/", "", $file);
							if (!file_exists(WRITE_PATH . '/' . $data . "/" . $dir)) {
								if (!@mkdir(WRITE_PATH . '/' . $data . "/" . $dir, 0666)) {
									//could not create dir
								}
							}
						} else {
							$dir = str_replace(PROVISIONER_BASE . "endpoint/" . $brand . "/" . $family . "/" . $data . "/", "", $file);
							if (!@copy($file, WRITE_PATH . '/' . $data . "/" . $dir)) {
								//could not copy file;
							} else {
								chmod(WRITE_PATH . '/' . $data . "/" . $dir, 0666);
							}
						}
					}
				} else {
					if(file_exists(PROVISIONER_BASE . "endpoint/" . $brand . "/" . $family . "/" . $data)) {
						if(!file_exists(dirname(WRITE_PATH . '/' . $data))) {
							!@mkdir(dirname(WRITE_PATH . '/' . $data));
						}
						copy(PROVISIONER_BASE . "endpoint/" . $brand . "/" . $family . "/" . $data, WRITE_PATH . '/' . $data);
						chmod(WRITE_PATH . '/' . $data, 0666);
					}
				}
			}
		}
	}

       foreach ($returned_data as $file => $data) {
           if (((file_exists(WRITE_PATH . '/' . $file)) AND (is_writable(WRITE_PATH . '/' . $file)) AND (!in_array($file, $endpoint->protected_files))) OR (!file_exists(WRITE_PATH . '/' . $file))) {
               file_put_contents(WRITE_PATH . '/' . $file, $data);
               chmod(WRITE_PATH . '/' . $file, 0666);
               if (!file_exists(WRITE_PATH . '/' . $file)) {
                   //could not create dir
                   return(FALSE);
               }
           } elseif (!in_array($file, $endpoint->protected_files)) {
               //could not create dir
               return(FALSE);
           }
       }
}
