<?php
/**
 * DinklyGroup
 *
 * *
 * @package    Dinkly
 * @subpackage ModelsCustomClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class DinklyGroup extends BaseDinklyGroup
{
	protected $permissions = array();

	protected $members = array();

	public function getPermissions()
	{
		if($this->permissions == array())
		{
			$this->permissions = DinklyGroupPermissionCollection::getPermissionsByGroup($this->db, $this->getId());
		}

		return $this->permissions;
	}

	public function getMembers()
	{
		if($this->members == array())
		{
			$this->members = DinklyUserGroupCollection::getUsersByGroup($this->db, $this->getId());
		}

		return $this->members;
	}

	public function getMemberCount()
	{
		return sizeof($this->getMembers());
	}

	public function removePermission($permission_id)
	{
		$permission = new DinklyPermission();
		$permission->init($permission_id);

		//If the permission isn't new, it exists, and we can continue
		if(!$permission->isNew())
		{
			$perm_join = new DinklyGroupPermission();
			$perm_join->initWithGroupAndPermission($this->getId(), $permission_id);

			if(!$perm_join->isNew())
			{
				$perm_join->delete();

				return true;
			}
		}

		return false;
	}

	public function addPermissions($permission_ids)
	{
		if($permission_ids != array())
		{
			foreach($permission_ids as $id)
			{
				$permission = new DinklyPermission();
				$permission->init($id);

				//If the permission isn't new, that means it exists, which is a good thing
				if(!$permission->isNew())
				{
					//Make sure this join record doesn't already exist first
					$permission_join = new DinklyGroupPermission();
					$permission_join->initWithGroupAndPermission($this->getId(), $id);
					
					if($permission_join->isNew())
					{
						$permission_join->setDinklyGroupId($this->getId());
						$permission_join->setDinklyPermissionId($id);
						$permission_join->save();
					}
				}
			}

			return true;
		}

		return false;
	}

	public function delete()
	{
		$members = $this->getMembers();
		if($members != array())
		{
			foreach($members as $user)
			{
				$user->removeFromGroup($this->getId());
			}
		}

		$permissions = $this->getPermissions();
		if($permissions != array())
		{
			foreach($permissions as $permission)
			{
				$this->removePermission($permission->getId());
			}
		}

		parent::delete();
	}
}

