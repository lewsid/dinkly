<?php
use Symfony\Component\Yaml\Yaml;
class DinklyDataConfigTest extends PHPUnit_Framework_TestCase
{
		protected $db;

	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");


	}

	public function testGetDBCreds()
	{
		//make sure the yaml is parsing correctly
		$db_creds = Yaml::parse($_SERVER['APPLICATION_ROOT'] . "config/db.yml");
		$creds = DinklyDataConfig::getDBCreds(); 
		$this->assertEquals($db_creds['admin'],$creds);
		
	}
	public function testHasConnection(){

				//test on connection that does not exist
				$this->assertFalse(DinklyDataConfig::hasConnection('admin_table'));

				//test create connection and then see if exists
				DinklyDataConfig::loadDBCreds();
				$this->assertTrue(DinklyDataConfig::hasConnection('admin'));


	}
		public function testSetActiveConnection(){

		  $db_creds = Yaml::parse($_SERVER['APPLICATION_ROOT'] . "config/db.yml");  
			$_SESSION['dinkly']['db_creds'] = $db_creds; 
			$set_db=DinklyDataConfig::setActiveConnection('admin');
			$this->assertTrue($set_db);
			//make sure connection was actually made
			$this->assertTrue(DinklyDataConfig::hasConnection('admin'));

	}
		public function testLoadDBCreds(){

			$db_creds= DinklyDataConfig::getDBCreds(); 
			$first_connection = key($db_creds);
		



	}

}
?>
<!-- php vendor/phpunit/phpunit/phpunit --bootstrap config/bootstrap.php tools/unit_tests/classes/core/DinklyDataConfig.php -->