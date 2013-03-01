<?php

require_once('config/bootstrap.php');

$sql = $target_connection = false;
$options = getopt("ic::");
if(isset($options['i']))
{
	$sql = true;
}
ModelBuilder::buildAll($sql);