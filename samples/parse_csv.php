<?php
/**
 * Demo CSV Parser Script for Provisioner
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
define('PROVISIONER_BASE', '../');
$write = TRUE;
$write_path = "tmp/";
$csv_file = "phones.csv";

//Load and Parse CSV file
$contents = file_get_contents($csv_file);
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

//Parse Each Phone
foreach($phone_data as $info_new) {
	require_once('../autoload.php');

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
	$endpoint->processor_info = "Web Provisioner 2.0";

	//Timezone
	if (!class_exists("DateTimeZone")) { require('tz.php'); }
	$endpoint->DateTimeZone = new DateTimeZone('US/Pacific');;

	//Provide alternate Configuration file instead of the one from the hard drive
	//$endpoint->config_files_override['$mac.cfg'] = "{\$srvip}\n{\$admin_pass|0}\n{\$test.line.1}";

	if (!$write) {
	    $endpoint->settings['provision']['type'] = 'dynamic';
	    $endpoint->settings['provision']['protocol'] = 'http';
	    $endpoint->settings['provision']['path'] = 'http://' . $_SERVER["SERVER_ADDR"] . dirname($_SERVER['REQUEST_URI']) . '/';
	    $endpoint->settings['provision']['encryption'] = FALSE;
	} else {
	    $endpoint->settings['provision']['type'] = 'file';
	    $endpoint->settings['provision']['protocol'] = 'tftp';
	    $endpoint->settings['provision']['path'] = '';
	    $endpoint->settings['provision']['encryption'] = FALSE;
	}

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

	//var_dump($returned_data);
	
	if($write) {
		//Create Directory Structure (If needed)
        if (isset($endpoint->directory_structure)) {
            foreach ($endpoint->directory_structure as $data) {
                $dir = $write_path . $data;
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
                if (((file_exists($write_path . $data)) AND (!in_array($data, $endpoint->protected_files))) OR (!file_exists($write_path . $data))) {
                    if (is_dir(PROVISIONER_BASE . "endpoint/" . $brand . "/" . $family . "/" . $data)) {
                        if (!file_exists($write_path . $data)) {
                            if (!@mkdir($write_path . $data, 0666)) {
                                //could not create dir
                            }
                        }
                        $dir_iterator = new RecursiveDirectoryIterator(PROVISIONER_BASE . "endpoint/" . $brand . "/" . $family . "/" . $data . "/");
                        $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
                        // could use CHILD_FIRST if you so wish
                        foreach ($iterator as $file) {
                            if (is_dir($file)) {
                                $dir = str_replace(PROVISIONER_BASE . "endpoint/" . $brand . "/" . $family . "/" . $data . "/", "", $file);
                                if (!file_exists($write_path . $data . "/" . $dir)) {
                                    if (!@mkdir($write_path . $data . "/" . $dir, 0666)) {
                                        //could not create dir
                                    }
                                }
                            } else {
                                $dir = str_replace(PROVISIONER_BASE . "endpoint/" . $brand . "/" . $family . "/" . $data . "/", "", $file);
                                if (!@copy($file, $write_path . $data . "/" . $dir)) {
                                    //could not copy file;
                                } else {
                                    chmod($write_path . $data . "/" . $dir, 0666);
                                }
                            }
                        }
                    } else {
                        copy(PROVISIONER_BASE . "endpoint/" . $brand . "/" . $family . "/" . $data, $write_path . $data);
                        chmod($write_path . $data, 0666);
                    }
                }
            }
        }

        foreach ($returned_data as $file => $data) {
            if (((file_exists($write_path . $file)) AND (is_writable($write_path . $file)) AND (!in_array($file, $endpoint->protected_files))) OR (!file_exists($write_path . $file))) {
                file_put_contents($write_path . $file, $data);
                chmod($write_path . $file, 0666);
                if (!file_exists($write_path . $file)) {
                    //could not create dir
                    return(FALSE);
                }
            } elseif (!in_array($file, $endpoint->protected_files)) {
                //could not create dir
                return(FALSE);
            }
        }
	}
}