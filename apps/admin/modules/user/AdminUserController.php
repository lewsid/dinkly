<?php
/**
 * AdminUserController
 *
 * @package    Dinkly
 * @subpackage AppsDinklyUserController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class AdminUserController extends AdminController
{
	protected $user;

	protected $errors = array();

	public function __construct()
	{
		parent::__construct();

		if(!DinklyUser::isLoggedIn() || !DinklyUser::isMemberOf('admin'))
		{
			$this->loadModule('admin', 'home', 'default', true);
			return false;
		}
	}

	public function loadDefault($parameters)
	{
		$this->users = DinklyUserCollection::getAll();
		return true;
	}

	public function loadDelete($parameters)
	{
		$user = new DinklyUser();

		if(isset($parameters['id']))
		{
			$user->init($parameters['id']);

			if(!$user->isNew())
			{
				$user->delete();

				DinklyFlash::set('warning_user_message', 'User successfully deleted');

				return $this->loadModule('admin', 'user', 'default', true);
			}
		}

		return false;
	}

	public function loadNew($parameters)
	{
		$this->user = new DinklyUser($this->db);

		if(isset($_POST['username']))
		{
			$this->validateUserPost($_POST);

			//If we have no errors, save the user and redirect to detail
			if($this->errors == array())
			{
				$this->user->save();

				DinklyFlash::set('good_user_message', 'User successfully created');

				return $this->loadModule('admin', 'user', 'detail', true, true, array('id' => $this->user->getId()));
			}
		}

		return true;
	}

	public function validateUserPost($post_array)
	{
		//If the passed username doesn't match the existing one, update
		if($post_array['username'] != $this->user->getUsername())
		{
			//Check the username/email for uniqueness
			if(!DinklyUserCollection::isUniqueUsername($post_array['username']))
			{
				$this->errors[] = "Email address already in use, please try another.";
			}

			//Make sure it's also a valid email address
			if(!filter_var($post_array['username'], FILTER_VALIDATE_EMAIL))
			{
			    $this->errors[] = "Invalid username. It must be a valid email address.";
			}

			$this->user->setUsername($post_array['username']);

			//If we're editing the current user, we should update the session'd username
			if($this->user->getId() == DinklyUser::getAuthSessionValue('logged_id'))
			{
				DinklyUser::setAuthSessionValue('logged_username', $this->user->getUsername());
			}
		}

		//If the password isn't blank
		if($post_array['password'] != "" && $post_array['confirm-password'] != "")
		{
			$has_error = false;

			//Make sure both match
			if($post_array['password'] != $post_array['confirm-password'])
			{
				$has_error = true;
				$this->errors[] = "Passwords do not match.";
			}

			//Check for length
			if(strlen($post_array['password']) < 8)
			{
				$has_error = true;
				$this->errors[] = "Password must be at least 8 characters in length.";
			}

			//If the password is valid, update
			if(!$has_error) { $this->user->setPassword($post_array['password']); }
		}
		else if($_POST['user-id'] == "" && $_POST['password'] == "")
		{
			$this->errors[] = "Password is a required field";
		}

		//If the first name isn't empty and doesn't match the existing one, update
		if($post_array['first-name'] != "" && $post_array['first-name'] != $this->user->getFirstName())
		{
			$this->user->setFirstName($post_array['first-name']);
		}

		//If the last name isn't empty and doesn't match the exiting one, update
		if($post_array['last-name'] != "" && $post_array['last-name'] != $this->user->getLastName())
		{
			$this->user->setLastName($post_array['last-name']);
		}

		//If the title isn't empty and does't match the existing one, update
		if($post_array['title'] != "" && $post_array['title'] != $this->user->getTitle())
		{
			$this->user->setTitle($post_array['title']);
		}
	}

	public function loadEdit($parameters)
	{
		$this->user = new DinklyUser();

		if(isset($parameters['id']))
		{
			$this->user->init($parameters['id']);

			if(isset($_POST['username']))
			{
				$this->validateUserPost($_POST);

				if($_POST['source'] == 'profile')
				{
					if($_POST['date-format'] == 'MM/DD/YY')
					{
						$this->user->setDateFormat('m/d/y');
					}
					else if($_POST['date-format'] == 'YYYY-MM-DD')
					{
						$this->user->setDateFormat('Y-m-d');
					}
				}

				//If we have no errors, save the user and redirect to detail
				if($this->errors == array())
				{
					$this->user->save();

					//If the source is 'profile' this was in a modal, and sent via ajax
					if($_POST['source'] == 'profile')
					{
						echo 'success';
						die();
					}
					else
					{
						DinklyFlash::set('good_user_message', 'User successfully updated');

						return $this->loadModule('admin', 'user', 'detail', true, true, array('id' => $this->user->getId()));
					}
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
				$user = new DinklyUser($this->db);
				$user->init($parameters['id']);
				$user->addToGroups($_POST['group']);

				DinklyFlash::set('good_user_message', 'User groups updated');

				return $this->loadModule('admin', 'user', 'detail', true, true, array('id' => $user->getId()));
			}
		}

		return false;
	}

	public function loadRemoveGroup($parameters)
	{
		if(isset($parameters['id']) && isset($parameters['group_id']))
		{
			$user = new DinklyUser($this->db);
			$user->init($parameters['id']);

			$user->removeFromGroup($parameters['group_id']);

			DinklyFlash::set('good_user_message', 'User removed from group');

			return $this->loadModule('admin', 'user', 'detail', true, true, array('id' => $user->getId()));
		}

		return false;
	}

	public function loadDetail($parameters)
	{
		$this->user = null;
		$this->available_groups = array();

		if(isset($parameters['id']))
		{
			$this->user = new DinklyUser($this->db);
			$this->user->init($parameters['id']);

			//Build a collection of groups that the user in not currently in
			$temp_groups = DinklyGroupCollection::getAll();

			if($temp_groups != array())
			{
				foreach($temp_groups as $temp_group)
				{
					$has_group = false;
					if($this->user->getGroups() != array())
					{
						foreach($this->user->getGroups() as $g)
						{
							if($temp_group->getId() == $g->getId())
							{
								$has_group = true;
							}
						}
					}

					if(!$has_group) { $this->available_groups[] = $temp_group; }
				}
			}

			return true;
		}

		return false;
	}
}