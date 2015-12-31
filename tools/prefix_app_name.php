<?php

/**
 * Used for upgrade from v2.04 or lower to prefix the app name to the class name
 * and file name for controllers
 */

require_once('config/bootstrap.php');

$apps = array_filter(glob($_SERVER['APPLICATION_ROOT'] . 'apps/*'), 'is_dir');

foreach($apps as $app)
{
	$app_path_parts = explode('/', $app);
	$app_name = end($app_path_parts);
	$source_prefix = Dinkly::convertToCamelCase($app_name, true);
	echo "\nUpdateing " . $app_name . " app\n";
	foreach (
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($app, \RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST) as $item
		) 
	{
		if (!$item->isDir())
		{
			$name_parts = explode('/', $iterator->getSubPathName());
			$file_name = end($name_parts);

			if(
				strstr($file_name, 'Controller') 
				&& !strstr($file_name, $source_prefix."Controller") 
				&& !strstr($file_name, $source_prefix)
				)
			{
				$controller_name = str_replace('.php', '', $file_name);
				$new_file_name = $source_prefix.$file_name;
				$new_controller_name = $source_prefix.$controller_name;
				$source = $app . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
				$dest = $app . DIRECTORY_SEPARATOR . str_replace($file_name, $new_file_name,  $iterator->getSubPathName());
				echo $file_name . " changes to " . $new_file_name . "\n";
				echo $controller_name . " changes to " . $new_controller_name . "\n";
				rename($source, $dest);
				$contents=file_get_contents($dest);
				$contents=str_replace($controller_name, $new_controller_name, $contents);
				file_put_contents($dest, $contents);
			}
		}
	}
}
echo "Done!\n";