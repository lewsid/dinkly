<?php

//rename_app
require_once('config/bootstrap.php');

$options = getopt("a:d:");
if(!isset($options['a']) || !isset($options['d']))
{
	echo "\nPlease use the -a flag and -d flag to indicate the source application name and the destination application name to use.\nExample: php tools/copy_app.php -a=admin -d=my_admin\n";
}
else 
{
	echo "\nCopying " . $options['a'] . " app to " . $options['d'] . " app \n";

	$source = $_SERVER['APPLICATION_ROOT'] . 'apps/' . $options['a'];
	$dest= $_SERVER['APPLICATION_ROOT'] . 'apps/' . $options['d'];
	$source_prefix = Dinkly::convertToCamelCase($options['a'], true);
	$destination_prefix = Dinkly::convertToCamelCase($options['d'], true);

	if(mkdir($dest))
		echo "\nCreated directory for " . $options['d'] . " app \n";

	echo "\nCopying files for " . $options['d'] . " app:\n";
	foreach (
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST) as $item
		) 
	{
		if ($item->isDir())
		{
			mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
		} 
		else
		{
			$dest_file = $dest . DIRECTORY_SEPARATOR . str_replace($source_prefix, $destination_prefix, $iterator->getSubPathName());
			copy($item, $dest_file);
			$contents=file_get_contents($dest_file);
			$contents=str_replace($options['a'], $options['d'], $contents);
			$contents=str_replace($source_prefix, $destination_prefix, $contents);
			file_put_contents($dest_file, $contents);
			echo $dest_file . "\n";
		}
	}
	echo "\n" . $options['d'] . " app succesfully create!\n\n";
}