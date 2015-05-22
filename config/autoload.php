<?php 
/**
 * autoload
 *
 * 
 *
 * @package    Dinkly
 * @subpackage CoreClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
//Define autoloader 
function dinkly_autoloader($class_name)
{
	$app_root = $_SERVER['APPLICATION_ROOT'];

	//strip class name from namespaces
	if(stristr($class_name,  '\\'))
	{
		$parts = explode('\\', $class_name);
		$class_name = $parts[sizeof($parts) - 1];
	}

	//load models
	$base_core_file  = $app_root . '/classes/core/base/' . $class_name . '.php';
	$custom_core_file  = $app_root . '/classes/core/custom/' . $class_name . '.php';
	$base_model_file = $app_root . '/classes/models/base/' . $class_name . '.php';
	$custom_model_file = $app_root . '/classes/models/custom/' . $class_name . '.php';
	
	//third-party exceptions
	$yaml_file        = $app_root . '/vendor/symfony/yaml/Symfony/Component/Yaml/' . $class_name . '.php';
	$exception_file   = $app_root . '/vendor/symfony/yaml/Symfony/Component/Yaml/Exception/' . $class_name . '.php';
	$custom_file      = $app_root . '/classes/thirdparty/' . $class_name . '/' . $class_name . '.php';

	//load plugins
	$plugin_dir = $app_root . '/plugins';

	if(is_dir($plugin_dir))
	{
		$contents = scandir($plugin_dir);

		foreach($contents as $file_or_dir)
		{
			if(is_dir($plugin_dir . '/' . $file_or_dir) && $file_or_dir != '..' && $file_or_dir != '.')
			{
				$class_dir = $plugin_dir . '/' . $file_or_dir . '/classes';

				if(is_dir($class_dir))
				{
					$base_plugin_class = $class_dir . '/models/base/' . $class_name . '.php';
					$custom_plugin_class = $class_dir . '/models/custom/' . $class_name . '.php';

					if(file_exists($base_plugin_class))
					{
						require_once $base_plugin_class; 
						return true; 
					}
					else if(file_exists($custom_plugin_class))
					{
						require_once $custom_plugin_class; 
						return true; 
					}
				}
			}
		}
	}
	
	if(file_exists($base_core_file))
	{
		require_once $base_core_file; 
		return true; 
	} 
	else if(file_exists($custom_core_file))
	{
		require_once $custom_core_file;
		return true;
	}
	else if(file_exists($base_model_file))
	{
		require_once $base_model_file;
		return true;
	}
	else if(file_exists($custom_model_file))
	{
		require_once $custom_model_file;
		return true;
	}
	else if(file_exists($yaml_file))
	{
		require_once $yaml_file;
		return true;
	}
	else if(file_exists($exception_file))
	{
		require_once $exception_file;
		return true;
	}
	else if(file_exists($custom_file))
	{
		require_once $custom_file;
		return true;
	}

	return false; 
}

spl_autoload_register('dinkly_autoloader');