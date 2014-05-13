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
	public function __construct()
	{
		if(!DinklyUser::isLoggedIn() || !DinklyUser::isMemberOf('admin'))
		{
			$this->loadModule('admin', 'home', 'default', true);
			return false;
		}
	}

	public function loadDefault()
	{
		$this->users = DinklyUserCollection::getAll();
		return true;
	}

	public function loadEdit($parameters)
	{
		$this->user = null;

		if(isset($parameters['id']))
		{
			$this->user = new DinklyUser();
			$this->user->init($parameters['id']);

			return true;
		}

		return false;
	}

	public function loadDetail($parameters)
	{
		$this->user = null;
		$this->saved = null;

		if(isset($parameters['id']))
		{
			$this->user = new DinklyUser();
			$this->user->init($parameters['id']);

			return true;
		}

		return false;
	}
}