<?php

class DinklyDataConfigTest extends PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");

		$this->db_creds = array('host' => 'localhost', 'user' => 'root', 'pass' => 'root', 'name' => 'dinkly_unit_test');

		//Prepulate database and load with test users
		DinklyDataConfig::setActiveConnection('unit_test');
		DinklyBuilder::buildTable('unit_test', 'TestUser', null, false);
		DinklyBuilder::loadAllFixtures('unit_test', false);
	}
	
	public function testSetActiveConnection()
	{
		//Test when input is not an array and has no connection
		$test_input = "hello";
		$this->assertFalse(DinklyDataConfig::setActiveConnection($test_input));

		//Now make connection with valid creds
		$this->assertTrue(DinklyDataConfig::setActiveConnection($this->db_creds));

		//Make sure connection was actually made
		$this->assertTrue(DinklyDataConfig::hasConnection('unit_test'));
	}

	public function testGetDBCreds()
	{
		//Make sure the yaml is parsing correctly
		$creds = DinklyDataConfig::getDBCreds(); 

		$this->assertEquals($this->db_creds, $creds);
	}

	public function testHasConnection()
	{
		//Test on connection name that does not exist
		$this->assertFalse(DinklyDataConfig::hasConnection('monkey_shoes'));

		//Test create connection and then see if exists
		DinklyDataConfig::setActiveConnection($this->db_creds);
		$this->assertTrue(DinklyDataConfig::hasConnection('unit_test'));
	}

	public function testLoadDBCreds()
	{
		$this->assertTrue(DinklyDataConfig::setActiveConnection($this->db_creds));
	}
}
?>