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
			$this->permissions = DinklyGroupPermissionCollection::getPermissionsByGroup($this->getId());
		}

		return $this->permissions;
	}

	public function getMembers()
	{
		if($this->members == array())
		{
			$this->members = DinklyUserGroupCollection::getUsersByGroup($this->getId());
		}

		return $this->members;
	}

	public function getMemberCount()
	{
		return sizeof($this->getMembers());
	}
}

