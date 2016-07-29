<?php

/*
	EXPERIMENTAL!

	This function is used to read the structure of a database table or 
	an entire database and create yml files for Dinkly

	Example Usages: 
		php tools/import_db.php -s=my_database
		php tools/import_db.php -s=my_database -m=my_table

	Note: You must first add your database to config/db.yml
*/

require_once("config/bootstrap.php");

//get the options to use
$options = getopt("s:m:");
$model_name = null;
if(isset($options["m"])) $model_name = $options["m"];
else echo "\nYou must specify a model\n";

//call the main function
if($options["s"]) getDbStructure($options["s"], $model_name);
else echo "\nYou must specify a database\n";

//this function theoretically would be moved into Dinkly core when completed
function getDbStructure($schema, $model_name = null, $verbose_output = true)
{
	//set active connection to schema and stop if there is no matching schema
	if(!DinklyDataConfig::setActiveConnection($schema))
	{
		echo "\nNo such schema in config/db.yml\n";

		return false;
	} 

	//Connect to database
	$db = DinklyDataConnector::fetchDB();

	$creds = DinklyDataConfig::getDBCreds();
	$db_name = $creds["name"];
	$db_name = DinklyBuilder::sanitize($db, $db_name);
	$model_name = DinklyBuilder::sanitize($db, $model_name);

	$stmt = $db->prepare("SELECT * FROM " . $model_name . " ");
	$stmt->execute();
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo "\nRead table " . $model_name . " columns...\n";

	sqlToYml($rows, $model_name, $db_name);
}


function sqlToYml($rows, $model_name, $database_name)
{
	$output = "";
	$model_name_camel = Dinkly::convertToCamelCase($model_name, 1);

	// Output the table name
	$output .= "table_name: " . $model_name . "\nrecords:\n";

	// Loop over the result set
	foreach($rows as $row)
	{
		$output .= "  -\n";
		foreach($row as $key => $val)
		{
			$output .= "    " . $key . ": \"" . $val . "\"\n";
		}
	}

	//Set directory path to keep things cleaner
	$dir = dirname(__FILE__) . "/../config/fixtures/" . $database_name;
	$yml_file = $model_name_camel . ".yml";

	//Create directory if it doesn"t exist
	if(!file_exists($dir)) shell_exec("mkdir $dir");

	//If file exists move it to a backup
	if(file_exists("$dir/$yml_file")) shell_exec("mv $dir/$yml_file $dir/$yml_file~");

	//Write to the yml file	
	$handle = fopen("$dir/$yml_file", "w") or die("Cannot open file:  ".$yml_file);
	fwrite($handle, $output);

	echo "\nYML file created...\n";
}	
