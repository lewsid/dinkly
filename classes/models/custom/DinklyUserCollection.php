<?php
/**
 * DinklyUserCollection
 *
 * *
 * @package    Dinkly
 * @subpackage ModelsCustomClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class DinklyUserCollection extends DinklyDataCollection
{
	public static function isUniqueUsername($username, $db = null)
	{
		$user = new DinklyUser();

		if($db == null) { $db = self::fetchDB(); }
		
		$query = $user->getSelectQuery() . " where username=" . $db->quote($username);

		$results = $db->query($query)->fetchAll();

		if($results != array() && $results != NULL)
		{
			return false;
		}
		else { return true; }
	}

	public static function getByArrayOfIds($user_ids, $db = null)
	{
		$peer_object = new DinklyUser();

		if($db == null) { $db = self::fetchDB(); }

		$clean_ids = array();
		if(!is_array($user_ids)) { return false; }

		foreach($user_ids as $id)
		{
			if(is_numeric($id)) { $clean_ids[] = $id; }
		}
		
		$query = $peer_object->getSelectQuery() . " where id in (" . implode(',', $clean_ids) . ")";

		return self::getCollection($peer_object, $query, $db);
	}
}

