<?php
/**
 * DinklyGroupPermissionCollection
 *
 * *
 * @package    Dinkly
 * @subpackage ModelsCustomClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class DinklyGroupPermissionCollection extends DinklyDataCollection
{
	public static function getPermissionsByGroup($db = null, $group_id)
	{
		$peer_object = new DinklyGroupPermission();
		if($db == null) { $db = self::fetchDB(); }

		$query = $peer_object->getSelectQuery() . " where dinkly_group_id = " . $db->quote($group_id);

		$group_perm_joins = self::getCollection($peer_object, $query, $db);

		if($group_perm_joins != array())
		{
			$perm_ids = array();
			foreach($group_perm_joins as $perm_join)
			{
				$perm_ids[] = $perm_join->getDinklyPermissionId();
			}

			$perms = DinklyPermissionCollection::getByArrayOfIds($perm_ids, $db);

			if($perms != array())
			{
				return $perms;
			}
		}
		
		return false;
	}
}

