<?php

class AuthUserBundle extends DBObjectBundle
{
	public static function getAll()
	{
		$db = new DBHelper(DBConfig::getDBCreds());
		$peer_object = new AuthUser;
		if($db->Select($peer_object->getSelectQuery(), true))
		{
			$peer_object->setDB($db);
			return self::handleResults($peer_object);
		}
	}
}