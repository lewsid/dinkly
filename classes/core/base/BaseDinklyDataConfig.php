<?php 
/**
 * BaseDinklyDataConfig
 *
 * 
 *
 * @package    Dinkly
 * @subpackage CoreClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
use Symfony\Component\Yaml\Yaml;

class BaseDinklyDataConfig
{

	/**
	 * Fetch DB credentials from config/db.yml file, Set DB sessions vars, Set first DB connection
	 * Uses first connection in db.yml
	 * Default to first connection found
	 *
	 * @return bool true on successful DB connection or false on failure
	 */
	public static function loadDBCreds()
	{
		if(!isset($_SESSION['dinkly']['db_creds']))
		{
			$config = Dinkly::getConfig();
			$db_creds = $config['databases'];
			$_SESSION['dinkly']['db_creds'] = $db_creds;
			$first_connection = key($db_creds);
			self::setActiveConnection($first_connection);
		}
	}

	/**
	 * Checks for active DB connection and sets one if none found
	 * @param string $connection_name String name of DB connection
	 * 
	 * @return bool true if DB connection exists or successfully connected 
	 */
	public static function hasConnection($connection_name)
	{
		if(!isset($_SESSION['dinkly']['db_creds']))
		{
			DinklyDataConfig::loadDBCreds();
		}
		return array_key_exists($connection_name, $_SESSION['dinkly']['db_creds']);
	}

	/**
	 * Sets active DB connection and sets session variables accordingly
	 * Can overide active connection if new DB credentials passed in
	 * @param mixed $connection Array containing DB credentials for override
	 * $connection String to verify already actively connected to DB
	 * 
	 * @return bool true if DB connection overrided or already existed, false if not connected
	 */
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
	
	/**
	 * Fetches credentials of active DB connection
	 * 
	 * @param mixed  $connection_name bool defaulted false |
	 * $connection_name string to get credentials of specific connection
	 *
	 * @return array containing credentials of either active DB or chosen DB
	 */
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

