<?php
/**
 * DinklyDataConnector
 *
 * 
 *
 * @package    Dinkly
 * @subpackage CoreClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class DinklyDataConnector extends BaseDinklyDataConnector
{
	//Put your overrides here...
	public static function fetchDB($error_mode = 1)
	{
		$pdo_err_mode = null;
		if($error_mode == 0) $pdo_err_mode = PDO::ERRMODE_SILENT;
		else if($error_mode == 1) $pdo_err_mode = PDO::ERRMODE_WARNING;
		else if($error_mode == 2) $pdo_err_mode = PDO::ERRMODE_EXCEPTION;

		$creds = DinklyDataConfig::getDBCreds();
		
		$db = new PDO(
				"dblib:host=".$creds['host'].";dbname=".$creds['name'],
				$creds['user'],
				$creds['pass']
		);

		$db->setAttribute(PDO::ATTR_ERRMODE, $pdo_err_mode);

		return $db;
	}
}