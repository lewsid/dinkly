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

		header("Location: /");

		return false;
	}

	public function loadUserList()
	{
		if(!AuthUser::isLoggedIn())
		{
			header("Location: /");
			return false;
		}

		$this->users = AuthUserBundle::getAll();

		return true;
	}
}
