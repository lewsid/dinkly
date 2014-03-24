<?php

class DinklyDataConfigTest extends PHPUnit_Framework_TestCase
{
		protected $db;

	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");


	}

	public function testLoadDBCreds()
	{
		 // $this->assertTrue(true);
		$creds=DinklyDataConfig::getDBCreds();
	}
	

}
?>
<!-- php vendor/phpunit/phpunit/phpunit --bootstrap config/bootstrap.php tools/unit_tests/classes/core/DinklyDataConfig.php -->