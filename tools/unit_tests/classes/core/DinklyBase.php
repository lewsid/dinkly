<?php
use Symfony\Component\Yaml\Yaml;
class DinklyBaseTest extends PHPUnit_Framework_TestCase
{
		

	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");
				$this->valid_config = 
									array(
					  "admin"=> array(
					    "base_href"=> "/", 
					    "default_app"=> true, 
					    "app_name"=> "Dinkly Admin", 
					    "copyright"=> "Dinkly", 
					    "default_module"=> "home", 
					    "app_description"=> "Just a humble little PHP MVC Framework"
					  ), 
					  "global"=> array(
					    "dinkly_version"=> 1.25
					  ), 
					  "api"=> array(
					    "base_href"=> "/api", 
					    "default_module"=> "api", 
					    "app_name"=> "Dinkly API"
					  )
					);
				$this->valid_modules= array("home","login","user");
		

	}
	public function testLoadError(){

	}
	public function testLoadModule(){

	}
	public function testCurrentView(){

	}
	public function testCurrentModule(){

	}
	public function testGetParameters(){

	}
	public function testGetDefaultApp(){
		//test setting config values and getting default
		$_SESSION['dinkly']['config']=$this->valid_config;
		$this->assertEquals(DinklyBase::getDefaultApp(true),"admin");
		$this->assertEquals(DinklyBase::getDefaultApp(),$this->valid_config['admin']);
	}
	public function testGetValidModules(){
		//test output before setting modules manually
		$this->assertEquals(DinklyBase::getValidModules("admin"),$this->valid_modules);
		//manually set sessions and check modules
		$_SESSION['dinkly']['valid_modules_admin']=null;
		$_SESSION['dinkly']['valid_modules_admin']=array("test");
		$this->assertEquals(DinklyBase::getValidModules("admin"),array("test"));
	}
	public function testGetCurrentAppName(){

		$_SESSION['dinkly']['current_app_name']="test_app";
		$this->assertEquals(DinklyBase::getCurrentAppName(),"test_app");

	}
	public function testGetConfigValue(){
		//test getting value by manually setting config
		$_SESSION['dinkly']['config']=$this->valid_config;
		$app_name = 'admin';
		$key="copyright";
		$this->assertEquals(DinklyBase::getConfigValue($key,$app_name),"Dinkly");
		//test invalid key
		$dirty_key="bad";
		$this->assertFalse(DinklyBase::getConfigValue($dirty_key));

	}
	public function testConvertFromCamelCase(){
		$test_camel_case ="TestApp";
		$this->assertEquals(DinklyBase::convertFromCamelCase($test_camel_case),"test_app");
	}
		public function testConvertToCamelCase(){
		$test_camel_case ="test_app";
		//test with first letter capitalized
		$this->assertEquals(DinklyBase::convertToCamelCase($test_camel_case,true),"TestApp");
		//test to regular camel case
		$this->assertEquals(DinklyBase::convertToCamelCase($test_camel_case,false),"testApp");
	}
	public function testGetConfig(){
		//test before config is set
		$this->assertEquals(DinklyBase::getConfig(),$this->valid_config);
		//test config value is set
		$_SESSION['dinkly']['config']="test_set_config";
		$this->assertEquals(DinklyBase::getConfig(),"test_set_config");

	}
	public function testGetModuleHeader(){
		//set header and get it to test set correctly
		DinklyBase::setModuleHeader('admin');
		$this->assertEquals(DinklyBase::getModuleHeader(),'admin');

	}
	public function testSetModuleHeader(){
		//set module header and test that it is set correctly
		DinklyBase::setModuleHeader('admin');
		$this->assertEquals(DinklyBase::getModuleHeader(),'admin');
		//test invalid header
		$this->assertNotEquals(DinklyBase::getModuleHeader(),'error');
	}
	public function testLoadApp(){
		//test on app that exists
		$this->assertTrue(DinklyBase::loadApp('admin'));
		//test on app that does not exist
		$this->assertFalse(DinklyBase::loadApp('null'));
	}
	public function loadError(){
		//just need this to bypass DinklyBase::loadError()
	}
}
?>
<!-- php vendor/phpunit/phpunit/phpunit --bootstrap config/bootstrap.php tools/unit_tests/classes/core/DinklyBase.php -->