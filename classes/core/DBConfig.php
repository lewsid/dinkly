<?php 

use Symfony\Component\Yaml\Yaml;

class DBConfig
{
  //Default to first connection in db.yml
  public static function loadDBCreds()
  {
    if(!isset($_SESSION['dinkly']['db_creds']))
    {
      $db_creds = Yaml::parse($_SERVER['APPLICATION_ROOT'] . "config/db.yml");  
      $_SESSION['dinkly']['db_creds'] = $db_creds;
      $first_connection = key($db_creds);
      self::setActiveConnection($first_connection);
    }
  }

  public function hasConnection($connection_name)
  {
    if(!isset($_SESSION['dinkly']['db_creds']))
    {
      DBConfig::loadDBCreds();
    }
    return array_key_exists($connection_name, $_SESSION['dinkly']['db_creds']);
  }

  public function setActiveConnection($connection_name)
  { 
    if(self::hasConnection($connection_name))
    {
      $_SESSION['dinkly']['db_creds']['active_db'] = $connection_name;
      return true;
    }
    return false;
  }

  public static function getDBCreds()
  {
    if(!isset($_SESSION['dinkly']['db_creds']))
    {
      DBConfig::loadDBCreds();
    }

    $active_db = $_SESSION['dinkly']['db_creds']['active_db'];

    return $_SESSION['dinkly']['db_creds'][$active_db];
  }
  
  public static function testDB()
  {
    $dbh = new DBHelper(self::getDBCreds());
    
    return $dbh->getStatus();
  }
}

