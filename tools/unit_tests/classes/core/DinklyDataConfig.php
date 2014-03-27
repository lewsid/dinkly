<?php
use Symfony\Component\Yaml\Yaml;
class DinklyDataConfigTest extends PHPUnit_Framework_TestCase
{
		protected $db;

	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");


	}
		public function testSetActiveConnection(){

			//test when input is not an array and has no connection
			$test_input = "hello";
			$this->assertFalse(DinklyDataConfig::setActiveConnection($test_input));
			//now make connection with valid creds
			$db_creds=array('DB_HOST'=>'localhost','DB_USER'=>'root','DB_PASS'=>'root','DB_NAME'=>'admin');
			$this->assertTrue(DinklyDataConfig::setActiveConnection($db_creds));
			//make sure connection was actually made
			$this->assertTrue(DinklyDataConfig::hasConnection('admin'));
	}

	public function testGetDBCreds()
	{


		//make sure the yaml is parsing correctly
		$db_creds = array('admin'=>array('DB_HOST'=>'localhost','DB_USER'=>'root','DB_PASS'=>'root','DB_NAME'=>'admin'));
		$creds = DinklyDataConfig::getDBCreds(); 
		$this->assertEquals($db_creds['admin'],$creds);
		
	}
	public function testHasConnection(){

				//test on connection name that does not exist
				$this->assertFalse(DinklyDataConfig::hasConnection('admin_table'));

				//test create connection and then see if exists
				$db_creds=array('DB_HOST'=>'localhost','DB_USER'=>'root','DB_PASS'=>'root','DB_NAME'=>'admin');
				DinklyDataConfig::setActiveConnection($db_creds);
				$this->assertTrue(DinklyDataConfig::hasConnection('admin'));


	}

		public function testLoadDBCreds(){

			$db_creds=array('DB_HOST'=>'localhost','DB_USER'=>'root','DB_PASS'=>'root','DB_NAME'=>'admin');
			$this->assertTrue(DinklyDataConfig::setActiveConnection($db_creds));



	}

}
?>
<!-- php vendor/phpunit/phpunit/phpunit --bootstrap config/bootstrap.php tools/unit_tests/classes/core/DinklyDataConfig.php -->