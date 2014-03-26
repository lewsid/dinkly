<?php

class AdminUserTest extends PHPUnit_Framework_TestCase
{
	protected $user;

	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");
		$this->user= new AdminUser();
		$this->user->init(1);
		$_SESSION['dinkly']['current_app_name']='admin';
	}
	public function testAuthenticate()
	{
		//make sure they are not logged in already
		$this->assertFalse($this->user->isLoggedIn());
		//test authentification with invalid credentials
		$bad_username="bad";
		$bad_password ="bad";
		$this->user->authenticate($bad_username,$bad_password);
		//test to make sure it did not log them in with bad creds
		$this->assertFalse($this->user->isLoggedIn());
		//test now with the right creds
		$username ="bfett";
		$password ="shut711up";
		$this->user->authenticate($username,$password);
		//test logged on and session set
		$this->assertTrue($this->user->isLoggedIn());
		$this->assertEquals($_SESSION['dinkly']['admin']['logged_in'],true);
		$this->assertEquals($_SESSION['dinkly']['admin']['logged_username'],$username);
		$this->assertEquals($_SESSION['dinkly']['admin']['logged_id'],$username);


	}
	public function testGetLoggedUsername()
	{
		$test_username="scott";
		$this->user->setLoggedIn(true,$test_username);
		$this->assertEquals($this->user->getLoggedUsername(),$test_username);
	}
	public function testSetLoggedIn()
	{
		//make sure they are not logged in already
		$this->assertFalse($this->user->isLoggedIn());
		$username= $this->user->getUsername();
		$val=true;
		//if not logged in we can do so and check to make sure session vars are correct
		$this->user->setLoggedIn($val,$username);
		$this->assertTrue($this->user->isLoggedIn());
		$this->assertEquals($_SESSION['dinkly']['admin']['logged_in'],true);
		$this->assertEquals($_SESSION['dinkly']['admin']['logged_username'],$username);
		$this->assertEquals($_SESSION['dinkly']['admin']['logged_id'],$username);

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
	public function testSetPassword(){
		//test old password
		$db_password=$this->user->getPassword();
		$input_password ="shut711up";
		$hashed_password = $db_password;
		$this->assertEquals(crypt($input_password, $hashed_password),$hashed_password);
		//change password and test new authentication
		$new_password ="password";
		$this->user->setPassword($new_password);
		$new_db_password=$this->user->getPassword();
		$this->assertEquals(crypt($new_password, $new_db_password),$new_db_password);


	}



}
?>