<?php

require_once('../../../../config/bootstrap.php');
require_once('PHPUnit/Autoload.php');

class ToArrayNoScrub extends PHPUnit_Framework_TestCase
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
	//casses we really care about
	public function testToArray()
	{
		$this->assertEmpty(array_diff_assoc($this->user->toArray(), $this->valid_array));
	}

	public function testToArrayScrubWithEmptyArray()
	{
		$this->assertEmpty(array_diff_assoc($this->user->toArray(array()), $this->valid_array));
	}

	public function testToArrayScrubOneValueAsString()
	{
		$test_array = $this->valid_array;
		unset($test_array['password']);
		$this->assertEmpty(array_diff_assoc($this->user->toArray('password'), $test_array));
	}

	public function testToArrayScrubOneValueInArray()
	{
		$test_array = $this->valid_array;
		unset($test_array['password']);
		$this->assertEmpty(array_diff_assoc($this->user->toArray(array('password')), $test_array));
	}

	public function testToArrayScrubTwoValues()
	{
		$test_array = $this->valid_array;
		unset($test_array['password']);
		unset($test_array['title']);
		$this->assertEmpty(array_diff_assoc($this->user->toArray(array('password', 'title')), $test_array));
	}

	public function testToArrayScrubAllValues()
	{
		$test_array = array();
		$scrub_array = array_keys($this->valid_array);
		$this->assertEmpty(array_diff_assoc($this->user->toArray($scrub_array), $test_array));
	}

	//outside cases
	public function testToArrayScrubNonExistantValue()
	{
		$this->assertEmpty(array_diff_assoc($this->user->toArray(array('hillbilly')), $this->valid_array));
	}

	public function testToArrayScrubNullValue()
	{
		$this->assertEmpty(array_diff_assoc($this->user->toArray(null), $this->valid_array));
	}

	public function testToArrayScrubTrueBoolean()
	{
		$this->assertEmpty(array_diff_assoc($this->user->toArray(true), $this->valid_array));
	}

	public function testToArrayScrubFalseBoolean()
	{
		$this->assertEmpty(array_diff_assoc($this->user->toArray(false), $this->valid_array));
	}

	protected function tearDown()
	{
		unset($this->user);
	}
}
?>