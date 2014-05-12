<?php
/**
 * DinklyPermissionCollection
 *
 * *
 * @package    Dinkly
 * @subpackage ModelsCustomClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class DinklyPermissionCollection extends DinklyDataCollection
{
	public static function getByArrayOfIds($perm_ids, $db = null)
	{
		$peer_object = new DinklyPermission();

		if($db == null) { $db = self::fetchDB(); }

		$clean_ids = array();
		if(!is_array($perm_ids)) { return false; }

		foreach($perm_ids as $id)
		{
			if(is_numeric($id)) { $clean_ids[] = $id; }
		}
		
		$query = $peer_object->getSelectQuery() . " where id in (" . implode(',', $clean_ids) . ")";

		return self::getCollection($peer_object, $query, $db);
	}
}

