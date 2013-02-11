<?php

/****************************************************************************************************************

  CHILD CLASS EXAMPLE:

    class UserBundle extends DBObjectBundle
    {
      public static function getAll()
      {
        $db = new DBHelper(DBConfig::getDBCreds());
        $peer_object = new User();
        if($db->Select($peer_object->getSelectQuery(), true)) { return self::handleResults($peer_object); }
      }
    }
    
  CHILD CLASS USAGE EXAMPLE:
  
    $users = UserBundle::getAll();

***************************************************************************************************************/

abstract class DBObjectBundle extends DBObject
{ 
  protected static function getBundle($results, $peer_object)
  {
    if($results != array() && $results != NULL)
    {
      $arrObject = array();
      $i = 0;
      foreach($results as $result)
      {
        $class_name = get_class($peer_object);
        $tempObject = new $class_name($peer_object->getDB());
        $tempObject->hydrate($result, true);
        
        $arrObject[$i] = $tempObject;
        
        $i++;
      }
      return $arrObject;
    }
  }
  
  protected static function handleResults($peer_object)
  {
    $results = $peer_object->getDB()->arrResult;
    return self::getBundle($results, $peer_object);
  }
}