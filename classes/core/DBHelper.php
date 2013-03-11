<?php 

/*

  Database Helper Class
  Christopher Lewis (lewsid@lewsid.com)
  
  Supported Public Functions:
  
  DBHelper($arrKeys, $blPersist = 1)
    -constructor
    -$arrKeys = array('DB_HOST' => <host name>, 'DB_USER' => <username>, 'DB_PASS' => <password>, 'DB_NAME' => 'database name')
    
  doKill()
    -kill non-persistent connection
    
  getError($strMode = "all")
    -Retrieve last error
    -Possible values for $strMode include "is_err", "err_num", "err_detail", "err_query", and "all"
    
  Insert($strInsert)
    -SQL Insert
    -Returns ID of inserted row; false otherwise
    
  Delete($strDelete)
    -SQL Delete
    -Returns affected rows; false otherwise
    
  Select($strSelect)
    -SQL Select
    -Fills result array on success; false otherwise. Returns false if empty array is returned.
    
  getResult()
    -Getter for result array
    
  getStatus()
    -Getter for connection status

*/

class DBHelper
{
  private $dbConnection; //store link to connection
  private $intError;     //mysql error number
  private $strError;     //mysql error detail
  private $blError;      //did the last query cause an error?
  
  public $arrResult;     //stores results from database queries
  public $arrDebug;      //keeps track of script and line info in the event of an error
  public $blStatus;      //connection status

  /* 
    Primary Constructor

    Defaults to unknown application and a peristant connection. If you decide to go non-perist, make sure
    to call doKill() to close it back off.
  */
  public function DBHelper($arrKeys, $blPersist = 1)
  {
    $this->initDBHelper($arrKeys['DB_HOST'], $arrKeys['DB_USER'], $arrKeys['DB_PASS'], $arrKeys['DB_NAME'], $blPersist);
  }
  
  private function initDBHelper($strHost, $strUser, $strPass, $strDatabase, $blPersist)
  {
    $this->blStatus = false;
    
    if($blPersist) { $this->dbConnection = mysql_connect($strHost, $strUser, $strPass); }
    else { $this->dbConnection = mysql_pconnect($strHost, $strUser, $strPass); }
    
    $blStatus = $this->selectDB($strDatabase);
  }

  public function selectDB($db_name)
  {
    if(mysql_select_db($db_name, $this->dbConnection)) { return true; }
    return false;
  }
  
  /* Kill connection */
  public function doKill() { mysql_close($this->dbConnection); }
  
  /* Return last error in various formats. Default return is an array with all information. */
  public function getError($strMode = "all")
  {
    switch($strMode)
    {
      case "is_err":
        return $this->blError;
        break;
      case "err_num":
        return $this->intError;
        break;
      case "err_detail":
        return $this->strError;
        break;
      case "err_query":
        return $this->strErrorQuery;
        break;
      case "all":
        return array("is_err" => $this->blError, 
          "err_num"           => $this->intError, 
          "err_detail"        => $this->strError,
          "err_query"         => $this->strErrorQuery);
        break; 
    }
  }
  
  /* Intended to be run following an error; loads object vars with error info */
  private function setError($strQuery)
  {
    $this->blError = true;
    $this->intError = mysql_errno();
    $this->strError = mysql_error();
    $this->strErrorQuery = $strQuery;
  }
  
  /* SQL insert; returns id on success */
  public function Insert($strInsert)
  {
    $this->blError = false;
    $this->arrDebug = debug_backtrace();
        
    if(mysql_query($strInsert, $this->dbConnection)) { return mysql_insert_id($this->dbConnection); }
    else
    {
      $this->setError($strInsert);  
      return false;
    }
  }

  /* SQL insert; returns id on success */
  public function CreateTable($strCreate)
  {
    $this->blError = false;
    $this->arrDebug = debug_backtrace();
        
    if(mysql_query($strCreate, $this->dbConnection)) { return true; }
    else
    {
      $this->setError($strInsert);  
      return false;
    }

    return false;
  }
  
  /* SQL update; returns array of affected rows on success - shared with Delete() */
  public function Update($strUpdate)
  {
    $this->blError = false;
    $this->arrDebug = debug_backtrace();
  
    if(mysql_query($strUpdate, $this->dbConnection)) { return mysql_affected_rows($this->dbConnection); }
    else
    {
      $this->setError($strUpdate);
      return false;
    }
  }
  
  /* SQL delete; same damn function as Update() but here for the sake of clarity */
  public function Delete($strDelete) { $this->Update($strDelete); }
  
  /* Retrieve records from database. On success returns a multi-dimensional array with all the information your heart could desire. */
  public function Select($strSelect)
  {
    $this->arrResult = NULL;
    $this->arrDebug = debug_backtrace();
  
    if($Result = mysql_query($strSelect, $this->dbConnection))
    {
      $i = 0;
      while($row = mysql_fetch_array($Result, MYSQL_ASSOC))
      {        
        $this->arrResult[$i] = $row;
        $i++;
      }
      
      if(mysql_num_rows($Result) > 0) { return $this->arrResult; }
      else { return false; }
    }
    else
    {
      $this->setError($strSelect);
      return false;
    }
    
  }
  
  /* Result getter */
  public function getResult() { return $this->arrResult; }
  
  /* Connection status getter */
  public function getStatus() { return $this->blStatus; }
}