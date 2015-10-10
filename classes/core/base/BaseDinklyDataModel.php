<?php 
/**
 * BaseDinklyDataModel
 *
 * 
 *
 * @package    Dinkly
 * @subpackage CoreClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

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

abstract class BaseDinklyDataModel extends DinklyDataConnector
{
	protected $db;
	protected $dbTable;
	protected $isNew;
	protected $regDirty = array();
	protected $registry = array();

	/**
	 * Connect with database and form empty data model
	 *
	 * @param PDO $db PDO object for custom DB connection, default DB when left empty
	 * 
	 * @return Makes DB connection and Constructs null model from registry
	 */
	public function __construct($db = null)
	{
		if(!$db) $this->db = self::fetchDB();
		else $this->db = $db;

		$this->isNew = true;
		
		foreach($this->getRegistry() as $element) { $this->$element = NULL; }
	}

	/**
	 * Initialize peer object of specific class
	 *
	 * @param int $id Integer value used in DB query to retrieve object
	 * 
	 * @return hydrates object and returns true if exist in DB or returns false if not found
	 */
	public function init($id)
	{
		if(!$this->db) { throw New Exception("Unable to perform init without a database object"); }

		$query = $this->getSelectQuery() . " where id=" . $this->db->quote($id);
		$result = $this->db->query($query)->fetchAll();
				
		if($result != array())
		{
			$this->hydrate($result, true);
			return true;
		}
		return false;

	}

	/**
	 * Initialize object with values other than id
	 *
	 * @param array $properties Array of class property names and values to filter on 
	 * 
	 * @return hydrates object and returns true if exist in DB or returns false if not found
	 */
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
				$where .= ' AND `' . $col . '` = ' . $this->db->quote($value); 
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

	/**
	 * Retrieve all existing properties of an object
	 *
	 * 
	 * @return Array of all value keys of an object using registry
	 */
	public function toArray()
 	{
 		$array = array();
 		foreach($this->getRegistry() as $key => $element)
 		{
 			$array[$key] = $this->$element;
 		}
 		return $array;
 	}

	/**
	 * Update or create record of an object
	 *
	 * @param bool $force_insert Boolean defaulted to false 
	 *
	 * @return changes modified fields of an object or inserts new record if param set true
	 */
	public function save($force_insert = false)
	{
		if(!$this->isNew && !$force_insert) { return $this->update(); }
		else { return $this->insert(); }
	}

	/**
	 * Retrieve sql select query with all DB properties returned
	 *
	 * @param $append_table_prefix boolean set true to return column names with table appended
	 *			which is pretty handy for joins
	 *
	 * @return String to be used as query on database for object
	 */
	public function getSelectQuery($append_table_prefix = false)
	{ 
		$columns = $this->getColumns();

		if($append_table_prefix)
		{
			$temp_columns = array();

			foreach($columns as $col)
			{
				$temp_columns[] = $this->getDBTable() . '.' . $col;
			}

			$columns = $temp_columns;
		}
		
		return "select " . implode(", ", $columns) . " from " . $this->getDBTable();
	}

	/**
	 * meant to be overloaded by child objects to attach any related objects following the hydrate
	 *
	 *
	 */
	public function attach() { }

	/**
	 * Fills all properties of an object with specific values
	 *
	 * @param array $full_result Array values and properties from DB
	 * @param bool $hasDB Boolean defaulted to false to verify DB connection
	 *
	 * @return bool of true after instantiating objects record results
	 */
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

	/**
	 * Delete a database object
	 *
	 * @return bool true on success or false on failure
	 * @throws Exception if unable to perform delete of object
	 */
	public function delete()
	{
		if(!$this->db) { throw New Exception("Unable to perform delete without a database object"); }

		$reg = $this->getRegistry();
		$is_valid = false;
		$query = "delete from " . $this->getDBTable() . " where id = " . $this->db->quote($this->Id);

		return $this->db->exec($query);
	}

	/**
	 * Update an existing database object
	 *
	 * @return bool true on success of query or no changes to be made or false on failed DB execution
	 * @throws Exception if unable to perform update of object
	 */
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

		$primaryKey = $this->getPrimaryKey();
		$query .= " where " . $primaryKey . "='" . $this->{Dinkly::convertToCamelCase($primaryKey, true)} . "'";

		if($is_valid) { return $this->db->exec($query); }
		else { return false; }
	}

	/**
	 * Insert a database object
	 *
	 * @return bool true on success or false on failure
	 * @throws Exception if unable to perform insertion of object
	 */
	protected function insert()
	{
		if(!$this->db) { throw New Exception("Unable to perform insert without a database object"); }

		//Automatically set created_at, if it exists on this object
		if(property_exists($this, 'CreatedAt'))
		{
			if($this->getCreatedAt() == '' || $this->getCreatedAt() == '00-00-0000 00:00:00' 
				|| $this->getCreatedAt() == null)
			{
				$this->setCreatedAt(date('Y-m-d H:i:s'));
			}
		}

		//Automatically set updated_at, if it exists on this object
		if(property_exists($this, 'UpdatedAt'))
		{
			if($this->getUpdatedAt() == '' || $this->getUpdatedAt() == '00-00-0000 00:00:00' 
				|| $this->getUpdatedAt() == null)
			{
				$this->setUpdatedAt(date('Y-m-d H:i:s'));
			}
		}

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

	/**
	 * Retrieve all properties an object can possess
	 *
	 * @return Array of all properties an object can have values for
	 * 
	 */
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

	/**
	 * Retrieve the registry of a given objects data model
	 *
	 * @return Array with keys of properties and values of properties in camel case
	 * 
	 */
	protected function getRegistry() { return $this->registry; }

	/**
	 * Retrieve the data table name of a given model
	 *
	 * @return String that is the name of the database table containing an object
	 * 
	 */
	protected function getDBTable() { return $this->dbTable; }

	/**
	 * Retrieve the primary key of a given model
	 *
	 * @return String that is the primary key of the database table containing an object
	 * 
	 */
	public function getPrimaryKey()
	{
		$sql = "SHOW KEYS FROM " . $this->getDBTable() . " WHERE Key_name = 'PRIMARY'";
		$result = $this->db->query($sql)->fetch();

		return $result['Column_name'];
	}

	/**
	 * Forces entire data model to refresh to completely wipe a record
	 *
	 */
	public function forceDirty()
	{
		foreach($this->getRegistry() as $key => $element)
		{
			$this->regDirty[$key] = true;
		}
	}

	/**
	 * Retrieve the database connection 
	 *
	 * @return PDO object based on DB credentials
	 * 
	 */
	public function getDB() { return $this->db; }

	/**
	 * Set the database connection 
	 * @param PDO $value PDO containing new DB credentials
	 */
	public function setDB($value) { $this->db = $value;}

	/**
	 * Check if an object is newly created or already existed
	 *
	 * @return bool true if new object or false if existing 
	 */
	public function isNew() { return $this->isNew; }
	
	/**
	 * Construct Dynamic getters and setters for objects
	 * @param string $method String containing function call
	 * @param array $arguments Array of values to be set
	 *
	 * @return bool true if both property and method exist
	 * @throws Exception when calling get/set on property/ method that does not exist
	 */
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
			if($this->$property != $arguments[0])
			{
				$this->$property = $arguments[0];

				//Set variable dirty so we know to add it to any queries
				foreach($this->getRegistry() as $key => $element)
				{
					if($element == $property) { $this->regDirty[$key] = true; }
				}
			}

			return true;
		
		}
		elseif (!$match && $prefix == "set") { throw new Exception("Setting a variable that does not exist: var:$property value: $arguments[0]"); }
		else { throw new Exception("Calling a get/set method that does not exist: $property"); }
	}
}