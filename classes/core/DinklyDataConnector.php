<?php

class DinklyDataConnector
{
	//$error_mode 0 = no errors, 1 = show warnings, 2 = throw exceptions
	public static function fetchDB($error_mode = 1)
	{
		$pdo_err_mode = null;
		if($error_mode == 0) $pdo_err_mode = PDO::ERRMODE_SILENT;
		else if($error_mode == 1) $pdo_err_mode = PDO::ERRMODE_WARNING;
		else if($error_mode == 2) $pdo_err_mode = PDO::ERRMODE_EXCEPTION;

		$creds = DinklyDataConfig::getDBCreds();
		
		$db = new PDO(
				"mysql:host=".$creds['DB_HOST'].";dbname=".$creds['DB_NAME'],
				$creds['DB_USER'],
				$creds['DB_PASS']
		);

		$db->setAttribute(PDO::ATTR_ERRMODE, $pdo_err_mode);

		return $db;
	}

	public static function testDB($error_mode = 1)
	{
		try
		{
			self::fetchDB();
		}
		catch (PDOException $e)
		{
    		echo "Connection failed: " . $e->getMessage() . "\n";
    		return false;
		}

		return true;
	}
}