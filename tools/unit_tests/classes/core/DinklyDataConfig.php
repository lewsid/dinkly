<?php

class DinklyDataConfigTest extends PHPUnit_Framework_TestCase
{
		protected $db;

	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");
		$dsn = 'mysql:dbname=admin;host=localhost;port=3306';
		$username = 'root';
		$password = 'root';
		$this->db = new PDO($dsn, $username, $password);

	}




	public function testLoadDBCreds()
	{
		 // $this->assertTrue(true);
	}
	

}
?>
