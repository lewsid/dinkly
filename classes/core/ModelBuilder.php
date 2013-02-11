<?php

use Symfony\Component\Yaml\Yaml;

class ModelBuilder
{
	public static function buildModel($model_name)
	{
    $file_path = $_SERVER['APPLICATION_ROOT'] . "config/schema/" . $model_name . ".yml";
		
    echo "Attempting to parse '" . $model_name . "' schema yaml...";
    $raw_model = Yaml::parse($file_path);
    $table_name = null;

    if(isset($raw_model['table_name']))
    {
      $table_name = $raw_model['table_name'];
      echo "success!\n";
    }
    else return false;

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

        foreach($raw_model['registry'] as $column)
        {
          $var_name = str_replace('_', ' ', $column);
          $var_name = ucwords(strtolower($var_name));
          $var_name = str_replace(' ', '', $var_name);
          fwrite($fp, "\t\t" . "'" . $column . "' => '" . $var_name . "'," . PHP_EOL);
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

  public static function buildAll()
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
      self::buildModel($model);
    }
  }
}