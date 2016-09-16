<?php

class DinklyDataCollectionTest extends PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");
		
		//Prepulate database and load with test users
		DinklyDataConfig::setActiveConnection('demo');
		DinklyBuilder::buildTable('demo', 'DemoUser', null, false);
		DinklyBuilder::loadAllFixtures('demo', false, true);
	}

	public function testGetAll()
	{
		//Test that collection of users matches that of custom built array
		$this->test_users = DemoUserCollection::getAll();
		$this->user = new DemoUser();
		$this->user->init(1);
		$test_collection[] = $this->user;

		$this->another_user = new DemoUser();
		$this->another_user->init(2);
		$test_collection[] = $this->another_user;

		$this->another_user = new DemoUser();
		$this->another_user->init(3);
		$test_collection[] = $this->another_user;
		
		$this->assertEquals($this->test_users, $test_collection);
		$this->assertEquals($this->test_users[0], $test_collection[0]);
		$this->assertEquals($this->test_users[0]->getFirstName(), $test_collection[0]->getFirstName());
	}

	public function testGetWith()
	{
		//Test that collection pulled is correct by param
		$input_array = array('id' => 1);
		$this->admin_users = DemoUserCollection::getWith(null, $input_array);
		$this->user = new DemoUser();
		$this->user->init(1);
		$test_collection[] = $this->user;
		
		$this->assertEquals($this->admin_users, $test_collection);
		$this->assertEquals($this->admin_users[0], $test_collection[0]);
		$this->assertEquals(count($this->admin_users), 1);
		
		//Test that collection is not same using different param
		$this->user_collection = DemoUserCollection::getWith(null, array('id' => 2));
		$this->assertNotEquals($this->user_collection, $this->admin_users);
		
		//Test that collection is correct using different param
		$this->collection = DemoUserCollection::getWith(null, array('FirstName' => 'Boba', 'LastName' => 'Fett'));

		$this->assertEquals($this->collection, $test_collection);
		$this->assertEquals(count($this->collection), 1);

		//Test to make sure returns array of objects
		$this->assertEquals($this->collection[0]->getId(), 1);
		$this->assertEquals($this->collection[0]->getUsername(), $this->user->getUsername());
	}
}
?>