<?php

require_once('config/bootstrap.php');

$options = getopt("m:s:i");
if(!isset($options['s']))
{
	echo "\nPlease use the -s flag to indicate which schema set to use.\nExample: php gen_model.php -s=dinkly -m=fubar\n\n";
	die();
}
if(!isset($options['m']))
{
	echo "\nPlease use the -m flag to indicate the desired model name to use.\nExample: php gen_model.php -s=dinkly -m=fubar\n\n";
	die();
}

if(DinklyBuilder::buildModel($options['s'], $options['m']))
{
	if(isset($options['i'])) { DinklyBuilder::buildTable($options['s'], $options['m']); }
}