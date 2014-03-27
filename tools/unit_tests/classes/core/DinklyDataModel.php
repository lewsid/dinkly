<?php

class DinklyDataModelTest extends PHPUnit_Framework_TestCase
{
	protected $user;

	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");

		$this->user = new AdminUser();
		$this->user->init(1);
		$this->valid_array = array(
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

	protected function tearDown()
	{
		unset($this->user);
	}
	public function testInit()
	{
		//create two users and make sure they both have the same output
		$this->new_user1= new AdminUser();
		$this->new_user2= new AdminUser();
		$this->assertEquals($this->new_user1, $this->new_user2);
	}
	public function testInitWith()
	{
			//test init for user that already exists
			$this->new_user= new AdminUser();
			$this->new_user->initWith($this->valid_array);
			$this->assertEquals($this->new_user, $this->user);
	}
	public function testGetSelectQuery()
	{
	//
				$testRegistry= array(
													'id' ,
													'created_at' ,
													'updated_at' ,
													'username' ,
													'password' ,
													'first_name' ,
													'last_name' ,
													'title' ,
													'last_login_at' ,
													'login_count' 
												);
		$testSelect="select";
		$columns ="";
		foreach($testRegistry as $pos => $col)
		{
				if ($pos !=9)
				$columns.= " `".$col."`,";
				else
				$columns.= " `".$col."`";

		}
		 $testSelect.= $columns. " from " . "admin_user";
													
		$this->assertEquals($testSelect, $this->user->getSelectQuery());
	}
		public function testDelete()
	{
			//test delete when user doesn't exists
			$this->test_user= new AdminUser();
			$this->test_user->init(2);
			$this->assertEquals(0,$this->test_user->delete());
	}
	// protected function testUpdate()
	// {

	// }
	// protected function testInsert()
	// {

	// }

	// //change back to protected
	// protected function testGetColumns()
	// {
	// 	$testRegistry= array();
	// 	$testReg = $this->valid_array;
	// 	foreach($testReg as $key)
	// 	{
	// 		$testRegistry[]= '`' . key($testReg) . '`';
	// 		next($testReg);

	// 	}

	// 	 $this->assertEmpty(array_diff_assoc($testRegistry,$this->user->getColumns()));


	// }

	public function testGetRegistry()
	{
	$testRegistry= array(
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



//change back to protected
	// protected function testGetDBTable()
	// {
	// 	$dbTable = 'admin_user';
	// 	$this->assertEquals($dbTable,$this->user->getDBTable());
	// }
	public function testForceDirty()
	{
			$testDirtyRegistry= array(
		'id' => true,
		'created_at' => true,
		'updated_at' => true,
		'username' => true,
		'password' => true,
		'first_name' => true,
		'last_name' => true,
		'title' => true,
		'last_login_at' => true,
		'login_count' => true,
	);
			// $test_dirty_reg = $this->user->forceDirty();
			// $this->assertEmpty(array_diff_assoc($testDirtyRegistry, $test_dirty_reg));

	}
	public function testGetDB()
	{
		$dsn = 'mysql:dbname=admin;host=localhost;port=3306';
		$username = 'root';
		$password = 'root';
		$db = new PDO($dsn, $username, $password);
		$this->assertEquals($db, $this->user->getDB());
	}
	public function testSetDB()
	{
		//old PDO object
		$dsn = 'mysql:dbname=admin;host=localhost;port=3306';
		$username = 'root';
		$password = 'root';
		$db_old = new PDO($dsn, $username, $password);
		//new PDO object
		$dsn = 'mysql:dbname=quiz;host=localhost;port=3306';
		$username = 'root';
		$password = 'root';
		$db_new = new PDO($dsn, $username, $password);

		$this->user->setDB($db_new);


		$this->assertEquals($db_new, $this->user->getDB());


	}
}
?>