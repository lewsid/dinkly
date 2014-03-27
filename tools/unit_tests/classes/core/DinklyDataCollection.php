<?php
class DinklyDataCollectionTest extends PHPUnit_Framework_TestCase
{
	//must have atleast two admin users in DB to test correctly
	// protected function setUp()
	// {
	// 	$add_user= new AdminUser();
	// 	$add_user->init(3);
	// 	$add_user->setCreatedAt(date('Y-m-d G:i:s'));
	// 	$add_user->setUpdatedAt(date('Y-m-d G:i:s'));
	// 	$add_user->save();
	// }	
	public function testGetAll()
	{
		//test that collection of admin users matches that of custom built array
		$this->admin_users= AdminUserCollection::getAll();
		$this->user = new AdminUser();
		$this->user->init(1);
		$this->another_user = new AdminUser();
		$this->another_user->init(2);
		$test_collection[]=$this->user;
		$test_collection[]=$this->another_user;
		$this->assertEquals($this->admin_users,$test_collection);
		$this->assertEquals($this->admin_users[0],$test_collection[0]);
		$this->assertEquals($this->admin_users[0]->getFirstName(),$test_collection[0]->getFirstName());
	}
public function testGetWith(){
		//test that collection pulled is correct by param
		$input_array=array('id'=>1);
		$this->admin_users= AdminUserCollection::getWith($input_array);
		$this->user = new AdminUser();
		$this->user->init(1);
		$test_collection[]=$this->user;
		$this->assertEquals($this->admin_users,$test_collection);
		$this->assertEquals($this->admin_users[0],$test_collection[0]);
		$this->assertEquals(count($this->admin_users),1);
		//test that collection is not same using different param
		$this->admin_user_collection=AdminUserCollection::getWith(array('id'=>2));
		$this->assertNotEquals($this->admin_user_collection,$this->admin_users);
		//test that collection is correct using different param
		$this->another_user = new AdminUser();
		$this->another_user->init(2);
		$test_collection[]=$this->another_user;
		$this->collection= AdminUserCollection::getWith(array('LoginCount'=>1));
		$this->assertEquals($this->collection,$test_collection);
		$this->assertEquals(count($this->collection),2);
		//test to make sure returns array of objects
		$this->assertEquals($this->collection[0]->getId(),1);
		$this->assertEquals($this->collection[0]->getUsername(),$this->user->getUsername());
		$this->assertEquals($this->collection[1]->getId(),2);
		$this->assertEquals($this->collection[1]->getUsername(),$this->another_user->getUsername());
}











}
?>
<!-- php vendor/phpunit/phpunit/phpunit --bootstrap config/bootstrap.php tools/unit_tests/classes/core/DinklyDataCollection.php -->