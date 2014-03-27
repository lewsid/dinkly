<?php

class DinklyDataConnectorTest extends PHPUnit_Framework_TestCase
{
	public $username;
	
	public $password;

	public $dsn;

	public function setUp()
	{
		$this->dsn = 'mysql:dbname=admin;host=localhost;port=3306';
		$this->username = 'root';
		$this->password = 'root';
	}

	public function testFetchDB()
	{
		//Test to make sure DB is fetched correctly
		$db = DinklyDataConnector::fetchDB();
		
		$new_db = new PDO($this->dsn, $this->username, $this->password);
		$this->assertEquals($new_db, $est);
	}

	public function testTestDB()
	{
		//test now that DB is fetched correctly
		$this->assertTrue(DinklyDataConnector::testDB());
	}
}
?>