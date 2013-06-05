<?php 

use Symfony\Component\Yaml\Yaml;

class DinklyDataConfig
{
	//Default to first connection in db.yml
	public static function loadDBCreds()
	{
		if(!isset($_SESSION['dinkly']['db_creds']))
		{
			$db_creds = Yaml::parse($_SERVER['APPLICATION_ROOT'] . "config/db.yml");  
			$_SESSION['dinkly']['db_creds'] = $db_creds;
			$first_connection = key($db_creds);
			self::setActiveConnection($first_connection);
		}
	}

	public static function hasConnection($connection_name)
	{
		if(!isset($_SESSION['dinkly']['db_creds']))
		{
			DinklyDataConfig::loadDBCreds();
		}
		return array_key_exists($connection_name, $_SESSION['dinkly']['db_creds']);
	}

	public static function setActiveConnection($connection)
	{ 
		//If connection is array, we can override the loaded configurations
		if(is_array($connection))
		{
			$active_db = $connection['DB_NAME'];

			$_SESSION['dinkly']['db_creds'][$active_db] = $connection;

			$_SESSION['dinkly']['db_creds']['active_db'] = $active_db;

			return true;
		}
		else
		{
			if(self::hasConnection($connection))
			{
				$_SESSION['dinkly']['db_creds']['active_db'] = $connection;
				return true;
			}
		}
		
		return false;
	}

	public static function getDBCreds($connection_name = false)
	{
		if(!isset($_SESSION['dinkly']['db_creds']))
		{
			DinklyDataConfig::loadDBCreds();
		}

		$active_db = $_SESSION['dinkly']['db_creds']['active_db'];

		if(!$connection_name)
		{
			return $_SESSION['dinkly']['db_creds'][$active_db];  
		}
		else
		{
			return $_SESSION['dinkly']['db_creds'][$connection_name];
		}
	}
}

