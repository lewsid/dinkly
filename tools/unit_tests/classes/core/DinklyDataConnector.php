<?php

class DinklyDataConnectorTest extends PHPUnit_Framework_TestCase
{

	public function testFetchDB()
	{
		//test to make sure DB is fetched correctly
		$this->test = DinklyDataConnector::fetchDB();
		$dsn = 'mysql:dbname=admin;host=localhost;port=3306';
		$username = 'root';
		$password = 'root';
		$this->new_db = new PDO($dsn, $username, $password);
		$this->assertEquals($this->new_db,$this->test);

	}

	public function testTestDB(){
	
		//test now that DB is fetched correctly
		$this->assertTrue(DinklyDataConnector::testDB());





	}
	

}
?>