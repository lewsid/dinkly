<?php

use Symfony\Component\Yaml\Yaml;

class DinklyBuilder extends Dinkly
{
	public static function genTableAlterQuery($db, $table, $field_config)
	{
		$sql = "alter table " . self::sanitize($db, $table) . " add ";

		if(is_array($field_config))
		{
			$col_name = key($field_config);
			$sanitized_col_name = self::sanitize($db, $col_name);
			$sanitized_col_type = self::sanitize($db, $field_config[$col_name]['type']);
			$sql .= $sanitized_col_name . ' ' . $sanitized_col_type;

			if(isset($field_config[$col_name]['length']))
			{
				$sql .= ' ('.$field_config[$col_name]['length'].')';
			}

			if(!$field_config[$col_name]['allow_null']) { $sql .= " NOT NULL"; }
		}
		else
		{
			switch($field_config)
			{
				case 'id':
					$has_id = true;
					$sql .= "`id` int(11) NOT NULL AUTO_INCREMENT";
					break;

				case 'created_at':
					$sql .= "`created_at` datetime NOT NULL";
					break;

				case 'updated_at':
					$sql .= "`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP";
					break;
			}
		}

		return $sql;
	}

	public static function addMissingModelFieldsToDb($schema, $verbose_output = null)
	{
		if(!DinklyDataConfig::setActiveConnection($schema)) { return false; }

		//If no DB exists, create one
		try { $db = DinklyDataConnector::fetchDB(); }
		catch(PDOException $e)
		{
			if($e->getCode() == 1049)
			{
				self::createDb($schema, DinklyDataConfig::getDBCreds());
				$db = DinklyDataConnector::fetchDB();
			}
		}

		$model_names = DinklyBuilder::getAllModels($schema);

		//Gather up yaml configs for each model
		$model_yaml = $table_names = array();
		foreach($model_names as $model)
		{
			$model_yaml[$model] = self::parseModelYaml($schema, $model, false);
			$table_names[$model] = $model_yaml[$model]['table_name'];
		}

		//Create index for matching tables and columns within the yaml config
		$yaml_fields = array();
		foreach($model_yaml as $y)
		{
			$yaml_fields[$y['table_name']] = array();
			foreach($y['registry'] as $field_name)
			{
				$name = null;
				if(is_array($field_name)) { $name = key($field_name); }
				else { $name = $field_name; }

				$yaml_fields[$y['table_name']][] = $name;
			}
		}

		//Create a very similar index, for matching tables and columns within the existing database
		$db_table_fields = array();
		foreach($table_names as $table_name)
		{
			$query = "show columns from " . $table_name;
			$results = $db->query($query)->fetchAll();

			$db_table_fields[$table_name] = array();
			foreach($results as $k => $v)
			{
				$db_table_fields[$table_name][] = $v['Field'];
			}
		}

		//Find any fields that are in the yaml, but are not in the database currently
		$fields_to_add = array();
		foreach($yaml_fields as $table_name => $yaml_field_list)
		{
			foreach($yaml_field_list as $field_name)
			{
				if(!in_array($field_name, $db_table_fields[$table_name]))
				{
					if(!isset($fields_to_add[$table_name])) { $fields_to_add[$table_name] = array(); }
					$fields_to_add[$table_name][] = $field_name;
				}
			}
		}

		//For each field missing from the database, but present in the yaml, run an alter query
		foreach($fields_to_add as $table => $field_list)
		{
			foreach($field_list as $field)
			{
				$registry = $model_yaml[Dinkly::convertToCamelCase($table, true)]['registry'];
				foreach($registry as $field_config)
				{
					$sql = null;
					if(is_array($field_config))
					{
						if(key($field_config) == $field)
						{
							$sql = self::genTableAlterQuery($db, $table, $field_config);
						}
					}
					else
					{
						if($field == $field_config)
						{
							$sql = self::genTableAlterQuery($db, $table, $field_config);
						}
					}

					if($sql)
					{
						if($verbose_output)
						{
							echo "Adding field " . $field . " to " . $table . "...\n";
						}
						$db->exec($sql);
					}
				}
			}
		}
	}

