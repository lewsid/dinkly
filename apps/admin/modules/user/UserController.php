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
		parent::__construct();

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
		$this->user = new DinklyUser();
		$this->errors = array();

		if(isset($parameters['id']))
		{
			$this->user->init($parameters['id']);

			if(isset($_POST['username']))
			{
				//If the passed username doesn't match the existing one, update
				if($_POST['username'] != $this->user->getUsername())
				{
					$has_error = false;

					//Check the password for uniqueness
					if(!DinklyUserCollection::isUniqueUsername($_POST['username']))
					{
						$has_error = true;
						$this->errors[] = "Email address already in use, please try another.";
					}

					//Make sure it's also a valid email address
					if(!filter_var($_POST['username'], FILTER_VALIDATE_EMAIL))
					{
						$has_error = true;
					    $this->errors[] = "Invalid username. It must be a valid email address.";
					}

					//If its valid, update
					if(!$has_error)
					{
						$this->user->setUsername($_POST['username']);

						//If we're editing the current user, we should update the session'd username
						if($this->user->getId() == DinklyUser::getAuthSessionValue('logged_id'))
						{
							DinklyUser::setAuthSessionValue('logged_username', $this->user->getUsername());
						}
					}
				}

				//If the password isn't blank
				if($_POST['password'] != "" && $_POST['confirm-password'] != "")
				{
					$has_error = false;

					//Make sure both match
					if($_POST['password'] != $_POST['confirm-password'])
					{
						$has_error = true;
						$this->errors[] = "Passwords do not match.";
					}

					//Check for length
					if(strlen($_POST['password']) < 8)
					{
						$has_error = true;
						$this->errors[] = "Password must be at least 8 characters in length.";
					}

					//If the password is valid, update
					if(!$has_error) { $this->user->setPassword($_POST['password']); }
				}

				//If the first name isn't empty and doesn't match the existing one, update
				if($_POST['first-name'] != "" && $_POST['first-name'] != $this->user->getFirstName())
				{
					$this->user->setFirstName($_POST['first-name']);
				}

				//If the last name isn't empty and doesn't match the exiting one, update
				if($_POST['last-name'] != "" && $_POST['last-name'] != $this->user->getLastName())
				{
					$this->user->setLastName($_POST['last-name']);
				}

				//If the title isn't empty and does't match the existing one, update
				if($_POST['title'] != "" && $_POST['title'] != $this->user->getTitle())
				{
					$this->user->setTitle($_POST['first_name']);
				}

				//If we have no errors, save the user and redirect to detail
				if($this->errors == array())
				{
					$this->user->save();

					return $this->loadModule('admin', 'user', 'detail', true, true, array('id' => $this->user->getId(), 'saved' => 1));
				}
			}

			return true;
		}

		return false;
	}

	public function loadAddGroup($parameters)
	{
		if(isset($parameters['id']))
		{
			if(isset($_POST['group']))
			{
				$user = new DinklyUser();
				$user->init($parameters['id']);
				$user->addToGroups($_POST['group']);

				
			}
		}
	}

	public function loadDetail($parameters)
	{
		$this->user = null;
		$this->saved = false;
		$this->created = false;
		$this->available_groups = array();

		if(isset($parameters['id']))
		{
			if(isset($parameters['saved'])) { $this->saved = true; }
			if(isset($parameters['created'])) { $this->created = true; }

			$this->user = new DinklyUser();
			$this->user->init($parameters['id']);

			//Build a collection of groups that the user in not currently in
			$temp_groups = DinklyGroupCollection::getAll();

			foreach($temp_groups as $temp_group)
			{
				$has_group = false;
				foreach($this->user->getGroups() as $g)
				{
					if($temp_group->getId() == $g->getId())
					{
						$has_group = true;
					}
				}

				if(!$has_group) { $this->available_groups[] = $temp_group; }
			}

			return true;
		}

		return false;
	}
}