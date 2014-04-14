<?php
/**
 * AdminUser
 *
 * 
 *
 * @package    Dinkly
 * @subpackage ModelsCustomClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class AdminUser extends BaseAdminUser
{
	/**
	 * Set password of admin user
	 *
	 * @param string $password: password you wish to set for admin user
	 *
	 * 
	 */
	public function setPassword($password)
	{
		$this->Password = crypt($password);
		$this->regDirty['password'] = true;
	}

	/**
	 * Check Dinkly session to see if admin user is logged in
	 *
	 * 
	 * @return bool true if logged in, false if not
	 */
	public static function isLoggedIn()
	{
		if(isset($_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_in'])) { return $_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_in']; }
		return false;
	}

	/**
	 * Retrieve all objects matching array of passed property/value pairs
	 *
	 * @param bool $val: set true to log in admin user
	 * @param string $username: username of admin user to set sessions
	 * 
	 */
	public static function setLoggedIn($val, $username)
	{
		$_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_in'] = $val;
		$_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_username'] = $username;
		$_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_id'] = $username;
	}

	/**
	 * Check Dinkly session to get username of admin that is logged in
	 *
	 * 
	 * @return mixed string | bool: string username if logged in, else false
	 */
	public static function getLoggedUsername()
	{
		if(isset($_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_username'])) { return $_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_username']; }
		return false;
	}

	/**
	 * Clear Dinkly session variables to log out admin user
	 *
	 * 
	 * 
	 */
	public static function logout()
	{	
		$_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_in'] = null;
		$_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_username'] = null;
		$_SESSION['dinkly'][Dinkly::getCurrentAppName()]['logged_id'] = null;
	}
	
	/**
	 * Verify with database that admin user credentials are correct and log in if so
	 * 
	 *
	 * @param string $username: input username of user attempting to log in
	 * @param string $input_password: input password of user attempting to log in
	 * 
	 * @return bool: true if correct credentials and logged on, false otherwise
	 */
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
