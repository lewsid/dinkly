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
}
?>