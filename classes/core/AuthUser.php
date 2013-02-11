<?php

class AuthUser extends DBObject
{
	public $registry = array(
	    'id'				=> 'Id',
	    'created_at'		=> 'CreatedAt',
	    'updated_at'		=> 'UpdatedAt',
	    'username'			=> 'Username',
	    'password'			=> 'Password',
	    'last_login_at'		=> 'LastLoginAt',
	    'login_count'		=> 'LoginCount'
  	);
  
	public $dbTable = 'dinkly_user';

	public function setPassword($password)
	{
		$this->Password = crypt($password);
		$this->regDirty['password'] = true;
	}

	public static function isLoggedIn()
	{
		if(isset($_SESSION['dinkly']['logged_in'])) { return $_SESSION['dinkly']['logged_in']; }
		return false;
	}

	public static function setLoggedIn($val, $username)
	{
		$_SESSION['dinkly']['logged_in'] = $val;
		$_SESSION['dinkly']['logged_username'] = $username;
		$_SESSION['dinkly']['logged_id'] = $username;
	}

	public static function getUsername()
	{
		if(isset($_SESSION['dinkly']['logged_username'])) { return $_SESSION['dinkly']['logged_username']; }
		return false;
	}

	public static function logout()
	{	
		$_SESSION['dinkly']['logged_in'] = null;
		$_SESSION['dinkly']['logged_username'] = null;
		$_SESSION['dinkly']['logged_id'] = null;
	}

	/* Returns 0 for complete fail, 1 for success and 2 if the account is locked */
	/* Locks account after 5 failed attempts */
	public static function authenticate($username, $input_password)
	{
		$dbo = new DBHelper(DBConfig::getDBCreds());		

		$dbo->Select("select * from dinkly_user where username='" . mysql_real_escape_string($username) . "'");

		//We found a match for the username      
		if($result = $dbo->getResult())
		{
			$user = new AuthUser();
			$user->init($result[0]['id']);
			$hashed_password = $result[0]['password'];

			if(crypt($input_password, $hashed_password) == $hashed_password)
			{
				$count = $user->getLoginCount() + 1;

				$user->setLastLoginAt(date('Y-m-d G:i:s'));
				$user->setLoginCount($count);
				$user->save();

				self::setLoggedIn(true, $result[0]['username']);

				return true;
			}
		}

		return false;
	}
}