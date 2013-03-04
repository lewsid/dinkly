<?php

class LoginController extends Dinkly 
{
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

	public function loadLogout()
	{
		AdminUser::logout();

		$this->loadModule('admin', 'home', 'default', true);

		return false;
	}
}
