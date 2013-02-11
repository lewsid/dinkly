<?php

class HomeController extends Dinkly 
{
	public function loadDefault()
	{
		return true;
	}

	public function loadLogin()
	{
		if(isset($_POST['username']) && isset($_POST['password']))
		{
			AuthUser::authenticate($_POST['username'], $_POST['password']);
		}

		header("Location: /");

		return false;
	}

	public function loadLogout()
	{
		AuthUser::logout();

		return $this->loadModule('home');
	}

	public function loadUserList()
	{
		if(!AuthUser::isLoggedIn()) return $this->loadModule('home');

		$this->users = AuthUserBundle::getAll();

		return true;
	}
}
