<?php
/**
 * DinklyBuilder
 *
 * Children of this class should contain only static functions that return arrays
 *
 * @package    Dinkly
 * @subpackage CoreClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

use Symfony\Component\Yaml\Yaml;

class DinklyBuilder extends BaseDinklyBuilder
{
	//Put overrides here...
	public static function addMissingModelsToDb($schema, $plugin_name = null, $verbose_output = null)
	{
		if(!DinklyDataConfig::setActiveConnection($schema)) { return false; }

		//If no DB exists, create one
		try { $db = DinklyDataConnector::fetchDB(); }
		catch(PDOException $e)
		{
			if($e->getCode() == 1049)
			{
				$creds = DinklyDataConfig::getDBCreds();
				static::createDb($creds['name'], $creds);
				$db = DinklyDataConnector::fetchDB();
			}
		}

		$model_names = array();
		if($plugin_name)
		{
			$model_names = static::getAllPluginModels($plugin_name, $schema);
		}
		else
		{
			$model_names = static::getAllModels($schema);
		}

		if($model_names != array())
		{
			//Gather up yaml table names for each model
			$yaml_table_names = array();
			foreach($model_names as $model)
			{
				$model_yaml = static::parseModelYaml($schema, $model, $plugin_name, false);
				$yaml_table_names[$model] = $model_yaml['table_name'];
			}

			$creds = DinklyDataConfig::getDBCreds();
			//Gather up the table names for those in the database currently
			$query = "SELECT * FROM ".$creds['name'].".INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
			$results = $db->query($query)->fetchAll();
			$db_table_names = array();
			foreach($results as $row) { $db_table_names[] = $row[0]; }

			//Find out which tables are missing from the db, but defined in the yaml
			$missing_tables = array();
			foreach($yaml_table_names as $model_name => $yaml_table_name)
			{
				if(!in_array($yaml_table_name, $db_table_names))
				{
					$missing_tables[$model_name] = $yaml_table_name;
				}
			}

			//Create our missing tables in the database
			foreach($missing_tables as $model_name => $table)
			{
				if($verbose_output)
				{
					echo "Creating table " . $table . "...\n";
				}
				static::buildTable($schema, $model_name, $plugin_name, null, $verbose_output, null);
			}
		}
	}

	public static function createDb($name, $creds)
	{
		$db = new PDO(
				"dblib:host=".$creds['host'].";",
				$creds['user'],
				$creds['pass']
		);

		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		//Sanitize the db name
		$name = static::sanitize($db, $name);

		//Create database if we need to
		//$db->exec("CREATE DATABASE IF NOT EXISTS " . $name);
		$db->exec("if not exists(select * from sys.databases where name = '".$name."') create database ".$name);
	}

	public static function buildTable($schema, $model_name, $plugin_name = null, $model_yaml = null, $verbose_output = true, $override_database_name = null)
	{$verbose_output = true;
		if(!$model_yaml)
		{
			$model_yaml = static::parseModelYaml($schema, $model_name, $plugin_name, $verbose_output);
		}

		if(!$model_yaml) { return false; }

		if(!DinklyDataConfig::setActiveConnection($schema)) { return false; }

		//Use the proper DB credentials, or apply a passed-in override
		$creds = DinklyDataConfig::getDBCreds();

		$name = $creds['name'];
		if($override_database_name) { $name = $override_database_name; }

		//Create database if it doesn't exist
		static::createDb($name, $creds);

		//Connect to the target DB
		$creds['name'] = $name;
		$db = DinklyDataConnector::fetchDB($creds);

		if($verbose_output)
		{
			echo "Creating/Updating MsSQL for table " . $model_yaml['table_name'] . "...";
		}

		//Now let's craft the query to build the table
		$table_name = static::sanitize($db, $model_yaml['table_name']);
		//$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (";
		$sql = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE id = object_id(N'".$table_name."')
				AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
				CREATE TABLE ".$table_name." (";

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
					$sql .= "id int NOT NULL IDENTITY";
					break;

				case 'created_at':
					$sql .= "created_at datetime NOT NULL";
					break;

				case 'updated_at':
					$sql .= "updated_at timestamp NOT NULL";
					break;

				default:
					$sanitized_col_name = static::sanitize($db, $col_name);
					$sanitized_col_type = static::sanitize($db, $column[$col_name]['type']);
					$sql .= '' . $sanitized_col_name . ' ' . $sanitized_col_type;

					if(isset($column[$col_name]['length']) and $sanitized_col_type != 'int')
					{
						$sql .= ' ('.$column[$col_name]['length'].')';
					}
					else if($sanitized_col_type == 'varchar' && !isset($column[$col_name]['length']))
					{
						throw new Exception($table_name . ' - ' . $sanitized_col_name . ' - length required.');
					}

					if(isset($column[$col_name]['default']))
					{
						$sql .= " DEFAULT '".$column[$col_name]['default']."'";
					}

					if(isset($column[$col_name]['allow_null']) && !$field_config[$col_name]['allow_null']) { $sql .= " NOT NULL"; }

					if(isset($column[$col_name]['primary_key']) && $column[$col_name]['primary_key']) { $sql .= " PRIMARY KEY"; }

					if(isset($column[$col_name]['auto_increment']) && $column[$col_name]['auto_increment']) { $sql .= " AUTO_INCREMENT"; }

					break;
	  		}

			if($pos == sizeof($model_yaml['registry']))
			{
				if($has_id) { $sql .= ", PRIMARY KEY (id)"; }
			}
			else { $sql .= ", "; }
		}

		$sql .= ")";

		//if($has_id) { $sql .= " AUTO_INCREMENT=1"; }

		$sql .= ";";
		error_log($sql);
		//Create the table
		$db->exec($sql);

		if($verbose_output) echo "...success!\n";

		return true;
	}

	public static function addMissingModelFieldsToDb($schema, $plugin_name = null, $verbose_output = null)
	{
		if(!DinklyDataConfig::setActiveConnection($schema)) { return false; }

		//If no DB exists, create one
		try { $db = DinklyDataConnector::fetchDB(); }
		catch(PDOException $e)
		{
			if($e->getCode() == 1049)
			{
				static::createDb($schema, DinklyDataConfig::getDBCreds());
				$db = DinklyDataConnector::fetchDB();
			}
		}

		$model_names = DinklyBuilder::getAllModels($schema);
		$creds = DinklyDataConfig::getDBCreds();

		//Gather up yaml configs for each model
		$model_yaml = $table_names = array();
		foreach($model_names as $model)
		{
			$model_yaml[$model] = static::parseModelYaml($schema, $model, $plugin_name, false);
			$table_names[$model] = $model_yaml[$model]['table_name'];
		}

		//Create index for matching tables and columns within the yaml config
		$yaml_fields = array();
		foreach($model_yaml as $model_name => $y)
		{
			// Keep track of model name throughout: it may differ from the table name
			$yaml_fields[$model_name] = array();
			foreach($y['registry'] as $field_name)
			{
				$name = null;
				if(is_array($field_name)) { $name = key($field_name); }
				else { $name = $field_name; }

				$yaml_fields[$model_name][] = $name;
			}
		}

		//Create a very similar index, for matching tables and columns within the existing database
		$db_table_fields = array();
		foreach($model_yaml as $model_name => $y)
		{
			//$query = "show columns from " . $y['table_name'];
			$query = "SELECT *
						FROM ".$creds['name'].".INFORMATION_SCHEMA.COLUMNS
						WHERE TABLE_NAME = N'".$y['table_name']."'";
			$results = $db->query($query)->fetchAll();

			$db_table_fields[$model_name] = array();
			foreach($results as $k => $v)
			{
				$db_table_fields[$model_name][] = $v['Field'];
			}
		}

		//Find any fields that are in the yaml, but are not in the database currently
		$fields_to_add = array();
		foreach($yaml_fields as $model_name => $yaml_field_list)
		{
			foreach($yaml_field_list as $field_name)
			{
				if(!in_array($field_name, $db_table_fields[$model_name]))
				{
					if(!isset($fields_to_add[$model_name])) { $fields_to_add[$model_name] = array(); }
					$fields_to_add[$model_name][] = $field_name;
				}
			}
		}

		//For each field missing from the database, but present in the yaml, run an alter query
		foreach($fields_to_add as $model_name => $field_list)
		{
			foreach($field_list as $field)
			{
				// Fetch table name from the YAML
				$table = $model_yaml[$model_name]['table_name'];
				$registry = $model_yaml[$model_name]['registry'];
				foreach($registry as $field_config)
				{
					$sql = null;
					if(is_array($field_config))
					{
						if(key($field_config) == $field)
						{
							$sql = static::genTableAlterQuery($db, $table, $field_config);
						}
					}
					else
					{
						if($field == $field_config)
						{
							$sql = static::genTableAlterQuery($db, $table, $field_config);
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
}