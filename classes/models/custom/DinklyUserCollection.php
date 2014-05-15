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
		
		$query = $user->getSelectQuery() . " where username='" . $username . "'";

		$results = $db->query($query)->fetchAll();

		if($results != array() && $results != NULL)
		{
			return false;
		}
		else { return true; }
	}
}

