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
$options = getopt("s:m::");
$model_name = null;
if(isset($options["m"])) $model_name = $options["m"];

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

	//get columns from specified table or all tables
	if($model_name)
	{
		$stmt = $db->prepare("SHOW COLUMNS FROM " . $model_name . "");
		$stmt->execute();	
		$table_schema = $stmt->fetchAll();
		echo "\nRead table " . $model_name . " columns...\n";

		sqlToYml($table_schema, $model_name, $db_name);
	}
	else
	{
		$stmt = $db->prepare("SHOW TABLES");
		$stmt->execute();
		$table_names = $stmt->fetchAll();

		foreach($table_names as $table_array)
		{
			$table_name = $table_array[0];
			$stmt = $db->prepare("SHOW COLUMNS FROM " . $table_name . "");
			$stmt->execute();	
			$table_schema = $stmt->fetchAll();
			echo "\nRead table " . $table_name . " columns...\n";

			sqlToYml($table_schema, $table_name, $db_name);
		}
	}
}


function sqlToYml($sql, $model_name, $database_name)
{
	$output = "";
	$model_name_camel = Dinkly::convertToCamelCase($model_name, 1);

	// Output the table name
	$output .= "table_name: " . $model_name . "\nregistry:\n";

	// Loop over the result set
	foreach($sql as $row)
	{
		if(in_array($row["Field"], array("id", "created_at", "updated_at")))
		{
			$output .= "  - " . $row["Field"] . "\n";
		}
		else
		{
			//Separate the type and length
			$type = explode("(", $row["Type"]);
			
			/* Output the row/collection indicator */
			//add the name	
			$output .= "  - " . $row["Field"] . ":";
			//add the type
			$output .= " { type: " . $type[0];
			//if the length contains a comma (for decimals) quote the length
			if($type[1] AND strpos($type[1],",") != false) $output .= ", length: " . str_replace(")", "", $type[1]) . "'";
			//else if there is a length add it
			elseif($type[1]) $output .= ", length: " . str_replace(")", "", $type[1]);
			//set whether null is allowable
			$output .= ", allow_null: ";
			if($row["Null"] == "Yes") $output .= "true";
			else $output .= "false";
			//add a line break
			$output .=  " }\n";
		}
	}

	//Set directory path to keep things cleaner
	$dir = dirname(__FILE__) . "/../config/schemas/" . $database_name;
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
