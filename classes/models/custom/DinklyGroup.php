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

	public function getPermissions()
	{
		if($this->permissions == array())
		{
			$this->permissions = DinklyGroupPermissionCollection::getPermissionsByGroup($this->getId());
		}

		return $this->permissions;
	}
}

