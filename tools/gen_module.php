<?php

require_once('config/bootstrap.php');

$options = getopt("m:");
if(!isset($options['m']))
{
	echo "\nPlease use the -m flag to indicate the desired module name to use.\nExample: php gen_module.php -m test_module\n\n";
}
else { ModuleBuilder::buildModule($options['m']); }