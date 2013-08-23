<?php

class provisionerTest extends PHPUnit_Framework_TestCase {
    protected $stack;
	protected $adaptor;
 
    protected function setUp()
    {
        require_once(dirname(dirname(__FILE__)).'/bootstrap.php');
		$this->adaptor = new adapter_generic_adapter();
    }
 
    public function dataProvider()
    {
        return array(
			array(
				array(
					"brand" => "yealink",
					"model"	=> "t26",
					"mac"	=>	"ADCD833A2F69",
					"lines"	=> array(
						array(
							"enable" => 1,
							"display_name" => "Test",
							"username"	=> "username",
							"auth_name"	=> "auth_name",
							"auth_password"	=>	"auth_password"
						)
					)
				)
			)
        );
    }
	
    /**
     * @dataProvider dataProvider
     */
    public function testLoader($arrConfig) 
	{
		$o = $this->adaptor->load_settings($arrConfig);
		
		$this->assertEquals($arrConfig['mac'],$o[0]['mac']);
		$this->assertEquals($arrConfig['lines'][0]['display_name'],$o[0]['lines'][0]['display_name']);
    }
	
	public function testInvalidFiles()
	{
		$this->setExpectedException('Exception', 'Json File Doesnt Exist');
		$this->adaptor->load_json('0');
	}
	
    public function testLoadConfig()
    {
		$file = file_get_contents(dirname(__FILE__).'/config1.conf');
		$json = json_decode($file,TRUE);
		$o = $this->adaptor->load_json(dirname(__FILE__).'/config1.conf');
		$this->assertEquals($json['mac'],$o[0]['mac']);
		$this->assertEquals($json['lines'][0]['display_name'],$o[0]['lines'][0]['display_name']);
		
		//Now mash the data with a new set and see what we get
		$file = file_get_contents(dirname(__FILE__).'/config2.conf');
		$json = json_decode($file,TRUE);
		$o = $this->adaptor->load_json(dirname(__FILE__).'/config2.conf');
				
		$this->assertEquals($json['mac'],$o[0]['mac']);
		$this->assertEquals($json['lines'][0]['display_name'],$o[1]['lines'][0]['display_name']);
		
		$config = $this->adaptor->get_config_manager();
		$array = $config->get_merged_config_objects();
		
		$this->assertEquals($json['lines'][0]['display_name'],$array['lines'][0]['display_name']);
    }
	
    public function testGenerateConfig()
    {
		$this->adaptor->load_json(dirname(__FILE__).'/config1.conf');
		
		$config = $this->adaptor->get_config_manager();

		$out = $config->generate_config_files();
		
		$config->get_settings();
	}
}
