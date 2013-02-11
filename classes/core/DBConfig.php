<?php 

use Symfony\Component\Yaml\Yaml;

class DBConfig
{
  public static function getDBCreds()
  {
    $db_creds = null;

    if(!isset($_SESSION['dinkly']['db_creds']))
    {
      $db_creds = Yaml::parse($_SERVER['APPLICATION_ROOT'] . "config/db.yml");  
      $_SESSION['dinkly']['db_creds'] = $db_creds;
    }
    else { $db_creds = $_SESSION['dinkly']['db_creds']; }
    
    return $db_creds;
  }
  
  public static function testDB()
  {
    $dbh = new DBHelper(self::getDBCreds());
    
    return $dbh->getStatus();
  }
}

