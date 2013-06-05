<?php

class DinklyDataConnector
{
	public static function fetchDB()
	{
		$creds = DinklyDataConfig::getDBCreds();
		
		$db = new PDO(
				"mysql:host=".$creds['DB_HOST'].";dbname=".$creds['DB_NAME'],
				$creds['DB_USER'],
				$creds['DB_PASS']
		);

		return $db;
	}
}