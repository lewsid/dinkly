<?php

class AdminUser extends BaseAdminUser
{
	public function setPassword($password)
	{
		$this->Password = crypt($password);
		$this->regDirty['password'] = true;
	}

	public static function isLoggedIn()
	{
		if(isset($_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_in'])) { return $_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_in']; }
		return false;
	}

	public static function setLoggedIn($val, $username)
	{
		$_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_in'] = $val;
		$_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_username'] = $username;
		$_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_id'] = $username;
	}

	public static function getLoggedUsername()
	{
		if(isset($_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_username'])) { return $_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_username']; }
		return false;
	}

	public static function logout()
	{	
		$_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_in'] = null;
		$_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_username'] = null;
		$_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_id'] = null;
	}

	/* Returns 0 for complete fail, 1 for success and 2 if the account is locked */
	/* Locks account after 5 failed attempts */
	public static function authenticate($username, $input_password)
	{
		$dbo = self::fetchDB();

		$sql = "select * from admin_user where username=".$dbo->quote($username);
		$result = $dbo->query($sql)->fetchAll();

		//We found a match for the username      
		if($result != array())
		{
			$user = new AdminUser();
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
