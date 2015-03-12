<?php

require_once('config/bootstrap.php');

$options = getopt("s:m:");
if(!isset($options['s']))
{
	echo "\nPlease use the -s flag to indicate which fixture set to use.\nExample: php tools/load_fixtures.php -s=dinkly\n\n";
	echo "You may optionally use the -m flag to indicate a single fixture to load.\nExample: php php tools/load_fixtures.php -s=dinkly -m=fubar\n\n";
	die();
}
if(isset($options['m']))
{
	DinklyBuilder::loadFixture($options['s'], $options['m'], true);
}
else
{
	DinklyBuilder::loadAllFixtures($options['s']);
}