<?php

require_once('config/bootstrap.php');

$options = getopt("m:ic::");
if(!isset($options['m']))
{
	echo "\nPlease use the -m flag to indicate the desired model name to use.\nExample: php gen_model.php -m=fubar\n\n";
	die();
}

if(ModelBuilder::buildModel($options['m']))
{
	if(isset($options['i'])) { ModelBuilder::buildTable($options['m'], false, $options['c']); }
}