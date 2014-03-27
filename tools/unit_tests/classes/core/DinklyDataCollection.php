<?php

class DinklyDataCollectionTest extends PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		//Prepulate table with test users
		DinklyBuilder::loadAllFixtures('unit_test', false);
	}

	public function testGetAll()
	{
		//Test that collection of users matches that of custom built array
		$this->admin_users= TestUserCollection::getAll();
		$this->user = new TestUser();
		$this->user->init(1);

		$this->another_user = new AdminUser();
		$this->another_user->init(2);
		
		$test_collection[] = $this->user;
		$test_collection[] = $this->another_user;
		
		$this->assertEquals($this->admin_users, $test_collection);
		$this->assertEquals($this->admin_users[0], $test_collection[0]);
		$this->assertEquals($this->admin_users[0]->getFirstName(), $test_collection[0]->getFirstName());
	}

	public function testGetWith()
	{
		//Test that collection pulled is correct by param
		$input_array = array('id' => 1);
		$this->admin_users= TestUserCollection::getWith($input_array);
		$this->user = new TestUser();
		$this->user->init(1);
		$test_collection[] = $this->user;
		
		$this->assertEquals($this->admin_users, $test_collection);
		$this->assertEquals($this->admin_users[0], $test_collection[0]);
		$this->assertEquals(count($this->admin_users), 1);
		
		//Test that collection is not same using different param
		$this->admin_user_collection = TestUserCollection::getWith(array('id' => 2));
		$this->assertNotEquals($this->admin_user_collection, $this->admin_users);
		
		//Test that collection is correct using different param
		$this->another_user = new TestUser();
		$this->another_user->init(2);
		$test_collection[] = $this->another_user;
		$this->collection = TestUserCollection::getWith(array('LoginCount' => 1));
		
		$this->assertEquals($this->collection, $test_collection);
		$this->assertEquals(count($this->collection), 2);

		//Test to make sure returns array of objects
		$this->assertEquals($this->collection[0]->getId(), 1);
		$this->assertEquals($this->collection[0]->getUsername(), $this->user->getUsername());
		$this->assertEquals($this->collection[1]->getId(), 2);
		$this->assertEquals($this->collection[1]->getUsername(), $this->another_user->getUsername());
	}
}
?>