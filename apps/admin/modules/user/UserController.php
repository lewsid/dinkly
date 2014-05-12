<?php
/**
 * UserController
 *
 * 
 *
 * @package    Dinkly
 * @subpackage AppsDinklyUserController
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
		if(!DinklyUser::isLoggedIn() || !DinklyUser::isMemberOf('admin'))
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
		$this->users = DinklyUserCollection::getAll();
		return true;
	}
}