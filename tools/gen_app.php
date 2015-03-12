<?php

require_once('config/bootstrap.php');

$options = getopt("a:");
if(!isset($options['a']))
{
	echo "\nPlease use the -a flag to indicate the desired application name to use.\nExample: php tools/gen_app.php -a=admin\n\n";
}
else { DinklyBuilder::buildApp($options['a']); }