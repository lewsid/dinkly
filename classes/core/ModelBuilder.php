<?php

use Symfony\Component\Yaml\Yaml;

class ModelBuilder
{
  public static function parseModelYaml($model_name)
  {
    $file_path = $_SERVER['APPLICATION_ROOT'] . "config/schema/" . $model_name . ".yml";  
    echo "Attempting to parse '" . $model_name . "' schema yaml...";
    $yaml = Yaml::parse($file_path); 

    $table_name = $yaml['table_name'];

    if(strlen($table_name) < 2)
    {
      echo "Failed to read a valid table name\n";
      return false;
    }
    else
    {
      echo "Success!\n";
      return $yaml;
    }
  }

	public static function buildModel($model_name, $raw_model = false)
	{
    if(!$raw_model)
    {
      $raw_model = self::parseModelYaml($model_name);
      if(!$raw_model) { return false; }
    }

    $table_name = $raw_model['table_name'];

    if(isset($raw_model['registry']))
    {
      $base_file = $_SERVER['APPLICATION_ROOT'] . "classes/models/base/Base" . $model_name . ".php";
      $base_bundle_file = $_SERVER['APPLICATION_ROOT'] . "classes/models/base/Base" . $model_name . "Bundle.php";

      echo "Attempting to create/write base '" . $model_name . "' models...\n";

      //create base model
      if($fp = fopen($base_file, 'w+'))
      {
        fwrite($fp, '<?php' . PHP_EOL . PHP_EOL);
        fwrite($fp, '# This is an auto-generated file. Please do not alter this file. Instead, make changes to the model file that extends it.');
        fwrite($fp, PHP_EOL . PHP_EOL);
        fwrite($fp, 'class Base' . $model_name . ' extends DBObject' . PHP_EOL . '{' . PHP_EOL);
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

      //create base bundle class
      if($fp = fopen($base_bundle_file, 'w+'))
      {
        fwrite($fp, '<?php' . PHP_EOL . PHP_EOL);
        fwrite($fp, '# This is an auto-generated file. Please do not alter it. Instead, make changes to the model file that extends it.');
        fwrite($fp, PHP_EOL . PHP_EOL);
        fwrite($fp, 'class Base' . $model_name . 'Bundle extends DBObjectBundle' . PHP_EOL . '{' . PHP_EOL);
        fwrite($fp, "\t" . 'public static function getAll()' . PHP_EOL . "\t" . '{' . PHP_EOL);
        fwrite($fp, "\t\t" . '$db = new DBHelper(DBConfig::getDBCreds());' . PHP_EOL);
        fwrite($fp, "\t\t" . '$peer_object = new ' . $model_name . ';' . PHP_EOL);
        fwrite($fp, "\t\t" . 'if($db->Select($peer_object->getSelectQuery(), true))' . PHP_EOL);
        fwrite($fp, "\t\t" . '{' . PHP_EOL);
        fwrite($fp, "\t\t\t" . '$peer_object->setDB($db);' . PHP_EOL);
        fwrite($fp, "\t\t\t" . 'return self::handleResults($peer_object);' . PHP_EOL);
        fwrite($fp, "\t\t" . '}' . PHP_EOL);
        fwrite($fp, "\t" . '}' . PHP_EOL . '}');

        fclose($fp);

        echo "Successfully created base bundle model!\n";
      }
      else
      {
        echo "failed! Aborting\n";
        return false;
      }

      //second, create the extensible model files, if one doesn't already exist (we never overwrite this one)
      $custom_file = $_SERVER['APPLICATION_ROOT'] . "classes/models/custom/" . $model_name . ".php";
      $custom_bundle_file = $_SERVER['APPLICATION_ROOT'] . "classes/models/custom/" . $model_name . "Bundle.php";

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

      if(!file_exists($custom_bundle_file))
      {
        $fp = fopen($custom_bundle_file, 'w+');
        fwrite($fp, '<?php' . PHP_EOL . PHP_EOL);
        fwrite($fp, 'class ' . $model_name . 'Bundle extends Base' . $model_name . 'Bundle' . PHP_EOL . '{' . PHP_EOL . PHP_EOL);
        fwrite($fp, '}' . PHP_EOL . PHP_EOL);
        fclose($fp);
      }
      else echo "Custom '" . $model_name . "' bundle class already exists - skipping.\n";

      return true;
    }
	}

  public static function buildTable($model_name, $model_yaml = false, $target_connection = null)
  {
    if($target_connection)
    {
      if(DBConfig::setActiveConnection($target_connection))
      {
        echo "Using database '" . $target_connection . "'...\n";
      }
      else
      {
        echo "Invalid database name. Check db.yml for a matching database.\n";
        return false;
      }
    }

    if(!$model_yaml)
    {
      $model_yaml = self::parseModelYaml($model_name);
      if(!$model_yaml) { return false; }
    }

    $db = new DBHelper(DBConfig::getDBCreds());

    echo "Creating/Updating MySQL for table " . $model_yaml['table_name'] . "...";

    $db->Update("DROP TABLE IF EXISTS " . $model_yaml['table_name']);

    $sql = "CREATE TABLE " . mysql_real_escape_string($model_yaml['table_name']) . " (";

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
          $sql .= "`" . $col_name . "` " . mysql_real_escape_string($column[$col_name]['type']);
          if(isset($column[$col_name]['length'])) { $sql .= " (" . mysql_real_escape_string($column[$col_name]['length']) . ")"; }
          if(!$column[$col_name]['allow_null']) { $sql .= " NOT NULL"; }
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

    if($db->CreateTable($sql))
    {
      echo "...success!\n";
      return true;
    }
    else { echo "\n\n" . $sql . "\n\n"; print_r($db->getError()); echo "\n"; }

    return false;
  }

  public static function buildAll($insert_sql = false, $target_connection = null)
  {
    $all_files = scandir($_SERVER['APPLICATION_ROOT'] . "config/schema/");
    
    $model_names = array();
    foreach($all_files as $file)
    {
      if($file != '.' && $file != '..' && stristr($file, '.yml'))
        $model_names[] = str_replace('.yml', '', $file);
    }

    foreach($model_names as $model)
    {
      $model_yaml = self::parseModelYaml($model);

      if(isset($model_yaml['table_name']))
      {
        echo "success!\n";
      }
      else return false;
      
      self::buildModel($model, $model_yaml);

      if($insert_sql)
      {
        self::buildTable($model, $model_yaml, $target_connection);
      }
    }
  }
}