	public static function createDb($db_name, $creds)
	{
		$db = new PDO(
				"mysql:host=".$creds['DB_HOST'].";",
				$creds['DB_USER'],
				$creds['DB_PASS']
		);

		//Sanitize the db name
		$db_name = self::sanitize($db, $db_name);

		//Create database if we need to
		$db->exec("CREATE DATABASE IF NOT EXISTS " . $db_name);
	}

	public static function addMissingModelsToDb($schema, $verbose_output = null)
	{
		if(!DinklyDataConfig::setActiveConnection($schema)) { return false; }

		//If no DB exists, create one
		try { $db = DinklyDataConnector::fetchDB(); }
		catch(PDOException $e)
		{
			if($e->getCode() == 1049)
			{
				self::createDb($schema, DinklyDataConfig::getDBCreds());
				$db = DinklyDataConnector::fetchDB();
			}
		}

		$model_names = self::getAllModels($schema);

		//Gather up yaml table names for each model
		$yaml_table_names = array();
		foreach($model_names as $model)
		{
			$model_yaml = self::parseModelYaml($schema, $model, false);
			$yaml_table_names[] = $model_yaml['table_name'];
		}

		//Gather up the table names for those in the database currently
		$query = "show tables;";
		$results = $db->query($query)->fetchAll();
		$db_table_names = array();
		foreach($results as $row) { $db_table_names[] = $row[0]; }

		//Find out which tables are missing from the db, but defined in the yaml
		$missing_tables = array();
		foreach($yaml_table_names as $yaml_table_name)
		{
			if(!in_array($yaml_table_name, $db_table_names))
			{
				$missing_tables[] = $yaml_table_name;
			}
		}

		//Create our missing tables in the database
		foreach($missing_tables as $table)
		{
			if($verbose_output)
			{
				echo "Creating table " . $table . "...\n";
			}
			self::buildTable($schema, Dinkly::convertToCamelCase($table, true), $verbose_output);
		}
	}

	public static function buildModule($app_name, $module_name)
	{
		$app_dir = $_SERVER['APPLICATION_ROOT'] . "apps/" . $app_name;
		if(!is_dir($app_dir))
		{
			echo "\nError: No matching app found.\n\n";
		}

		$module_folder = $app_dir . "/modules/" . $module_name;
		if(!is_dir($module_folder))
		{
			if(mkdir($module_folder))
			{
				mkdir($module_folder . "/views");

				$fp = fopen($module_folder . "/views/default.php", 'w+');
				fclose($fp);

				$fp = fopen($module_folder . "/" . Dinkly::convertToCamelCase($module_name, true) . "Controller.php", 'w+');
				fwrite($fp, '<?php' . PHP_EOL . PHP_EOL);
				fwrite($fp, 'class ' . Dinkly::convertToCamelCase($module_name, true) . 'Controller extends Dinkly ' . PHP_EOL . '{' . PHP_EOL);
				fwrite($fp, "\tpublic function loadDefault()" . PHP_EOL . "\t{" . PHP_EOL);
				fwrite($fp, "\t\treturn true;" . PHP_EOL . "\t}" . PHP_EOL . "}" . PHP_EOL);
				fclose($fp);
			}
			else
			{
				echo "\nError: Unable to create module directory.\n\n";
			}
		}
		else
		{
			echo "\nError: That module already exists.\n\n";
			return false;
		}
	}

