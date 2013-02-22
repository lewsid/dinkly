<?php

use Symfony\Component\Yaml\Yaml;

class FixtureLoader
{
  /* 
    $truncate will truncate the table if set to true, or append records if false
  */
	public static function loadFixture($model_name, $truncate = true)
	{
    $file_path = $_SERVER['APPLICATION_ROOT'] . "config/fixtures/" . $model_name . ".yml";
		
    echo "Attempting to parse '" . $model_name . "' fixture yaml...";
    $fixture = Yaml::parse($file_path);
    $model_name = $bundle_name = null;

    if(isset($fixture['table_name']))
    {
      $model_name = Dinkly::convertToCamelCase($fixture['table_name'], true);
      echo "success!\n";
    }
    else return false;

    if(isset($fixture['records']))
    {
      if($truncate)
      {
        echo "Truncating '" . $fixture['table_name']. "'...";
        $db = new DBHelper(DBConfig::getDBCreds());
        $db->Update("truncate table " . $fixture['table_name']);
        echo "success!\n";
      }

      $count = sizeof($fixture['records']);
      echo "Inserting " . $count . " record(s) into table '" . $fixture['table_name'] . "'";
      foreach($fixture['records'] as $pos => $record)
      {
        echo "...";
        $model = new $model_name();
        foreach($record as $col_name => $value)
        {
          $set_field = 'set' . Dinkly::convertToCamelCase($col_name, true);
          $model->$set_field($value);
        }
        $model->save();
      }
      echo "success!\n";

      return true;
    }
	}

  public static function loadAll()
  {
    $all_files = scandir($_SERVER['APPLICATION_ROOT'] . "config/fixtures/");
    
    $model_names = array();
    foreach($all_files as $file)
    {
      if($file != '.' && $file != '..' && stristr($file, '.yml'))
        $model_names[] = str_replace('.yml', '', $file);
    }

    foreach($model_names as $model)
    {
      self::loadFixture($model);
    }
  }
}