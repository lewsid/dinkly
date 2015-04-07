<?php
/**
 * BaseDinklyBuilder
 *
 * Children of this class should contain only static functions that return arrays
 *
 * @package    Dinkly
 * @subpackage CoreClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

use Symfony\Component\Yaml\Yaml;

class BaseDinklyBuilder extends Dinkly
{
	/**
	 * Alter an existing database table based on new schema for data model
	 *
	 * @param string $db String name of database to be altered
	 * @param string $table String name of table to be altered
	 * @param mixed String | Array $field_config containing new configuration
	 * 
	 * @return string containing sql statement to be queried to alter table
	 */
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

	/**
	 * Add missing fields to existing database table
	 *
	 * @param string $schema String name of schema to use to get fields
	 * @param string $verbose_output string used to log added fields
	 * 
	 * @return bool true if fields added successfully, false otherwise
	 */
	public static function addMissingModelFieldsToDb($schema, $plugin_name = null, $verbose_output = null)
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
			$model_yaml[$model] = self::parseModelYaml($schema, $model, $plugin_name, false);
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

	/**
	 * Create a database if it does not already exist
	 *
	 * @param string $db_name String name of database 
	 * @param array $creds Array containg DB credentials
	 * 
	 * 
	 */
	public static function createDb($name, $creds)
	{
		$db = new PDO(
				"mysql:host=".$creds['host'].";",
				$creds['user'],
				$creds['pass']
		);

		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		//Sanitize the db name
		$name = self::sanitize($db, $name);

		//Create database if we need to
		$db->exec("CREATE DATABASE IF NOT EXISTS " . $name);
	}

	/**
	 * Create missing tables in database based on yaml configurations
	 *
	 * @param string $schema String name of schema to be added
	 * @param string $verbose_output string used to log added models
	 * 
	 * 
	 */
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
				self::createDb($creds['name'], $creds);
				$db = DinklyDataConnector::fetchDB();
			}
		}

		$model_names = array();
		if($plugin_name)
		{
			$model_names = self::getAllPluginModels($plugin_name, $schema);
		}
		else
		{
			$model_names = self::getAllModels($schema);
		}

		if($model_names != array())
		{
			//Gather up yaml table names for each model
			$yaml_table_names = array();
			foreach($model_names as $model)
			{
				$model_yaml = self::parseModelYaml($schema, $model, $plugin_name, false);
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
				self::buildTable($schema, Dinkly::convertToCamelCase($table, true), $plugin_name, null, $verbose_output, null);
			}
		}
	}

	/**
	 * Build module from command line 
	 *
	 * @param string $app_name String name of app you wish to create
	 * @param string $module_name string name of module you wish to create
	 * 
	 * @return bool true if module is built successfully, false otherwise
	 */
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

				$fp = fopen($module_folder . "/" . Dinkly::convertToCamelCase($app_name, true) . Dinkly::convertToCamelCase($module_name, true) . "Controller.php", 'w+');
				fwrite($fp, '<?php' . PHP_EOL );
				fwrite($fp, '/**'.PHP_EOL .
				' * '.Dinkly::convertToCamelCase($module_name, true).'Controller'.PHP_EOL.
				' *'.PHP_EOL .
				' *'.PHP_EOL .
				' * @package    Dinkly'.PHP_EOL .
				' * @subpackage Apps'.Dinkly::convertToCamelCase($app_name, true) . Dinkly::convertToCamelCase($module_name, true).'Controller'.PHP_EOL .
				' * @author     Christopher Lewis <lewsid@lewsid.com>'.PHP_EOL .
				' */' . PHP_EOL . PHP_EOL);
				fwrite($fp, 'class ' . Dinkly::convertToCamelCase($app_name, true) . Dinkly::convertToCamelCase($module_name, true) . 'Controller extends ' . Dinkly::convertToCamelCase($app_name, true) . "Controller" . PHP_EOL . '{' . PHP_EOL);
				fwrite($fp,"\t/**".PHP_EOL .
				"\t * Constructor".PHP_EOL .
				"\t *".PHP_EOL . 
				"\t * @return void".PHP_EOL .
				"\t *".PHP_EOL .
				"\t */".PHP_EOL . "\tpublic function __construct()" . PHP_EOL . "\t{" . PHP_EOL);
				fwrite($fp, "\t\tparent::__construct();" . PHP_EOL . "\t}" . PHP_EOL . PHP_EOL);
				fwrite($fp,"\t/**".PHP_EOL .
				"\t * Load default view".PHP_EOL .
				"\t *".PHP_EOL . 
				"\t * @return bool: always returns true on successful construction of view".PHP_EOL .
				"\t *".PHP_EOL .
				"\t */".PHP_EOL . "\tpublic function loadDefault()" . PHP_EOL . "\t{" . PHP_EOL);
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

	/**
	 * Build application function to be used from command line
	 *
	 * @param string $app_name String name of app you wish to create
	 * 
	 * 
	 * @return bool true if module is built successfully, false otherwise
	 */
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

			$fp = fopen($app_dir . "/" . Dinkly::convertToCamelCase($app_name, true) . "Controller.php", 'w+');
			fwrite($fp, '<?php'.PHP_EOL.
			'/**'.PHP_EOL .
			' * '.Dinkly::convertToCamelCase($app_name, true).'Controller'.PHP_EOL.

			' * '.PHP_EOL .
			' *'.PHP_EOL .
			' * @package    Dinkly'.PHP_EOL .
			' * @subpackage Apps'.Dinkly::convertToCamelCase($app_name, true).'Controller'.PHP_EOL .
			' * @author     Christopher Lewis <lewsid@lewsid.com>'.PHP_EOL .
			' */' . PHP_EOL . PHP_EOL);
			fwrite($fp, 'class ' . Dinkly::convertToCamelCase($app_name, true) . 'Controller extends Dinkly' . PHP_EOL . '{' . PHP_EOL);
			fwrite($fp, "\t/**".PHP_EOL .
			"\t * Default Constructor".PHP_EOL .
			"\t * ".PHP_EOL .
			"\t * @return bool".PHP_EOL .
			"\t * ".PHP_EOL .
			"\t */".PHP_EOL ."\tpublic function __construct()" . PHP_EOL . "\t{" . PHP_EOL);
			fwrite($fp, "\t\treturn true;" . PHP_EOL . "\t}" . PHP_EOL . "}" . PHP_EOL);
			fclose($fp);
		}
		else
		{
			echo "\nError: Unable to create app directories.\n\n";
		}
	}

	/**
	 * Parse yaml schema of specified model
	 *
	 * @param string $schema String name of schema containing model
	 * @param string $model_name String name of model yaml file to be parsed
	 * @param bool $verbose_output defaulted true to show log, make false for no console log
	 *
	 * @return mixed array | bool array containing parse yaml or false on failure
	 */
	public static function parseModelYaml($schema, $model_name, $plugin_name = null, $verbose_output = true)
	{
		$file_path = null;
		if($plugin_name)
		{
			$file_path = $_SERVER['APPLICATION_ROOT'] . "plugins/" . $plugin_name . "/config/schemas/"
		  		. $schema . "/" . $model_name . ".yml";
		}
		else
		{
			$file_path = $_SERVER['APPLICATION_ROOT'] . "config/schemas/"
		  		. $schema . "/" . $model_name . ".yml";
		}

		if($verbose_output)
		{
			echo "Attempting to parse '" . $model_name . "' schema yaml...";
		}

		if(!file_exists($file_path))
		{
			if($verbose_output)
			{
				echo "schema file not found - $file_path\n";
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

		return false;
	}

	/**
	 * Build Base model dynamically based on schema file
	 *
	 * @param string $schema String name of schema containing model
	 * @param string $model_name String name of model yaml file to be parsed
	 * @param string $plugin String name of plugin name 
	 *
	 * @return bool true if both base and custom classes created, false on failure
	 */
	public static function buildModel($schema, $model_name, $plugin_name = null)
	{
		$raw_model = self::parseModelYaml($schema, $model_name, $plugin_name);
		if(!$raw_model) { return false; }

		$table_name = $raw_model['table_name'];

		if(isset($raw_model['registry']))
		{
			$base_file = null;
			if($plugin_name)
			{
				$base_file = $_SERVER['APPLICATION_ROOT'] . "plugins/" . $plugin_name . "/classes/models/base/Base" . $model_name . ".php";

				if(!file_exists($_SERVER['APPLICATION_ROOT'] . "plugins/" . $plugin_name . "/classes/models/base/"))
				{
					mkdir($_SERVER['APPLICATION_ROOT'] . "plugins/" . $plugin_name . "/classes/models/base/");
				}
			}
			else
			{
				$base_file = $_SERVER['APPLICATION_ROOT'] . "classes/models/base/Base" . $model_name . ".php";
			}

	  		echo "Attempting to create/write base '" . $model_name . "' models...\n";

			//create base model
			if($fp = fopen($base_file, 'w+'))
			{
				fwrite($fp, '<?php' . PHP_EOL);
				fwrite($fp, '/**'. PHP_EOL.
				' * '.'Base'.$model_name. PHP_EOL.
				' *'. PHP_EOL.
				' * # This is an auto-generated file. Please do not alter this file. Instead, make changes to the model file that extends it.'. PHP_EOL.
				' *'. PHP_EOL.
				' * @package    Dinkly'. PHP_EOL.
				' * @subpackage ModelsBaseClasses'. PHP_EOL.
				' * @author     Christopher Lewis <lewsid@lewsid.com>'. PHP_EOL.
				' */'. PHP_EOL);
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
				echo "Failed! Could not open model file - $base_file\n";
				return false;
			}

			//create base collection class

			//second, create the extensible model files, if one doesn't already exist (we never overwrite this one)
			$custom_file = null;
			if($plugin_name)
			{
				$custom_file = $_SERVER['APPLICATION_ROOT'] . "/plugins/" . $plugin_name . "/classes/models/custom/" . $model_name . ".php";	
			}
			else
			{
				$custom_file = $_SERVER['APPLICATION_ROOT'] . "classes/models/custom/" . $model_name . ".php";
			}

			$custom_collection_file = null;
			if($plugin_name)
			{
				$custom_collection_file = $_SERVER['APPLICATION_ROOT'] . "/plugins/" . $plugin_name . "/classes/models/custom/" . $model_name . "Collection.php";
			}
			else
			{
				$custom_collection_file = $_SERVER['APPLICATION_ROOT'] . "classes/models/custom/" . $model_name . "Collection.php";
			}

			if(!file_exists($custom_file))
			{
				echo "Creating custom models...";

				$fp = fopen($custom_file, 'w+');
				fwrite($fp, '<?php' . PHP_EOL );
				fwrite($fp, '/**'. PHP_EOL.
				' * '.$model_name. PHP_EOL.
				' *'. PHP_EOL.
				' *'.
				' *'. PHP_EOL.
				' * @package    Dinkly'. PHP_EOL.
				' * @subpackage ModelsCustomClasses'. PHP_EOL.
				' * @author     Christopher Lewis <lewsid@lewsid.com>'. PHP_EOL.
				' */'. PHP_EOL);
				fwrite($fp, 'class ' . $model_name . ' extends Base' . $model_name . PHP_EOL . '{' . PHP_EOL . PHP_EOL);
				fwrite($fp, '}' . PHP_EOL . PHP_EOL);
				fclose($fp);

				echo "successfully created custom model!\n";
			}
			else echo "Custom '" . $model_name . "' model already exists - skipping.\n";

			if(!file_exists($custom_collection_file))
			{
				$fp = fopen($custom_collection_file, 'w+');
				fwrite($fp, '<?php' . PHP_EOL);
				fwrite($fp, '/**'. PHP_EOL.
				' * '.$model_name.'Collection'. PHP_EOL.
				' *'. PHP_EOL.
				' *'.
				' *'. PHP_EOL.
				' * @package    Dinkly'. PHP_EOL.
				' * @subpackage ModelsCustomClasses'. PHP_EOL.
				' * @author     Christopher Lewis <lewsid@lewsid.com>'. PHP_EOL.
				' */'. PHP_EOL);
				fwrite($fp, 'class ' . $model_name . 'Collection extends DinklyDataCollection' . PHP_EOL . '{' . PHP_EOL . PHP_EOL);
				fwrite($fp, '}' . PHP_EOL . PHP_EOL);
				fclose($fp);
			}
			else echo "Custom '" . $model_name . "' collection class already exists - skipping.\n";

			return true;
		}
	}

	/**
	 * Make new database connection using default or specified DB credentials
	 *
	 * @param array $creds Array defaulted null use to specify custom DB credentials
	 * 
	 * 
	 *
	 * @return PDO created using specified or default DB credentials
	 */
	public static function fetchDB($creds = null)
	{
		if(!$creds) $creds = DinklyDataConfig::getDBCreds();

		$db = new PDO(
				"mysql:host=".$creds['host'].";dbname=".$creds['name'],
				$creds['user'],
				$creds['pass']
		);

		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $db;
	}

	/**
	 *  Cleans up quotation marks of variables to be used in DB query
	 *
	 * @param PDO $db PDO object which will be queried 
	 * @param mixed $variable mixed data type to be prepared for query 
	 * 
	 *
	 * @return string containing value of $variable prepared for query
	 */
	public static function sanitize($db, $variable)
	{
		$output = $db->quote($variable);
		return str_replace("'", "", $variable);
	}

	/**
	 *  Drop table from database completely if it exists
	 *
	 * @param string $schema String name of schema containing model
	 * @param string $model_name String name of model yaml file 
	 *
	 * @return bool false if table does not exist, true if table dropped
	 */
	public static function dropTable($schema, $model_name, $override_database_name = null)
	{
		if(!DinklyDataConfig::setActiveConnection($schema)) { return false; }

		//Use the proper DB credentials, or apply a passed-in override
		$creds = DinklyDataConfig::getDBCreds();
		$name = $creds['name'];

		//Connect to the target db
		$creds['name'] = $name;
		$db = self::fetchDB($creds);

		//Craft the sql
		$table_name = self::sanitize($db, Dinkly::convertFromCamelCase($model_name));
		$sql = "DROP TABLE IF EXISTS " . $table_name;

		//Drop the table
		$db->exec($sql);

		return true;
	}

	/**
	 *  Build database table based on given parameters
	 *
	 * @param string $schema: name of the database schema to refer to
	 * @param string $model_name: name of the model to build
	 * @param string $plugin_name: name of the plugin where the model lives
	 * @param string $model_yaml (optional): if passed, will override the automatic yaml parsing on the model based on the model name
	 * @param bool $verbose_output (optional): how chatty would you like the build to be?
	 * @param string $override_database_name (optional): if passed, this will override the name of the database as it appears in config/db.yml
	 *
	 * @return bool false if table does not exist, true if table dropped
	 */
	public static function buildTable($schema, $model_name, $plugin_name = null, $model_yaml = null, $verbose_output = true, $override_database_name = null)
	{
		if(!$model_yaml)
		{
			$model_yaml = self::parseModelYaml($schema, $model_name, $plugin_name, $verbose_output);
		}

		if(!$model_yaml) { return false; }

		if(!DinklyDataConfig::setActiveConnection($schema)) { return false; }

		//Use the proper DB credentials, or apply a passed-in override
		$creds = DinklyDataConfig::getDBCreds();

		$name = $creds['name'];
		if($override_database_name) { $name = $override_database_name; }

		//Create database if it doesn't exist
		self::createDb($name, $creds);

		//Connect to the target DB
		$creds['name'] = $name;
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
					$sql .= '`' . $sanitized_col_name . '` ' . $sanitized_col_type;

					if(isset($column[$col_name]['length']))
					{
						$sql .= ' ('.$column[$col_name]['length'].')';
					}
					else if($sanitized_col_type == 'varchar' && !isset($column[$col_name]['length']))
					{
						throw new Exception($table_name . ' - ' . $sanitized_col_name . ' - length required.');
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

	/**
	 *  Fetch all models under a specific schema folder
	 *
	 * @param string $schema: name of the database schema to refer to
	 *
	 * @return array of all models names without file extension
	 */
	public static function getAllModels($schema)
	{
		$model_names = array();
		$schema_path = $_SERVER['APPLICATION_ROOT'] . "config/schemas/" . $schema . "/";

		if(file_exists($schema_path))
		{
			$all_files = scandir($schema_path);	
			$model_names = array();
			
			foreach($all_files as $file)
			{
				if($file != '.' && $file != '..' && stristr($file, '.yml'))
				$model_names[] = str_replace('.yml', '', $file);
			}	
		}
		
		return $model_names;
	}

	public static function getAllPluginModels($plugin_name, $schema)
	{
		$model_names = array();
		$schema_path = $_SERVER['APPLICATION_ROOT'] . "plugins/" . $plugin_name . "/config/schemas/" . $schema . "/";

		if(file_exists($schema_path))
		{
			$all_files = scandir($schema_path);	

			$model_names = array();
			foreach($all_files as $file)
			{
				if($file != '.' && $file != '..' && stristr($file, '.yml'))
				$model_names[] = str_replace('.yml', '', $file);
			}
		}

		return $model_names;
	}

	public static function findPluginSchemas($plugin_name = null)
	{
		$plugin_names = array();
		$plugin_schemas = array();

		//Scan for any plugins to build
		if(!$plugin_name)
		{
			$plugins = scandir("plugins/");

			foreach($plugins as $plugin)
			{
				if($plugin != '.' && $plugin != '..')
				$plugin_names[] = $plugin;
			}
		}
		else
		{
			$plugin_names[] = $plugin_name;
		}

		//Search through for plugin schemas
		if($plugin_names != array())
		{
			foreach($plugin_names as $p)
			{
				$plugin_folders = scandir("plugins/" . $p . "/config/schemas/");

				foreach($plugin_folders as $f)
				{
					//Keep track of the plugin name and its schemas
					$plugin_schemas[$p] = $f;
				}
			}
		}

		return $plugin_schemas;
	}

	/**
	 *  Build all models under all of the schemas insert missing fields if wanted
	 *
	 * @param string $schema (optional): name of the database schema to refer to, else all will be built
	 * @param boo $insert_sql (optional): make true to insert missing fields from models
	 *
	 * 
	 */
	public static function buildAllModels($schema = null, $insert_sql = false, $plugin_name = false)
	{
		$schema_names = array();
		$plugin_schemas = self::findPluginSchemas($plugin_name);
		$is_plugin_schema = false;

		//No schema passed, search everywhere, build everything
		if(!$schema && !$plugin_name)
		{
			//Start with the basics
			$all_folders = scandir($_SERVER['APPLICATION_ROOT'] . "config/schemas/");

			foreach($all_folders as $folder)
			{
		  		if(substr($folder, 0, 1) != '.')
		    	$schema_names[] = $folder;
			}
		}
		else if(!$plugin_name)
		{
			$schema_names[] = $schema;
		}

		if($schema_names != array() && !$plugin_name)
		{
			foreach($schema_names as $schema)
			{
				$model_names = self::getAllModels($schema);

				if($model_names != array())
				{
					foreach($model_names as $model)
					{
						self::buildModel($schema, $model);
					}

					if($insert_sql)
					{
						self::addMissingModelsToDb($schema, null, true);
						self::addMissingModelFieldsToDb($schema, null, true);
					}
				}
			}
		}

		if($plugin_schemas != array() && $plugin_name)
		{
			foreach($plugin_schemas as $plugin_name => $plugin_schema)
			{
				if($plugin_name == $plugin_name)
				{
					$model_names = self::getAllPluginModels($plugin_name, $plugin_schema);

					foreach($model_names as $model)
					{
						self::buildModel($schema, $model, $plugin_name);
					}

					if($insert_sql)
					{
						self::addMissingModelsToDb($schema, $plugin_name, true);
						self::addMissingModelFieldsToDb($schema, $plugin_name, true);
					}
				}
			}
		}
	}

	/**
	 *  Load a specific fixture to populate DB table
	 *
	 * @param string $set: folder name of fixtures you would like to load
	 * @param string $model_name: name model fixture to be parsed
	 * @param bool $truncate (optional): truncate the table if set to true, or append records if false
	 * @param bool $verbose_output (optional): how chatty would you like the build to be?
	 * @param string $override_database_name (optional): if passed, this will override the name of the database as it appears in config/db.yml
	 *
	 * @return bool true if loaded successfully, false if load fails
	 */
	public static function loadFixture($set, $model_name, $plugin_name = null, $truncate = true, $verbose_output = true, $override_database_name = null)
	{
		//Use the proper DB credentials, or apply a passed-in override
		$creds = DinklyDataConfig::getDBCreds();
		if($override_database_name)
		{
			$creds['name'] = $override_database_name;
			DinklyDataConfig::setActiveConnection($creds);
		}

		//Create database if it doesn't exist
		$db = self::fetchDB($creds);

		$file_path = null;
		if($plugin_name)
		{
			$file_path = $_SERVER['APPLICATION_ROOT'] . "plugins/" . $plugin_name . "/config/fixtures/" . $set . "/" . $model_name . ".yml";
		}
		else
		{
			$file_path = $_SERVER['APPLICATION_ROOT'] . "config/fixtures/" . $set . "/" . $model_name . ".yml";
		}

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
					//Automatically set created date if none was passed
					if($col_name == 'created_at' && $value == "") { $value = date('Y-m-d G:i:s'); }
					
					$set_field = 'set' . Dinkly::convertToCamelCase($col_name, true);
					$model->$set_field($value);
				}
				$model->save();
			}

			if($verbose_output) { echo "success!\n"; }

			return true;
		}
	}
	
	/**
	 *  Load all fixture to populate DB table
	 *
	 * @param string $set: folder name of fixtures you would like to load
	 * @param bool $verbose_output (optional): how chatty would you like the build to be?
	 *
	 * @return bool true if loaded successfully, false if load fails
	 */
	public static function loadAllFixtures($set, $plugin_name = null, $truncate = false, $verbose = true)
	{
		$path = null;

		if($plugin_name)
		{
			$path = $_SERVER['APPLICATION_ROOT'] . "plugins/" . $plugin_name . "/config/fixtures/" . $set;
		}
		else
		{
			$path = $_SERVER['APPLICATION_ROOT'] . "config/fixtures/" . $set;
		}

		if(!is_dir($path))
		{
			if($verbose)
			{
				echo "\nNo matching set of fixtures found for '" . $set . "'\n\n";
			}
			return false;
		}

		$all_files = array();

		if($plugin_name)
		{
			$all_files = scandir($_SERVER['APPLICATION_ROOT'] . "plugins/" . $plugin_name . "/config/fixtures/" . $set);
		}
		else
		{
			$all_files = scandir($_SERVER['APPLICATION_ROOT'] . "config/fixtures/" . $set);
		}

		$model_names = array();
		foreach($all_files as $file)
		{
			if($file != '.' && $file != '..' && stristr($file, '.yml'))
			$model_names[] = str_replace('.yml', '', $file);
		}

		foreach($model_names as $model)
		{
			self::loadFixture($set, $model, $plugin_name, $truncate, $verbose);
		}

		return true;
	}
}