<?php

class DinklyBuilderTest extends PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");

		//Prepulate database and load with test users
		DinklyDataConfig::setActiveConnection('demo');
		DinklyBuilder::buildTable('demo', 'DemoUser', null, false);
		DinklyBuilder::loadAllFixtures('demo', false, true);
	}

	public function testParseModelYaml()
	{
		//Test for reading model yaml and parsing it into an array
		$this->assertArrayHasKey('table_name', DinklyBuilder::parseModelYaml('demo', 'DemoUser', false));
	}

	public function testBuildTable()
	{
		//Test the building of a table, including the creation of the database if doesn't already exist
		$this->assertTrue(DinklyBuilder::buildTable('demo', 'DemoUser', null, false));

		//Grab a model and turn it into an array to be manipulated in tests that follow
		$model_yaml = DinklyBuilder::parseModelYaml('demo', 'DemoUser', false);

		//Test that passing a pre-parsed yaml array works
		$this->assertTrue(DinklyBuilder::buildTable('demo', 'DemoUser', null, $model_yaml, false));
	}

	public function testDropTable()
	{
		//Build the table and database as needed, load test user
		DinklyBuilder::buildTable('demo', 'DemoUser', null, false);
		DinklyBuilder::loadAllFixtures('demo', false, true);

		//Test that we have a hyrdrated user model to work with
		$user = new DemoUser();
		$this->assertTrue($user->init(1));

		//Test the successful removal of the table
		$this->assertTrue(DinklyBuilder::dropTable('demo', 'DemoUser'));
	}

	public function testForMySQLKeywords()
	{
		//Let's make sure to start without a table
		DinklyBuilder::dropTable('demo', 'DemoUser');

		//Grab a model and turn it into an array to be manipulated in tests that follow
		$model_yaml = DinklyBuilder::parseModelYaml('demo', 'DemoUser', false);
		
		//Create a table with a column named after a MySQL keyword
		$model_yaml['registry'][] = array('key' => array('type' => 'int', 'allow_null' => true));
		$this->assertTrue(DinklyBuilder::buildTable('demo', 'DemoUser', null, $model_yaml, false));
	}
	

	protected function tearDown()
	{
	}
}
?>