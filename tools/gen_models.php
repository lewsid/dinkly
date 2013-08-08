<?php

require_once('config/bootstrap.php');

$sql = $target_connection = false;
$options = getopt("s:i");
if(isset($options['i']))
{
	$sql = true;
}

DinklyBuilder::buildAllModels($options['s'], $sql);
