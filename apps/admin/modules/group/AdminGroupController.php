<?php
/**
 * AdminGroupController
 *
 * @package    Dinkly
 * @subpackage AppsAdminGroupController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

class AdminGroupController extends AdminController
{
	protected $group;

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

	public function loadDeletePermission($parameters)
	{
		if(isset($_POST['permission_id']))
		{
			$permission = new DinklyPermission();
			$permission->init($_POST['permission_id']);

			if(!$permission->isNew())
			{
				$permission->delete();
				echo 'success';
			}
		}

		return false;
	}

	public function loadCreatePermission($parameters)
	{
		$errors = array();

		if(isset($_POST['permission_name']))
		{
			//Check for length
			if($_POST['permission_name'] == "")
			{
				$errors[] = "Name cannot be blank.";
			}

			//Check for whitespace in the abbreviation
			if(stristr($_POST['permission_name'], ' '))
			{
				$errors[] = "Abbreviation cannot contain whitespace.";
			}

			//Check the name for uniqueness
			if(!DinklyPermissionCollection::isUniqueName($_POST['permission_name']))
			{
				$errors[] = "Name already in use, please try another.";
			}

			//Make sure that the abbreviation is also alphanumeric, without funky symbols
			$valid_symbols = array('-', '_'); 
			if(!ctype_alnum(str_replace($valid_symbols, '', $_POST['permission_name'])))
			{
				$errors[] = "Name must be alphanumeric. Underscores and dashes are allowed.";
			}

			if($errors != array())
			{
				echo implode('<br>', $errors);
			}
			else
			{
				$permission = new DinklyPermission();
				$permission->setName($_POST['permission_name']);
				$permission->setDescription($_POST['permission_description']);
				$permission->save();

				echo 'success';
			}
		}

		return false;
	}

	public function loadDefault($parameters)
	{
		$this->groups = DinklyGroupCollection::getAll();
		$this->all_permissions = DinklyPermissionCollection::getAll();

		return true;
	}

	public function loadPermissionTable($parameters)
	{
		$this->all_permissions = DinklyPermissionCollection::getAll();

		return false;
	}

	public function loadDetail($parameters)
	{
		$this->group = null;
		$this->available_permissions = array();

		if(isset($parameters['id']))
		{
			$this->group = new DinklyGroup();
			$this->group->init($parameters['id']);

			//Build a collection of permissions that the group does not currently have
			$temp_permissions = DinklyPermissionCollection::getAll();

			if($temp_permissions != array())
			{
				foreach($temp_permissions as $temp_perm)
				{
					$has_perm = false;
					if($this->group->getPermissions() != array())
					{
						foreach($this->group->getPermissions() as $p)
						{
							if($temp_perm->getId() == $p->getId())
							{
								$has_perm = true;
							}
						}
					}

					if(!$has_perm) { $this->available_permissions[] = $temp_perm; }
				}
			}

			return true;
		}
	}

	public function validateGroupPost($post_array)
	{
		if(isset($post_array['name']))
		{
			//Just in case the js validation didn't already catch it
			if($post_array['name'] == "")
			{
				$this->errors[] = "Name cannot be blank.";
			}

			//Check the name for uniqueness
			if(!DinklyGroupCollection::isUniqueName($post_array['name']) && $post_array['name'] != $this->group->getName())
			{
				$this->errors[] = "Name already in use, please try another.";
			}

			//Check the abbreviation for uniqueness
			if(!DinklyGroupCollection::isUniqueAbbreviation($post_array['abbreviation']) 
				&& $post_array['abbreviation'] != $this->group->getAbbreviation())
			{
				$this->errors[] = "Abbreviation already in use, please try another.";
			}

			//Check for whitespace in the abbreviation
			if(stristr($post_array['abbreviation'], ' '))
			{
				$this->errors[] = "Abbreviation cannot contain whitespace.";
			}

			//Make sure that the abbreviation is also alphanumeric, without funky symbols
			$valid_symbols = array('-', '_'); 
			if(!ctype_alnum(str_replace($valid_symbols, '', $post_array['abbreviation'])))
			{
				$this->errors[] = "Abbreviation must be alphanumeric. Underscores and dashes are allowed.";
			}

			//Duh
			if($post_array['abbreviation'] == "")
			{
				$this->errors[] = "Abbreviation cannot be blank.";
			}

			//Update group (don't worry, we don't save unless everything is valid)
			$this->group->setName($post_array['name']);
			$this->group->setAbbreviation($post_array['abbreviation']);
			$this->group->setDescription($post_array['description']);
		}
	}

	public function loadDelete($parameters)
	{
		$group = new DinklyGroup();

		if(isset($parameters['id']))
		{
			$group->init($parameters['id']);

			if(!$group->isNew())
			{
				$group->delete();

				DinklyFlash::set('warning_group_message', 'Group successfully deleted');

				return $this->loadModule('admin', 'group', 'default', true);
			}
		}

		return false;
	}

	public function loadNew($parameters)
	{
		$this->group = new DinklyGroup();

		if(isset($_POST['name']))
		{
			$this->validateGroupPost($_POST);

			//If we have no errors, save the user and redirect to detail
			if($this->errors == array())
			{
				$this->group->save();

				DinklyFlash::set('good_group_message', 'Group successfully created');

				return $this->loadModule('admin', 'group', 'detail', true, true, array('id' => $this->group->getId()));
			}
		}

		return true;
	}

	public function loadRemovePermission($parameters)
	{
		if(isset($parameters['id']) && isset($parameters['permission_id']))
		{
			$group = new DinklyGroup();
			$group->init($parameters['id']);

			$group->removePermission($parameters['permission_id']);

			DinklyFlash::set('good_group_message', 'Permission removed');

			return $this->loadModule('admin', 'group', 'detail', true, true, array('id' => $group->getId()));
		}

		return false;
	}

	public function loadAddPermission($parameters)
	{
		if(isset($parameters['id']))
		{
			if(isset($_POST['permission']))
			{
				$group = new DinklyGroup();
				$group->init($parameters['id']);
				$group->addPermissions($_POST['permission']);

				DinklyFlash::set('good_group_message', 'Permissions updated');

				return $this->loadModule('admin', 'group', 'detail', true, true, array('id' => $group->getId()));
			}
		}

		return false;
	}

	public function loadEdit($parameters)
	{
		$this->group = new DinklyGroup();

		if(isset($parameters['id']))
		{
			$this->group->init($parameters['id']);

			if(isset($_POST['name']))
			{
				$this->validateGroupPost($_POST);

				//If we have no errors, save the group and redirect to detail
				if($this->errors == array())
				{
					$this->group->save();

					DinklyFlash::set('good_group_message', 'Group successfully updated');

					return $this->loadModule('admin', 'group', 'detail', true, true, array('id' => $this->group->getId()));
				}
			}

			return true;
		}

		return false;
	}
}
