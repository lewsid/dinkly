<?php

class DinklyDataModelTest extends PHPUnit_Framework_TestCase
{
	protected $user;

	public $username;
	
	public $password;

	public $dsn;

	public $test_dsn;

	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");

		$this->dsn = 'mysql:dbname=dinkly_unit_test;host=localhost;port=3306';
		$this->username = 'root';
		$this->password = 'root';

		//Prepulate table with test users
		DinklyDataConfig::setActiveConnection('unit_test');
		DinklyBuilder::buildTable('unit_test', 'TestUser', null, false);
		DinklyBuilder::loadAllFixtures('unit_test', false);

		$this->user = new TestUser();
		$this->user->init(1);
		$this->valid_array = 
			array(
				'id' 			=> $this->user->getId(),
				'created_at' 	=> $this->user->getCreatedAt(),
				'updated_at'	=> $this->user->getUpdatedAt(),
				'username' 		=> $this->user->getUserName(),
				'password' 		=> $this->user->getPassword(),
				'first_name' 	=> $this->user->getFirstName(),
				'last_name' 	=> $this->user->getLastName(),
				'title' 		=> $this->user->getTitle(),
				'last_login_at' => $this->user->getLastLoginAt(),
				'login_count' 	=> $this->user->getLoginCount() 
			);
	}
	
	public function testToArray()
	{
		$this->assertEmpty(array_diff_assoc($this->user->toArray(), $this->valid_array));
	}

	public function testIsNew()
	{
		$this->assertFalse($this->user->isNew());
	}

	public function testSet()
	{
		$this->assertTrue($this->user->setLastLoginAt(date('Y-m-d G:i:s')));
	}

	public function testHydrate()
	{
		//Test typical hydration ($hasDB = true)
		$this->assertTrue($this->user->hydrate($this->valid_array, true));

		//Test a hydration that results in no DB ($hasDB = false)
		$this->assertTrue($this->user->hydrate($this->valid_array));
	}

	public function testSave()
	{
		//Test when there has been no change
		$this->assertTrue($this->user->save());

		//Test when there has been some change
		$this->user->setLastLoginAt(date('Y-m-d G:i:s'));
		$this->assertEquals(1, $this->user->save());

		//Test when saving after a hydrate with $hasDB = false throws an exception
		$this->setExpectedException('Exception');
		$this->user->hydrate($this->valid_array, false);
		$this->assertFalse($this->user->save());
        $this->fail('An expected exception has not been raised.');
	}

	public function testInit()
	{
		//Create two users and make sure they both have the same output
		$this->new_user1 = new TestUser();
		$this->new_user2 = new TestUser();
		$this->assertEquals($this->new_user1, $this->new_user2);
	}

	public function testInitWith()
	{
		//Test init for user that already exists
		$this->new_user= new TestUser();
		$this->new_user->initWith(array('id' => 1));
		$this->assertEquals($this->new_user, $this->user);
	}

	public function testGetSelectQuery()
	{
		$testRegistry = 
			array(
				'id',
				'created_at',
				'updated_at',
				'username',
				'password',
				'first_name',
				'last_name',
				'title',
				'last_login_at',
				'login_count'
			);

		$testSelect = "select ";
		$columns = "";
		
		foreach($testRegistry as $pos => $col)
		{
			if($pos != 9)
				$columns .= $col . ", ";
			else
				$columns .= $col;
		}
		
		$testSelect .= $columns. " from " . "test_user";
													
		$this->assertEquals($testSelect, $this->user->getSelectQuery());
	}
	
	public function testDelete()
	{
		//Test delete when user doesn't exist
		$this->test_user = new TestUser();
		$this->test_user->init(-1);
		$this->assertEquals(0, $this->test_user->delete());
	}

	public function testGetRegistry()
	{
		$testRegistry = 
			array(
				'id' => 'Id',
				'created_at' => 'CreatedAt',
				'updated_at' => 'UpdatedAt',
				'username' => 'Username',
				'password' => 'Password',
				'first_name' => 'FirstName',
				'last_name' => 'LastName',
				'title' => 'Title',
				'last_login_at' => 'LastLoginAt',
				'login_count' => 'LoginCount',
			);
		
		$this->assertEquals($testRegistry, $this->user->getRegistry());
	}

	public function testForceDirty()
	{
		//Need some test code to confirm that when forceDirty is executed, the entire model refreshes
	}

	public function testGetDB()
	{
		$db = new PDO($this->dsn, $this->username, $this->password);
		$this->assertEquals($db, $this->user->getDB());
	}

	public function testSetDB()
	{
		//Fetch PDO object
		$db = new PDO($this->dsn, $this->username, $this->password);

		$this->user->setDB($db);

		$this->assertEquals($db, $this->user->getDB());
	}

	protected function tearDown()
	{
		unset($this->user);
	}
}
?>