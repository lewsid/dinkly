<?php

require_once('config/bootstrap.php');

$options = getopt("a:m:");
if(!isset($options['a']))
{
	echo "\nPlease use the -a flag to indicate which application this module will be contained in.\nExample: php tools/gen_module.php -a=dinkly -m=test_module\n\n";
}
else if(!isset($options['m']))
{
	echo "\nPlease use the -m flag to indicate the desired module name to use.\nExample: php tools/gen_module.php -a=dinkly -m=test_module\n\n";
}
else
{
	DinklyBuilder::buildModule($options['a'], $options['m']);

	echo "\nModule created! You'll want to clear the session to access it in your browser using /?nocache=1\n\n";
}