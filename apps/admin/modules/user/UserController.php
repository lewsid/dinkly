<?php
/**
 * UserController
 *
 * 
 *
 * @package    Dinkly
 * @subpackage AppsAdminUserUserController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class UserController extends AdminController
{
	/**
	 * Default Constructor loads module if admin logged in
	 * 
	 * @return bool: always returns false on successful construction of admin module
	 * 
	 */
	public function __construct()
	{
		if(!AdminUser::isLoggedIn())
		{
			$this->loadModule('admin', 'home', 'default', true);
			return false;
		}
	}
	/**
	 * Loads default module in admin partition of site if admin is logged in
	 * 
	 * @return bool: always returns false on successful construction of user default module
	 * 
	 */
	public function loadDefault()
	{
		$this->loadModule('admin', 'user', 'user_list', true);
		return false;
	}
	/**
	 * Loads User List module in admin partition and get user data from database
	 * 
	 * @return bool: always returns true on successful load of user data
	 * 
	 */
	public function loadUserList()
	{
		$this->users = AdminUserCollection::getAll();
		return true;
	}
}