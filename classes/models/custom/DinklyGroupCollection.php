<?php
/**
 * DinklyGroupCollection
 *
 * *
 * @package    Dinkly
 * @subpackage ModelsCustomClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class DinklyGroupCollection extends DinklyDataCollection
{
	public static function getByArrayOfIds($group_ids, $db = null)
	{
		$peer_object = new DinklyGroup();

		if($db == null) { $db = self::fetchDB(); }

		$clean_ids = array();
		if(!is_array($group_ids)) { return false; }

		foreach($group_ids as $id)
		{
			if(is_numeric($id)) { $clean_ids[] = $id; }
		}
		
		$query = $peer_object->getSelectQuery() . " where id in (" . implode(',', $clean_ids) . ")";

		return self::getCollection($peer_object, $query, $db);
	}

	public static function getAll($db = null)
	{
		$peer_object = new DinklyGroup();

		if($db == null) { $db = self::fetchDB(); }
		
		$query = $peer_object->getSelectQuery() . " order by name";

		return self::getCollection($peer_object, $query, $db);
	}

	public static function isUniqueName($name, $db = null)
	{
		$user = new DinklyGroup();

		if($db == null) { $db = self::fetchDB(); }
		
		$query = $user->getSelectQuery() . " where name=" . $db->quote($name);

		$results = $db->query($query)->fetchAll();

		if($results != array() && $results != NULL)
		{
			return false;
		}
		else { return true; }
	}

	public static function isUniqueAbbreviation($abbr, $db = null)
	{
		$user = new DinklyGroup();

		if($db == null) { $db = self::fetchDB(); }
		
		$query = $user->getSelectQuery() . " where abbreviation=" . $db->quote($abbr);

		$results = $db->query($query)->fetchAll();

		if($results != array() && $results != NULL)
		{
			return false;
		}
		else { return true; }
	}
}

