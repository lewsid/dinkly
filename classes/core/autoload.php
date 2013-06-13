<?php 

//Define autoloader 
function dinkly_autoloader($class_name)
{
	//strip class name from namespaces
	if(stristr($class_name,  '\\'))
	{
		$parts = explode('\\', $class_name);
		$class_name = $parts[sizeof($parts) - 1];
	}

	//load models
	$core_file  = $_SERVER['APPLICATION_ROOT'] . '/classes/core/' . $class_name . '.php';
	$base_model_file = $_SERVER['APPLICATION_ROOT'] . '/classes/models/base/' . $class_name . '.php';
	$custom_model_file = $_SERVER['APPLICATION_ROOT'] . '/classes/models/custom/' . $class_name . '.php';
	
	//third-party exceptions
	$yaml_file        = $_SERVER['APPLICATION_ROOT'] . '/vendor/symfony/yaml/Symfony/Component/Yaml/' . $class_name . '.php';
	$exception_file   = $_SERVER['APPLICATION_ROOT'] . '/vendor/symfony/yaml/Symfony/Component/Yaml/Exception/' . $class_name . '.php';
	$custom_file      = $_SERVER['APPLICATION_ROOT'] . '/classes/thirdparty/' . $class_name . '.php';
	
	if(file_exists($core_file))
	{
		require_once $core_file; 
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