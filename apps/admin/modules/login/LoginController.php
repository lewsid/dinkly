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
	public function __construct()
	{
		parent::__construct();
	}

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
			if(!DinklyUser::authenticate($_POST['username'], $_POST['password']))
			{
				DinklyFlash::set('invalid_login', 'Invalid login');
			}
		}

		$this->loadModule('admin', 'home', 'default', true, true);

		return false;
	}
	/**
	 * Logs out admin user and loads default module
	 * 
	 * @return bool: always returns false on successful log out
	 */
	public function loadLogout()
	{
		DinklyUser::logout();

		$this->loadModule('admin', 'home', 'default', true);

		return false;
	}
}
