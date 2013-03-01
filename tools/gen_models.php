<?php

require_once('config/bootstrap.php');

$sql = $target_connection = false;
$options = getopt("ic::");
if(isset($options['i']))
{
	$sql = true;
	if(isset($options['c']))
	{
		$target_connection = $options['c'];
	}
}
ModelBuilder::buildAll($sql, $target_connection);