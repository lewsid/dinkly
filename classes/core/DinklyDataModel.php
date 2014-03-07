<?php 

/****************************************************************************************************************

	CHILD CLASS EXAMPLE:
		
		class User extends DinklyDataModel
		{
			public $registry = array(
				'id'              => 'ID',
				'created'         => 'Created',
				'modified'        => 'Modified',
				'is_deleted'      => 'Deleted',
				'first_name'      => 'FirstName',
				'last_name'       => 'LastName',
			);
			
			public $dbTable = 'users';
		}
		
	CHILD CLASS USAGE EXAMPLE:
		
		$user = new User();
		$user->init(1);
		echo $user->getCreated() 

***************************************************************************************************************/

abstract class DinklyDataModel extends DinklyDataConnector
{
	protected $db;
	protected $dbTable;
	protected $isNew;
	protected $regDirty = array();
	protected $registry = array();
	
	public function __construct($db = null)
	{
		if(!$db) $this->db = self::fetchDB();
		else $this->db = $db;

		$this->isNew = true;
		
		foreach($this->getRegistry() as $element) { $this->$element = NULL; }
	}

	public function init($id)
	{
		if(!$this->db) { throw New Exception("Unable to perform init without a database object"); }

		$Select = $this->getSelectQuery() . " where id=" . $this->db->quote($id);
		$result = $this->db->query($Select)->fetchAll();
				
		if($result != array())
		{
			$this->hydrate($result, true);
		}
	}

	/* Init object with properties other than id. Example: $user->initWith(array('Username' => $username)); */
	public function initWith($properties = array())
	{
		if(!$this->db) { throw New Exception("Unable to perform init without a database object"); }

		if($properties != array())
		{
			$cols = array();
			foreach($properties as $property => $value)
			{
				$col_name = Dinkly::convertFromCamelCase($property);
				if(array_key_exists($col_name, $this->registry)) $cols[$col_name] = $value;
			}

			$where = '';
			foreach($cols as $col => $value)
			{
				$where .= ' AND ' . $col . ' = ' . $this->db->quote($value); 
			}
			$where = ' where ' . trim($where, ' AND');

			$query = $this->getSelectQuery() . $where;
			$result = $this->db->query($query)->fetchAll();
					
			if($result != array())
			{
				$this->hydrate($result, true);
			}
			else return false;
		}
		else return false;
	}

	/* Return object properties as array */
	public function toArray()
 	{
 		$array = array();
 		foreach($this->getRegistry() as $key => $element)
 		{
 			$array[$key] = $this->$element;
 		}
 		return $array;
 	}
	
	/* Only manipulates fields that have been modified in some way */
	public function save($force_insert = false)
	{
		if(!$this->isNew && !$force_insert) { return $this->update(); }
		else { return $this->insert(); }
	}
	
	public function getSelectQuery()
	{ 
		return "select " . implode(", ", $this->getColumns()) . " from " . $this->getDBTable();
	}
	
	/* This is meant to be overloaded by child objects to attach any related objects following the hydrate. */
	public function attach() { }
	
	public function hydrate($full_result, $hasDB = false)
	{  
		$reg = $this->registry;
		
		if(isset($full_result[0]))
		{
			if(is_array($full_result[0])) { $contents = $full_result[0]; }  
			else { $contents = $full_result; }
		}
		else { $contents = $full_result; }
		
		foreach($contents as $key => $record)
		{
			if(isset($reg[$key]))
			{
				$this->$reg[$key] = $record;
			}
		}

		$this->attach();
		
		if(!$hasDB) { $this->db = NULL; }
		
		$this->isNew = false;

		return true;
	}

	public function delete()
	{
		if(!$this->db) { throw New Exception("Unable to perform delete without a database object"); }

		$reg = $this->getRegistry();
		$is_valid = false;
		$query = "delete from " . $this->getDBTable() . " where id = " . $this->db->quote($this->Id);
		return $this->db->exec($query);
	}
	
