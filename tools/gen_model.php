<?php

require_once('config/bootstrap.php');
    
$options = getopt("hm:s:ip:e:");

if(isset($options['h']) || $options == array())
{
    echo "\n ======================== Generate Dinkly Model ========================\n\n";
    echo "   This tool will build a given model and optionally insert a matching\n";
    echo "   table into the database.\n";
    echo "\n";
    echo "   Usage: php tools/gen_model.php [args]\n\n";
    echo "   The available arguments are:\n";
    echo "       -h     Show this help\n";
    echo "       -m     Model name, in camel-case format (required)\n";
    echo "       -s     Schema name, in underscore format (required)\n";
    echo "       -p     Plugin name, in underscore format (optional)\n";
    echo "       -e     Environment, corresponding to a database connection entry in config.yml (defaults to 'dev')\n";
    echo "       -i     Insert SQL (optional)\n";
    echo "\n";
    echo "   Example: php tools/gen_model.php -s=monkey_tail -m=FunkyNugget -i\n";  
    echo "\n =======================================================================\n\n";
    exit;
}

if(!isset($options['s']))
{
	echo "\nPlease use the -s flag to indicate which schema set to use.\n\n";
	die();
}

if(!isset($options['m']))
{
	echo "\nPlease use the -m flag to indicate the desired model name to use.\n\n";
	die();
}

$plugin_name = null;
if(isset($options['p'])) { $plugin_name = $options['p']; }

if(isset($options['e'])) { $Dinkly = new Dinkly($options['e'], true); }

if(DinklyBuilder::buildModel($options['s'], $options['m'], $plugin_name))
{
	if(isset($options['i'])) { DinklyBuilder::buildTable($options['s'], $options['m'], $plugin_name); }
}