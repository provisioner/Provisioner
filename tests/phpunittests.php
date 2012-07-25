<?php
define("ROOT_PATH", dirname(dirname(__FILE__)));
define("MODULES_DIR", ROOT_PATH."/endpoint");
define("PROVISIONER_BASE", ROOT_PATH.'/');
require_once(PROVISIONER_BASE.'autoload.php');


class StackTest extends PHPUnit_Framework_TestCase
{	
	/**
	* Test for valid/missing json in all json files
	**/
	public function testjsons()
	{
		$brand = 'polycom';
		$family = 'spipm';
		$model = 'SoundPoint IP 550';
		$class = "endpoint_" . $brand . "_" . $family . '_phone';

		$endpoint = new $class();
		$data = $endpoint->file2json(MODULES_DIR.'/master.json');
		foreach (glob(MODULES_DIR."/*",GLOB_ONLYDIR) as $branddir) {
			$brand_filename = $branddir.'/brand_data.json';
			$this->assertTrue(file_exists($brand_filename), $branddir . ' is missing brand_data.json');
		    if(file_exists($brand_filename)) {
				$brand_data = $endpoint->file2json($brand_filename);
				$family_list = $brand_data['data']['brands']['family_list'];
				foreach($family_list as $familydir) {
					$family_filename = $branddir . '/' . $familydir['directory'] . '/family_data.json';
					$this->assertTrue(file_exists($family_filename), $branddir . '/' . $familydir['directory'] . ' is missing family_data.json');
					if(file_exists($family_filename)) {
						$family_data = $endpoint->file2json($family_filename);
						$model_data = $family_data['data']['model_list'];
						foreach($model_data as $modeld) {
							foreach($modeld['template_data'] as $template_filenames) {
								$this->assertTrue(file_exists($branddir . '/' . $familydir['directory'] . '/'. $template_filenames), $branddir . '/' . $familydir['directory'] . ' is missing '. $template_filenames);
								$template_data = $endpoint->file2json($branddir . '/' . $familydir['directory'] . '/'. $template_filenames);
							}
						}
				    }
				}
			}
		}
	}
	
	/**
	* Test autoloader
	**/
	public function testautoload()
	{
		$brand = 'polycom';
		$family = 'spipm';
		$model = 'SoundPoint IP 550';

		$class = "endpoint_" . $brand . "_" . $family . '_phone';

		$this->endpoint = new $class();	
		
		$this->assertTrue(is_object($this->endpoint));
		
		$this->assertTrue(method_exists($this->endpoint,"generate_all_files"));
	}
	
	public function testreplacementone() 
	{
		$brand = 'polycom';
		$family = 'spipm';
		$model = 'SoundPoint IP 550';

		$class = "endpoint_" . $brand . "_" . $family . '_phone';

		$endpoint = new $class();
		
		$endpoint->brand_name = $brand;
		$endpoint->family_line = $family;
		$endpoint->model = $model;
		$endpoint->mac = '0123456789AB';
		$endpoint->processor_info = 'PHPUNIT TESTING';
		
		if (!class_exists("DateTimeZone")) { require(PROVISIONER_BASE.'/samples/tz.php'); }
		$endpoint->DateTimeZone = new DateTimeZone('US/Pacific');;
		
		$endpoint->settings['line'][0] = array(
			"username" => "username_l1",
			"secret" => "secret_l1",
			"server_host" => "server_host_l1",
			"server_port" => "server_port_l1",
			"displayname" => "displayname_l1",
			"line" => "1"
		);
		$unmodified_settings = $endpoint->settings;
		$endpoint->generate_all_files();
		
		foreach($unmodified_settings['line'][0] as $key => $linedata) {
			$this->assertSame($linedata, $endpoint->replacement_array['lines'][1]['$'.$key], 'Error with '.$key.' replacement');
		}		
	}
	/**
	* @group large
	*/	
	public function testreplacementall() 
	{
		$brand = 'polycom';
		$family = 'spipm';
		$model = 'SoundPoint IP 550';
		$class = "endpoint_" . $brand . "_" . $family . '_phone';

		$all_list = array();

		$endpoint = new $class();
		$data = $endpoint->file2json(MODULES_DIR.'/master.json');
		foreach (glob(MODULES_DIR."/*",GLOB_ONLYDIR) as $branddir) {
			$brand_filename = $branddir.'/brand_data.json';
		    if(file_exists($brand_filename)) {
				$brand_data = $endpoint->file2json($brand_filename);
				$bn = $brand_data['data']['brands']['directory'];
				$all_list[$bn] = array();
				$family_list = $brand_data['data']['brands']['family_list'];
				foreach($family_list as $familydir) {
					$family_filename = $branddir . '/' . $familydir['directory'] . '/family_data.json';
					if(file_exists($family_filename)) {
						$family_data = $endpoint->file2json($family_filename);
						$fn = $family_data['data']['directory'];
						$all_list[$bn][$fn] = array();
						
						$model_data = $family_data['data']['model_list'];
						foreach($model_data as $modeld) {
							$all_list[$bn][$fn][] = $modeld;
							foreach($modeld['template_data'] as $template_filenames) {
								$template_data = $endpoint->file2json($branddir . '/' . $familydir['directory'] . '/'. $template_filenames);
							}
						}
				    }
				}
			}
		}
		unset($endpoint);
		
		foreach($all_list as $kbrand => $family_list) {
			$brand = $kbrand;
			foreach($family_list as $kfamily => $model_list) {
				$family = $kfamily;
				$class = "endpoint_" . $brand . "_" . $family . '_phone';
				foreach($model_list as $final_list) {
					$endpoint = new $class();
					$endpoint->brand_name = $brand;
					$endpoint->family_line = $family;
					$endpoint->model = $final_list['model'];
					$endpoint->mac = '0123456789AB';
					$endpoint->processor_info = 'PHPUNIT TESTING';

					if (!class_exists("DateTimeZone")) { require(PROVISIONER_BASE.'/samples/tz.php'); }
					$endpoint->DateTimeZone = new DateTimeZone('US/Pacific');;

					$endpoint->settings['line'][0] = array(
						"username" => "username_l1",
						"secret" => "secret_l1",
						"server_host" => "server_host_l1",
						"server_port" => "server_port_l1",
						"displayname" => "display_l1",
						"line" => "1"
					);
					$unmodified_settings = $endpoint->settings;
					
					$endpoint->generate_all_files();
					
					//Test Line stuff
					foreach($unmodified_settings['line'][0] as $key => $linedata) {
						if(isset($endpoint->replacement_array['lines'][1]['$'.$key])) {
							$this->assertSame($linedata, $endpoint->replacement_array['lines'][1]['$'.$key], 'Error with '.$key.' replacement on model:'.$endpoint->model);
						}
					}
					//Load the json templates and assign values then replace those values in the file
					
					unset($endpoint);
				}
			}
		}
	}
}
