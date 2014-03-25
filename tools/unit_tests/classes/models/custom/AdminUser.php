<?php

class AdminUserTest extends PHPUnit_Framework_TestCase
{
	protected $user;

	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");
		$this->user= new AdminUser();
		$this->user->init(1);
		$_SESSION['dinkly']['current_app_name']="admin";
	}

	public function testSetLoggedIn()
	{
		//make sure they are not logged in already
		$this->assertFalse($this->user->isLoggedIn());
		$username= $this->user->getUsername();
		//if not logged in we can do so and check to make sure session vars are correct
		
	}
	public function testIsLoggedIn()
	{
		//check if logged in before setting session var
		$this->assertFalse($this->user->isLoggedIn());
		//now log the user in and recheck make sure asserts true
		$this->user->setLoggedIn(true,$this->user->getUsername());
		$this->assertTrue($this->user->isLoggedIn());
	}
	public function testLogout(){
		//check if logged in before setting session var
		$this->assertFalse($this->user->isLoggedIn());
		//now log the user in and recheck make sure asserts true
		$this->user->setLoggedIn(true,$this->user->getUsername());
		$this->assertTrue($this->user->isLoggedIn());
		//after logging session variable will be null and assert not logged in
		$this->user->logout();
		$this->assertFalse($this->user->isLoggedIn());
	}






}
?>