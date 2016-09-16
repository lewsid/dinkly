<?php

class DinklyDataConnectorTest extends PHPUnit_Framework_TestCase
{
	public $username;
	
	public $password;

	public $dsn;

	public function setUp()
	{
		date_default_timezone_set("Europe/Paris");
		
		$this->dsn = 'mysql:dbname=dinkly_unit_test;host=localhost;port=3306';
		$this->username = 'root';
		$this->password = 'root';

		//Prepulate database and load with test users
		DinklyDataConfig::setActiveConnection('demo');
		DinklyBuilder::buildTable('demo', 'DemoUser', null, false);
		DinklyBuilder::loadAllFixtures('demo', false, true);
	}

	public function testFetchDB()
	{
		//Test to make sure DB is fetched correctly
		$db = DinklyDataConnector::fetchDB();
		
		$new_db = new PDO($this->dsn, $this->username, $this->password);
		$this->assertEquals($new_db, $db);
	}

	public function testTestDB()
	{
		//test now that DB is fetched correctly
		$this->assertTrue(DinklyDataConnector::testDB());
	}
}
?>