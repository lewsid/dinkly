<?php
/**
 * BaseDinklyFlash
 *
 *
 * @package    Dinkly
 * @subpackage CoreClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

class BaseDinklyFlash
{
	public static function exists($key)
	{
		if(isset($_SESSION['dinkly']['flash']))
		{
			if(isset($_SESSION['dinkly']['flash'][$key]))
			{
				return true;
			}
		}

		return false;
	}

	public static function set($key, $value)
	{
		if(!isset($_SESSION['dinkly']['flash']))
		{
			$_SESSION['dinkly']['flash'] = array();
		}

		$_SESSION['dinkly']['flash'][$key] = $value;
	}

	public static function get($key, $delete = true)
	{
		if(isset($_SESSION['dinkly']['flash']))
		{
			if(isset($_SESSION['dinkly']['flash'][$key]))
			{
				$value = $_SESSION['dinkly']['flash'][$key];

				if($delete) { unset($_SESSION['dinkly']['flash'][$key]); }

				return $value;
			}
		}
	}

	public static function getAll($delete = true)
	{
		if(isset($_SESSION['dinkly']['flash']))
		{
			$values = $_SESSION['dinkly']['flash'];

			if($delete) { $_SESSION['dinkly']['flash'] = array(); }
		
			return $values;
		}
	}

	public static function clear()
	{
		if(isset($_SESSION['dinkly']['flash']))
		{
			$_SESSION['dinkly']['flash'] = array();
		}
	}
}