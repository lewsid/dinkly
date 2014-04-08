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
		if(isset($_POST['username']) && isset($_POST['password']))
		{
			if(!AdminUser::authenticate($_POST['username'], $_POST['password']))
			{
				$_SESSION['dinkly']['badlogin'] = true;
			}
			else if(isset($_SESSION['dinkly']['badlogin'])) { unset($_SESSION['dinkly']['badlogin']); }
		}

		$this->loadModule('admin', 'home', 'default', true);

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
