<?php

/* Use this to test your database connection, as configured in classes/DinklyDataConfig.php */

require_once('config/bootstrap.php');

$options = getopt("s:e::");
if(!isset($options['s']))
{
	echo "\nPlease use the -s flag to indicate which connection/schema you would like to test\n\n";
	return false;
}

if(isset($options['e']))
{
	$Dinkly = new Dinkly($options['e']);
}

if(DinklyDataConnector::testDB($options['s'])) { echo "\nSuccessfully connected to database!\n"; }
else { echo "\nUnable to connect to database!\n"; }
