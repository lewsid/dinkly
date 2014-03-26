<?php

class UserController extends AdminController
{
	public function __construct()
	{
		if(!AdminUser::isLoggedIn())
		{
			$this->loadModule('admin', 'home', 'default', true);
			return false;
		}
	}

	public function loadDefault()
	{
		$this->loadModule('admin', 'user', 'user_list', true);
		return false;
	}

	public function loadUserList()
	{
		$this->users = AdminUserCollection::getAll();
		return true;
	}
}