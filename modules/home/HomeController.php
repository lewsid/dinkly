<?php

class HomeController extends Dinkly 
{
	public function __construct
	{
	}

	public function loadDefault()
	{
		return true;
	}

	public function loadLogin()
	{
		if(isset($_POST['username']) && isset($_POST['password']))
		{
			if(!AdminUser::authenticate($_POST['username'], $_POST['password']))
			{
				$_SESSION['dinkly']['badlogin'] = true;
			}
			else if(isset($_SESSION['dinkly']['badlogin'])) { unset($_SESSION['dinkly']['badlogin']); }
		}

		$this->loadModule('home');

		return false;
	}

	public function loadLogout()
	{
		AdminUser::logout();

		$this->loadModule('home');

		return false;
	}

	public function loadUserList()
	{
		if(!AdminUser::isLoggedIn())
		{
			$this->loadModule('home');
			return false;
		}

		$this->users = AdminUserBundle::getAll();

		return true;
	}
}
