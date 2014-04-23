<?php
/**
 * LoginController
 *
 * 
 *
 * @package    Dinkly
 * @subpackage AppsAdminLoginLoginController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class LoginController extends AdminController 
{
	/**
	 * Loads default admin login and runs authentication
	 * 
	 * @return bool: always returns false on successful construction of default admin module
	 * 
	 */
	public function loadDefault()
	{
		$error = null;

		if(isset($_POST['username']) && isset($_POST['password']))
		{
			if(!AdminUser::authenticate($_POST['username'], $_POST['password']))
			{
				$error = array('invalid_login' => 1);
			}
		}

		$this->loadModule('admin', 'home', 'default', true, true, $error);

		return false;
	}
	/**
	 * Logs out admin user and loads default module
	 * 
	 * @return bool: always returns false on successful log out
	 */
	public function loadLogout()
	{
		AdminUser::logout();

		$this->loadModule('admin', 'home', 'default', true);

		return false;
	}
}