	public static function buildApp($app_name)
	{
		$app_dir = $_SERVER['APPLICATION_ROOT'] . "apps/" . $app_name;
		if(is_dir($app_dir))
		{
			echo "\nError: Application already exists.\n\n";
			return false;
		}
		else
		{
			mkdir($app_dir);
		}

		if(mkdir($app_dir . "/layout") && mkdir($app_dir . "/modules"))
		{
			$fp = fopen($app_dir . "/layout/header.php", 'w+');
			fclose($fp);

			$fp = fopen($app_dir . "/layout/footer.php", 'w+');
			fclose($fp);
		}
		else
		{
			echo "\nError: Unable to create app directories.\n\n";
		}
	}

	public static function parseModelYaml($schema, $model_name, $verbose_output = true)
	{
		$file_path = $_SERVER['APPLICATION_ROOT'] . "config/schemas/"
		  . $schema . "/" . $model_name . ".yml";

		if($verbose_output)
		{
			echo "Attempting to parse '" . $model_name . "' schema yaml...";
		}

		if(!file_exists($file_path))
		{
			if($verbose_output)
			{
				echo "Schema directory not found.\n";
			}
			return false;
		}

		$yaml = Yaml::parse($file_path);

		$table_name = $yaml['table_name'];

		if(strlen($table_name) < 2)
		{
			if($verbose_output)
			{
				echo "Failed to read a valid table name\n";
			}
			return false;
		}
		else
		{
			if($verbose_output)
			{
				echo "Success!\n";
			}
			return $yaml;
		}
	}

	public static function buildModel($schema, $model_name)
	{
		$raw_model = self::parseModelYaml($schema, $model_name);
		if(!$raw_model) { return false; }

		$table_name = $raw_model['table_name'];

		if(isset($raw_model['registry']))
		{
			$base_file = $_SERVER['APPLICATION_ROOT'] . "classes/models/base/Base" . $model_name . ".php";

	  		echo "Attempting to create/write base '" . $model_name . "' models...\n";

			//create base model
			if($fp = fopen($base_file, 'w+'))
			{
				fwrite($fp, '<?php' . PHP_EOL . PHP_EOL);
				fwrite($fp, '# This is an auto-generated file. Please do not alter this file. Instead, make changes to the model file that extends it.');
				fwrite($fp, PHP_EOL . PHP_EOL);
				fwrite($fp, 'class Base' . $model_name . ' extends DinklyDataModel' . PHP_EOL . '{' . PHP_EOL);
				fwrite($fp, "\t" . 'public $registry = array(' . PHP_EOL);

			    foreach($raw_model['registry'] as $key => $column)
			    {
					$col_name = null;
					if(is_array($column)) { $col_name = key($column); }
					else { $col_name = $column; }

					$var_name = str_replace('_', ' ', $col_name);
					$var_name = ucwords(strtolower($var_name));
					$var_name = str_replace(' ', '', $var_name);
					fwrite($fp, "\t\t" . "'" . $col_name . "' => '" . $var_name . "'," . PHP_EOL);
		    	}

			    fwrite($fp, "\t" . ');' . PHP_EOL . PHP_EOL);
			    fwrite($fp, "\t" . 'public $dbTable = \'' . $table_name . '\';' . PHP_EOL);
			    fwrite($fp, '}' . PHP_EOL . PHP_EOL);

			    fclose($fp);

			    echo "Successfully created base model!\n";
			}
			else
			{
				echo "Failed! Aborting\n";
				return false;
			}

			//create base collection class

			//second, create the extensible model files, if one doesn't already exist (we never overwrite this one)
			$custom_file = $_SERVER['APPLICATION_ROOT'] . "classes/models/custom/" . $model_name . ".php";
			$custom_collection_file = $_SERVER['APPLICATION_ROOT'] . "classes/models/custom/" . $model_name . "Collection.php";

			if(!file_exists($custom_file))
			{
				echo "Creating custom models...";

				$fp = fopen($custom_file, 'w+');
				fwrite($fp, '<?php' . PHP_EOL . PHP_EOL);
				fwrite($fp, 'class ' . $model_name . ' extends Base' . $model_name . PHP_EOL . '{' . PHP_EOL . PHP_EOL);
				fwrite($fp, '}' . PHP_EOL . PHP_EOL);
				fclose($fp);

				echo "successfully created custom model!\n";
			}
			else echo "Custom '" . $model_name . "' model already exists - skipping.\n";

			if(!file_exists($custom_collection_file))
			{
				$fp = fopen($custom_collection_file, 'w+');
				fwrite($fp, '<?php' . PHP_EOL . PHP_EOL);
				fwrite($fp, 'class ' . $model_name . 'Collection extends DinklyDataCollection' . PHP_EOL . '{' . PHP_EOL . PHP_EOL);
				fwrite($fp, '}' . PHP_EOL . PHP_EOL);
				fclose($fp);
			}
			else echo "Custom '" . $model_name . "' collection class already exists - skipping.\n";

			return true;
		}
	}

