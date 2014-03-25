<?php
class DinklyDataCollectionTest extends PHPUnit_Framework_TestCase
{

	public function testGetAll()
	{
		//test that collection of admin users matches that of custom built array
		$this->admin_users= AdminUserCollection::getAll();
		$this->user = new AdminUser();
		$this->user->init(1);
		$test_collection[]=$this->user;
		$this->assertEquals($this->admin_users,$test_collection);
		$this->assertEquals($this->admin_users[0],$test_collection[0]);
		$this->assertEquals($this->admin_users[0]->getFirstName(),$test_collection[0]->getFirstName());
	}












}
?>
<!-- php vendor/phpunit/phpunit/phpunit --bootstrap config/bootstrap.php tools/unit_tests/classes/core/DinklyDataCollection.php -->