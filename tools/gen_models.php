<?php

// require_once('config/bootstrap.php');

// $sql = $schema = null;
// $options = getopt("s:i");
// if(isset($options['i'])) { $sql = true; }
// if(isset($options['s'])) { $schema = $options['s']; }
// DinklyBuilder::buildAllModels($schema, $sql);

require_once('config/bootstrap.php');

echo "\n ============================ Generate All Dinkly Models in Schema ====================\n\n";
    
$options = getopt("hm:s:ip:");

if(isset($options['h']) || $options == array())
{
    //Display some help
    echo "   Usage: php tools/gen_model.php [args]\n\n";
    echo "   The available args are:\n";
    echo "       -h    		   Show this Help\n";
    echo "       -m    		   Model name, in camel-case format (Required)\n";
    echo "       -s    		   Schema name, in underscore format (Required)\n";
    echo "       -p    		   Plugin name, in underscore format (Optional)\n";
    echo "       -i    		   Insert SQL (Optional)\n";
    echo "\n";
    echo "   Example: php tools/gen_model.php -s=dinkly -m=FubarTown -i\n";
    
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