	protected function update()
	{
		if(!$this->db) { throw New Exception("Unable to perform update without a database object"); }

		$reg = $this->getRegistry();
		$is_valid = false;
		$query = "update " . $this->getDBTable() . " set ";
		
		$i = 1;
		foreach($this->getColumns() as $col)
		{
			if(array_key_exists($col, $this->regDirty))
			{
				if($i < sizeof($this->regDirty))
				{
					$query .= $col . "=" . $this->db->quote($this->$reg[$col]) . ", ";
					$is_valid = true;
				}
				else if(sizeof($this->regDirty) == 1 || $i == sizeof($this->regDirty))
				{
					$query .= $col . "=" . $this->db->quote($this->$reg[$col]);
					$is_valid = true;
				}
				$i++;
			}
		}
		
		$query .= " where id='" . $this->Id . "'";

		if($is_valid) { return $this->db->exec($query); }
		else { return true; }
	}
	
	protected function insert()
	{
		if(!$this->db) { throw New Exception("Unable to perform insert without a database object"); }

		$reg = $this->getRegistry();
		$is_valid = false;
		$query = "insert into " . $this->getDBTable() . " (";
		$values = "values (";
		
		$i = 1;
		foreach($this->getColumns() as $col)
		{
			if(array_key_exists($col, $this->regDirty))
			{
				if(sizeof($this->regDirty) == 1)
				{
					$query .= $col . ") values (" . $this->db->quote($this->$reg[$col]) . ")";
					$values = "";
					$is_valid = true;
				}
				else if($i < sizeof($this->regDirty))
				{
					$query .= $col . ", ";
					$values .= $this->db->quote($this->$reg[$col]) . ", ";
					$is_valid = true;
				}
				else
				{
					$query .= $col . ") ";
					$values .= $this->db->quote($this->$reg[$col]) . ") ";
				}
				$i++;
			}
		}
		
		$query .= $values;
		$this->db->exec($query);

		$this->Id = $this->db->lastInsertId();
		$this->isNew = false;
		
		return $this->Id;
	}
	
	protected function getColumns()
	{
		$reg = $this->getRegistry();
		$columns = array();
		
		$i = 0;
		foreach($reg as $col)
		{
			$columns[$i] = key($reg);
			next($reg);
			$i++;
		}
		return $columns;
	}
	
	protected function getRegistry() { return $this->registry; }
	
	protected function getDBTable() { return $this->dbTable; }

	//My current favorite function name, this forces the entire model to refresh
	public function forceDirty()
	{
		foreach($this->getRegistry() as $key => $element)
		{
			$this->regDirty[$key] = true;
		}
	}
	
	public function getDB() { return $this->db; }
	
	public function setDB($value) { $this->db = $value; }
	
	public function isNew() { return $this->isNew; }
	
	/* Borrowed (then reformatted) from: http://blog.josephwilk.net/snippets/dynamic-gettersetters-for-php.html */
	function __call($method, $arguments)
	{  
		//Is this a get or a set
		$prefix = strtolower(substr($method, 0, 3));
	
		//What is the get/set class attribute
		$property = substr($method, 3);
		
		//Did not match a get/set call
		if(empty($prefix) || empty($property))
		{
			throw New Exception("Calling a non get/set method that does not exist: $method");
		}
	
		//Check if the get/set parameter exists within this class as an attribute
		$match = false;
		foreach($this as $class_var => $class_var_value)
		{
			if(strtolower($class_var) == strtolower($property))
			{
				$property = $class_var;
				$match = true;
			}
		}
	
		//Get attribute
		if($match && $prefix == "get" && (isset($this->$property) || is_null($this->$property)))
		{
			return $this->$property;
		}
	
		//Set
		if($match && $prefix == "set")
		{
			$this->$property = $arguments[0];
			
			//Set variable dirty so we know to add it to any queries
			foreach($this->getRegistry() as $key => $element)
			{
				if($element == $property) { $this->regDirty[$key] = true; }
			}
			return true;
		
		}
		elseif (!$match && $prefix == "set") { throw new Exception("Setting a variable that does not exist: var:$property value: $arguments[0]"); }
		else { throw new Exception("Calling a get/set method that does not exist: $property"); }
	}
}