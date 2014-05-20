<?php
/**
 * DinklyGroupPermission
 *
 * *
 * @package    Dinkly
 * @subpackage ModelsCustomClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class DinklyGroupPermission extends BaseDinklyGroupPermission
{
	public function initWithGroupAndPermission($group_id, $permission_id)
	{
		if(!$this->db) { throw New Exception("Unable to perform init without a database object"); }

		$query = $this->getSelectQuery() . " where dinkly_permission_id=" . $this->db->quote($permission_id) 
			. " and dinkly_group_id=" . $this->db->quote($group_id);

		$result = $this->db->query($query)->fetchAll();
				
		if($result != array())
		{
			$this->hydrate($result, true);
			return true;
		}
		return false;

	}
}

