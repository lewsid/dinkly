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

				//If we have no errors, save the user and redirect to detail
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
