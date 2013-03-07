<?php

require_once('config/bootstrap.php');

$sql = $target_connection = false;
$options = getopt("s:i");
if(!isset($options['s']))
{
	echo "\nPlease use the -s flag to indicate which schema to use.\nExample: php gen_model.php -s=dinkly\n\n";
	die();
}
if(isset($options['i']))
{
	$sql = true;
}
DinklyBuilder::buildAllModels($options['s'], $sql);