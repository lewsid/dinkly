<?php
/**
 * DinklyUserGroup
 *
 * *
 * @package    Dinkly
 * @subpackage ModelsCustomClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class DinklyUserGroup extends BaseDinklyUserGroup
{
	public function initWithUserAndGroup($user_id, $group_id)
	{
		if(!$this->db) { throw New Exception("Unable to perform init without a database object"); }

		$query = $this->getSelectQuery() . " where dinkly_user_id=" . $this->db->quote($user_id) 
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

