<?php
/**
 * AdminLoginController
 *
 * @package    Dinkly
 * @subpackage AppsAdminLoginLoginController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class AdminLoginController extends AdminController 
{
	public function __construct()
	{
		parent::__construct();
	}

	public function loadForgotPassword()
	{
		if(isset($_POST['email']))
		{
			if($_POST['email'] != '')
			{
				$user = new DinklyUser($this->db);
				$user->initWithEmail($_POST['email']);

				if($user->getId())
				{
					$token = bin2hex(openssl_random_pseudo_bytes(16));
					$user->setAutoLoginHash($token);
					$user->setAutoLoginExpire(date('Y-m-d H:i:s', strtotime("+30 minutes")));
					$user->save();
					$user->sendSetPasswordEmail();
					DinklyFlash::set('request_success', 'You have been sent a link to change your password that will expire in 30 minutes');
				}
				else
				{
					DinklyFlash::set('request_error', 'The provided email did not belong to an account');
				}
			}
			else
			{
				DinklyFlash::set('request_error', 'Please provide an email address');
			}
		}
		return true;
	}

	public function loadResetPassword($parameters)
	{
		if(!isset($parameters['k']))
		{
			return $this->loadModule('admin', 'home', 'default', true, true);
		}

		$user = new DinklyUser($this->db);
		$user->initWith(array('auto_login_hash' => $parameters['k']));
		if(!$user->getId())
		{
			return $this->loadModule('admin', 'login', 'forgot_password', true);
		}

		if(!strtotime($user->getAutoLoginExpire()) > time())
		{
			DinklyFlash::set('reset_error', 'Sorry, the link has expired.');
			
			return $this->loadModule('admin', 'login', 'forgot_password', true);
		}

		if(isset($_POST['password']) && isset($_POST['password-confirm']))
		{
			if($_POST['password'] != $_POST['password-confirm'])
			{
				DinklyFlash::set('reset_error', 'Passwords did not match');
			}
			elseif(strlen($_POST['password']) < 8)
			{
				DinklyFlash::set('reset_error', 'Password must be at least 8 characters long');
			}
			else
			{
				$user->setPassword($_POST['password']);
				$user->setAutoLoginHash('');
				$user->setAutoLoginExpire('');
				$user->save();

				DinklyFlash::set('reset_success', ' Your password was successfully set. Please login using your new password.');

				return $this->loadModule('admin', 'login', 'default', true);
			}
		}

		return true;
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
			else
			{
				$this->loadModule('admin', 'home', 'default', true);
			}
		}

		return true;
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
