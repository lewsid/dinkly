<?php

class DinklyDataModelTest extends PHPUnit_Framework_TestCase
{
	protected $user;

	protected function setUp()
	{
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

	protected function tearDown()
	{
		unset($this->user);
	}
}
?>