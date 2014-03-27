<?php

class DinklyDataConfigTest extends PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");

		$this->db_creds = array('DB_HOST'=>'localhost', 'DB_USER'=>'root', 'DB_PASS'=>'root', 'DB_NAME'=>'admin');
	}
	
	public function testSetActiveConnection()
	{
		//Test when input is not an array and has no connection
		$test_input = "hello";
		$this->assertFalse(DinklyDataConfig::setActiveConnection($test_input));

		//Now make connection with valid creds
		$this->assertTrue(DinklyDataConfig::setActiveConnection($this->db_creds));

		//Make sure connection was actually made
		$this->assertTrue(DinklyDataConfig::hasConnection('admin'));
	}

	public function testGetDBCreds()
	{
		//Make sure the yaml is parsing correctly
		$creds = DinklyDataConfig::getDBCreds(); 
		$this->assertEquals($this->db_creds['admin'], $creds);
	}

	public function testHasConnection()
	{
		//Test on connection name that does not exist
		$this->assertFalse(DinklyDataConfig::hasConnection('monkey_shoes'));

		//Test create connection and then see if exists
		DinklyDataConfig::setActiveConnection($this->db_creds);
		$this->assertTrue(DinklyDataConfig::hasConnection('admin'));
	}

	public function testLoadDBCreds()
	{
		$this->assertTrue(DinklyDataConfig::setActiveConnection($this->db_creds));
	}
}
?>