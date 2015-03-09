<?php

class DinklyBaseTest extends PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");
		
		$this->valid_config = 
			array(
				"settings" => array(
					"dinkly_version" => Dinkly::getConfigValue('dinkly_version', 'global')
				),
				"apps" => array(
					"frontend" => array(
						"default_app" => true,
            			"default_module" => "landing",
            			"base_href" => "/",
            			"enabled" => true,
            			"app_name" => "Dinkly Frontend",
            			"app_description" => "The humblest little MVC Framework",
            		),
					"admin" => array(
						"default_module" => "home",
						"base_href" => "/admin",
						"enabled" => true,
						"app_name" => "Dinkly Admin",
						"app_description" => "Admin for the humblest little MVC",
						"copyright" => "Dinkly"
					),
					"api" => array(
						"base_href" => "/api", 
						"enabled" => true,
						"default_module" => "api",
						"app_name" => "Dinkly API"
					),
					"doc" => array(
						"app_name" => "Dinkly Documentation",
						"base_href" => "/doc",
						"default_module"=> "home",
						"enabled"=>true
					)
				)
			);
		$this->bad_config = 
					array(
				"settings" => array(
					"dinkly_version" => Dinkly::getConfigValue('dinkly_version', 'global')
				),
				"apps" => array(
				)
			);

		$this->valid_modules = array("home", "login", "user");
		
		$context = null;

		$this->valid_context = 
			array('current_app_name' => 'admin', 'module' => 'home', 'view' => 'default', 'parameters'=>array('id'=>1));
	}

	public function testRoute()
	{

	}

	public function testLoadError()
	{
		
	}

	public function testGetContext()
	{
		$this->base= new BaseDinkly();
		$example_uri = "/admin/home/default/id/1";
		$this->context = $this->base->getContext($example_uri);
		$test_context = $this->valid_context;
		//test to make sure context is formatted correctly against example context
		$this->assertEquals($test_context['current_app_name'], $this->context['current_app_name']);
		$this->assertEquals($test_context['module'], $this->context['module']);
		$this->assertEquals($test_context['view'], $this->context['view']);
		$this->assertEquals($test_context['parameters']['id'], $this->context['parameters']['id']);
	}

	public function testLoadModule()
	{
		// We need to come back to this one...

		// $this->base= new DinklyBase();
		// $this->base->loadModule('admin');

		// //Provide sample URI to be used
		// $_SERVER['REQUEST_URI'] = "/admin/home/default/id/1";
		
		// //make sure view is constructed correctly
		// $this->assertEquals($this->base->getCurrentView(),'default');
		
		// //make sure module is constructed correctly
		// $this->assertEquals($this->base->getCurrentModule(),'home');
		
		// //make sure parameters stored correctly
		// $parameters=$this->base->getParameters();
		// $this->assertEquals($parameters['id'], 1);
		
		// //test upon failure to load module
		// $this->bad_base= new DinklyBase();
		// $this->assertFalse($this->bad_base->loadModule('bad'));
	}

	public function testGetCurrentView()
	{
		$this->base= new BaseDinkly();
		$_SERVER['REQUEST_URI']="/home/default/id/1";
		//make sure current view is set correctly based on URI
		$this->assertEquals($this->base->getCurrentView(),'default');
	}

	public function testGetCurrentModule()
	{
		$this->base= new BaseDinkly();
		$_SERVER['REQUEST_URI']="/home/default/id/1";
		//make sure current module is set correctly based on URI
		$this->assertEquals($this->base->getCurrentModule(),'home');
	}

	public function testGetParameters()
	{
	  	$this->base= new BaseDinkly();
		$example_uri = "/home/default/id/200";
		$this->context =$this->base->getContext($example_uri);
		$parameters=$this->base->getParameters();
		//test stored parameters agains sample context that is set
		$this->assertEquals($parameters['id'],200);
	}

	public function testGetDefaultApp()
	{
		//Test setting config values and getting default
		$_SESSION['dinkly']['config'] = $this->valid_config;
		$this->assertEquals(BaseDinkly::getDefaultApp(true), "frontend");
		$config = BaseDinkly::getDefaultApp();
		$this->assertEquals($this->valid_config['apps']['frontend'], $config);
	}

	public function testGetValidModules()
	{
		//Test output before setting modules manually
		// $this->assertEquals(DinklyBase::getValidModules("admin"), $this->valid_modules);
		
		// //Manually set sessions and check modules
		// $_SESSION['dinkly']['valid_modules']['admin'] = null;
		// $_SESSION['dinkly']['valid_modules']['admin'] = array("test");
		// $this->assertEquals(DinklyBase::getValidModules("admin"), array("test"));
	}

	public function testGetCurrentAppName()
	{
		//Test that we are able to retrieve the current app name
		$_SESSION['dinkly']['current_app_name'] = "test_app";
		$this->assertEquals(BaseDinkly::getCurrentAppName(), "test_app");
	}

	public function testGetConfigValue()
	{
		//Test getting value by manually setting config
		$_SESSION['dinkly']['config'] = $this->valid_config;
		$app_name = 'admin';
		$key = "copyright";
		$this->assertEquals(BaseDinkly::getConfigValue($key,$app_name), "Dinkly");
		
		//Test invalid key
		$dirty_key = "bad";
		$this->assertFalse(BaseDinkly::getConfigValue($dirty_key));
	}

	public function testConvertFromCamelCase()
	{
		$test_camel_case = "TestApp";
		$this->assertEquals(BaseDinkly::convertFromCamelCase($test_camel_case), "test_app");
	}
	
	public function testConvertToCamelCase()
	{
		$test_camel_case = "test_app";

		//Test with first letter capitalized
		$this->assertEquals(BaseDinkly::convertToCamelCase($test_camel_case,true), "TestApp");
		
		//Test to regular camel case
		$this->assertEquals(BaseDinkly::convertToCamelCase($test_camel_case,false), "testApp");
	}

	public function testGetConfig()
	{
		$config = BaseDinkly::getConfig();
		$this->assertEquals($config['apps'], $this->valid_config['apps']);
	}

	public function testGetModuleHeader()
	{
		//Set header and get it to test set correctly
		BaseDinkly::setModuleHeader('admin');
		$this->assertEquals(BaseDinkly::getModuleHeader(), 'admin');
	}

	public function testSetModuleHeader()
	{
		//Set module header and test that it is set correctly
		BaseDinkly::setModuleHeader('admin');
		$this->assertEquals(BaseDinkly::getModuleHeader(), 'admin');

		//Test invalid header
		$this->assertNotEquals(BaseDinkly::getModuleHeader(), 'error');
	}

	public function testValidateConfig()
	{
		//test using a well constructed valid config
		$config = $this->valid_config;
		$this->assertTrue(BaseDinkly::validateConfig($config));
	}

	public function loadError()
	{
		//just need this to bypass DinklyBase::loadError()
	}
}
?>