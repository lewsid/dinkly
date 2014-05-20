<?php
/**
 * GroupController
 *
 *
 * @package    Dinkly
 * @subpackage AppsAdminGroupController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

class GroupController extends AdminController
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

	public function loadDefault($parameters)
	{
		$this->groups = DinklyGroupCollection::getAll();
		return true;
	}

	public function loadDetail($parameters)
	{
		$this->group = null;
		$this->available_permissions = array();

		if(isset($parameters['id']))
		{
			$this->group = new DinklyGroup();
			$this->group->init($parameters['id']);

			//Build a collection of permissions that the group does not current have
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
				$this->user->save();

				DinklyFlash::set('good_group_message', 'Group successfully created');

				return $this->loadModule('admin', 'group', 'detail', true, true, array('id' => $this->group->getId()));
			}
		}

		return true;
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
