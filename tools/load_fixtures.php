<?php

require_once('config/bootstrap.php');

$options = getopt("s:m:p:t");

if(isset($options['h']) || $options == array())
{
    echo "\n ================================== Load Fixtures ====================================\n\n";
    echo "   This tool will insert fixtures into a database. You may load all fixtures for\n";
    echo "   a given schema, or pass a model name to load individually.\n";
    echo "   you may re-run this script with the insert flag to update your tables.";
    echo "\n";
    echo "   Usage: php tools/gen_models.php [args]\n\n";
    echo "   The available arguments are:\n";
    echo "       -h     Show this help\n";
    echo "       -s     Schema name, in underscore format (required)\n";
    echo "       -m     Model name, in camel-case format (optional)\n";
    echo "       -p     Plugin name, in underscore format (optional)\n";
    echo "       -e     Environment, corresponding to a database connection entry in config.yml (defaults to 'dev')\n";
    echo "       -t     Truncate table before loading fixture (optional)\n";
    echo "\n";
    echo "   Example: php tools/load_fixtures.php -s=monkey_tail -m=Banana -t\n";
    
    echo "\n =======================================================================================\n\n";
    exit;
}

if(!isset($options['s']))
{
	echo "\nPlease use the -s flag to indicate which fixture set to use.\nExample: php tools/load_fixtures.php -s=dinkly\n\n";
	echo "You may optionally use the -m flag to indicate a single fixture to load.\nExample: php php tools/load_fixtures.php -s=dinkly -m=fubar\n\n";
	die();
}

$truncate = false;
if(isset($options['t']))
{
	$truncate = true;
}

$plugin_name = false;
if(isset($options['p']))
{
	$plugin_name = $options['p'];
}

if(isset($options['m']))
{
    if(isset($options['e'])) { $Dinkly = new Dinkly($options['e']); }
    
	DinklyBuilder::loadFixture($options['s'], $options['m'], $plugin_name, $truncate);
}
else
{
	DinklyBuilder::loadAllFixtures($options['s'], $plugin_name, $truncate);
}