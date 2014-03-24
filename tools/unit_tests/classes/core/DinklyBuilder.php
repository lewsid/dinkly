<?php

class DinklyBuilderTest extends PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");
	}

	public function testParseModelYaml()
	{
		//Test for reading model yaml and parsing it into an array
		$this->assertArrayHasKey('table_name', DinklyBuilder::parseModelYaml('unit_test', 'TestUser', false));
	}

	public function testBuildTable()
	{
		//Test the building of a table, including the creation of the database if doesn't already exist
		$this->assertTrue(DinklyBuilder::buildTable('unit_test', 'TestUser', null, false));

		//Grab a model and turn it into an array to be manipulated in tests that follow
		$model_yaml = DinklyBuilder::parseModelYaml('unit_test', 'TestUser', false);

		//Test that passing a pre-parsed yaml array works
		$this->assertTrue(DinklyBuilder::buildTable('unit_test', 'TestUser', $model_yaml, false));

		//Pop an mysql keyword into the yaml, which might cause MySQL to choke
		$model_yaml['registry'][] = array('key' => array('type' => 'int', 'allow_null' => true));

		//Pass bad yaml, which shouldn't cause problems here thanks to the wrapping of the column names in quotes
		$this->assertTrue(DinklyBuilder::buildTable('unit_test', 'TestUser', $model_yaml, false));
	}
	

	protected function tearDown()
	{
	}
}
?>