	public static function fetchDB($creds = null)
	{
		if(!$creds) $creds = DinklyDataConfig::getDBCreds();

		$db = new PDO(
				"mysql:host=".$creds['DB_HOST'].";dbname=".$creds['DB_NAME'],
				$creds['DB_USER'],
				$creds['DB_PASS']
		);

		return $db;
	}

	public static function sanitize($db, $variable)
	{
		$output = $db->quote($variable);
		return str_replace("'", "", $variable);
	}

	public static function buildTable($schema, $model_name, $verbose_output = true, $override_database_name = null)
	{
		$model_yaml = self::parseModelYaml($schema, $model_name, $verbose_output);
		if(!$model_yaml) { return false; }

		if(!DinklyDataConfig::setActiveConnection($schema)) { return false; }

		//Use the proper DB credentials, or apply a passed-in override
		$creds = DinklyDataConfig::getDBCreds();
		$db_name = $creds['DB_NAME'];
		if($override_database_name) { $db_name = $override_database_name; }

		//Create database if it doesn't exist
		self::createDb($db_name, $creds);

		//Now connect to the new DB
		$creds['DB_NAME'] = $db_name;
		$db = self::fetchDB($creds);

		if($verbose_output)
		{
			echo "Creating/Updating MySQL for table " . $model_yaml['table_name'] . "...";
		}

		//Now let's craft the query to build the table
		$table_name = self::sanitize($db, $model_yaml['table_name']);
		$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (";

		$has_id = false; $pos = 0;

		foreach($model_yaml['registry'] as $key => $column)
		{
			$pos++;
			$col_name = null;
			if(is_array($column)) { $col_name = key($column); }
			else { $col_name = $column; }

			switch($col_name)
			{
	    		case 'id':
					$has_id = true;
					$sql .= "`id` int(11) NOT NULL AUTO_INCREMENT";
					break;

				case 'created_at':
					$sql .= "`created_at` datetime NOT NULL";
					break;

				case 'updated_at':
					$sql .= "`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP";
					break;

				default:
					$sanitized_col_name = self::sanitize($db, $col_name);
					$sanitized_col_type = self::sanitize($db, $column[$col_name]['type']);
					$sql .= $sanitized_col_name . ' ' . $sanitized_col_type;

					if(isset($column[$col_name]['length']))
					{
						$sql .= ' ('.$column[$col_name]['length'].')';
					}
					else if($sanitized_col_type == 'varchar' && !isset($column[$col_name]['length']))
					{
						throw new Exception("\n" . $table_name . ' - ' . $sanitized_col_name . ' - length required.');
					}

					if(!isset($column[$col_name]['allow_null'])) { $sql .= " NULL"; }

					break;
	  		}

			if($pos == sizeof($model_yaml['registry']))
			{
				if($has_id) { $sql .= ", PRIMARY KEY (`id`)"; }
			}
			else { $sql .= ", "; }
		}

		$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8";

		if($has_id) { $sql .= " AUTO_INCREMENT=1"; }

		$sql .= ";";

		//Create the table
		$db->exec($sql);

		if($verbose_output) echo "...success!\n";

		return true;
	}

	public static function getAllModels($schema)
	{
		$all_files = scandir($_SERVER['APPLICATION_ROOT'] . "config/schemas/" . $schema . "/");

		$model_names = array();
		foreach($all_files as $file)
		{
			if($file != '.' && $file != '..' && stristr($file, '.yml'))
			$model_names[] = str_replace('.yml', '', $file);
		}

		return $model_names;
	}

	public static function buildAllModels($schema = null, $insert_sql = false)
	{
		$schema_names = array();

		if(!$schema)
		{
			$all_folders = scandir($_SERVER['APPLICATION_ROOT'] . "config/schemas/");

			foreach($all_folders as $folder)
			{
		  		if(substr($folder, 0, 1) != '.')
		    	$schema_names[] = $folder;
			}
		}
		else
		{
			$schema_names[] = $schema;
		}

		foreach($schema_names as $schema)
		{
			$model_names = self::getAllModels($schema);

			foreach($model_names as $model)
			{
				self::buildModel($schema, $model);
			}

			if($insert_sql)
			{
				self::addMissingModelsToDb($schema, true);
				self::addMissingModelFieldsToDb($schema, true);
			}
		}
	}

	/*
		$truncate will truncate the table if set to true, or append records if false
	*/
	public static function loadFixture($set, $model_name, $truncate = true, $verbose_output = true, $override_database_name = null)
	{
		//Use the proper DB credentials, or apply a passed-in override
		$creds = DinklyDataConfig::getDBCreds();
		if($override_database_name)
		{
			$creds['DB_NAME'] = $override_database_name;
			DinklyDataConfig::setActiveConnection($creds);
		}

		//Create database if it doesn't exist
		$db = self::fetchDB($creds);

    	$file_path = $_SERVER['APPLICATION_ROOT'] . "config/fixtures/" . $set . "/" . $model_name . ".yml";

		if($verbose_output)
		{
			echo "Attempting to parse '" . $model_name . "' fixture yaml...";
		}

		$fixture = Yaml::parse($file_path);
		$model_name = $collection_name = null;

		if(isset($fixture['table_name']))
		{
			$model_name = Dinkly::convertToCamelCase($fixture['table_name'], true);
			if($verbose_output) { echo "success!\n"; }
		}
		else return false;

		if(isset($fixture['records']))
		{
			if($truncate)
			{
				if($verbose_output) { echo "Truncating '" . $fixture['table_name']. "'..."; }

				$db->exec("truncate table " . $fixture['table_name']);

				if($verbose_output) { echo "success!\n"; }
			}

			$count = sizeof($fixture['records']);

			if($verbose_output)
			{
				echo "Inserting " . $count . " record(s) into table '" . $fixture['table_name'] . "'";
			}

			foreach($fixture['records'] as $pos => $record)
			{
				if($verbose_output) { echo "..."; }
				$model = new $model_name();
				foreach($record as $col_name => $value)
				{
					$set_field = 'set' . Dinkly::convertToCamelCase($col_name, true);
					$model->$set_field($value);
				}
				$model->save();
			}

			if($verbose_output) { echo "success!\n"; }

			return true;
		}
	}

	public static function loadAll($set)
	{
		if(!is_dir($_SERVER['APPLICATION_ROOT'] . "config/fixtures/" . $set))
		{
			echo "\nNo matching set of fixtures found for '" . $set . "'\n\n";
			return false;
		}
		$all_files = scandir($_SERVER['APPLICATION_ROOT'] . "config/fixtures/" . $set);

		$model_names = array();
		foreach($all_files as $file)
		{
			if($file != '.' && $file != '..' && stristr($file, '.yml'))
			$model_names[] = str_replace('.yml', '', $file);
		}

		foreach($model_names as $model)
		{
			self::loadFixture($set, $model);
		}

		return true;
	}
}