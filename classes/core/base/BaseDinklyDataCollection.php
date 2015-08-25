<?php
/**
 * BaseDinklyDataCollection
 *
 * Children of this class should contain only static functions that return arrays
 *
 * @package    Dinkly
 * @subpackage CoreClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

abstract class BaseDinklyDataCollection extends DinklyDataModel
{
	/**
	 * Retrieve all objects
	 *
	 * @param PDO object $db Optional PDO object for recycling existing connections
	 *
	 * @return Array of objects or false if not found
	 */
	public static function getAll($db = null)
	{
		$peer_class = preg_replace('/Collection$/', '', get_called_class());
		if(class_exists($peer_class))
		{
			$peer_object = new $peer_class();

			if($db == null) { $db = self::fetchDB(); }

			$query = $peer_object->getSelectQuery();

			return self::getCollection($peer_object, $query, $db);
		}
		return false;
	}

	/**
	 * Retrieve all objects matching array of passed property/value pairs
	 *
	 * @param PDO object $db Optional PDO object for recycling existing connections
	 *
	 * @param array $properties Array of class property names and values to filter on
	 *
	 * @param array $order Array of class property names to order results by
	 *
	 * @param array $direction String Order 'asc' or 'desc' - Only does something if an array
	 *						was passed for $order parameter.
	 *
	 * @param array $limit Array containing one or two elements. If a single element is passed,
	 *					   it will return a dataset of that size or less. If two elements are
	 *					   set, the first will be an offset and the second a range
	 *        examples: The following would return a thousand records following the first 100:
	 *        		    array(0 => 100, 1 => 1000)
	 *
	 *					The following would simply return 700 records:
	 *					array(0 => 700)
	 *
	 *
	 * @return Array of matching objects or false if not found
	 */
	public static function getWith($db = null, $properties, $order = array(), $direction = 'asc', $limit = array())
	{
		//Dynamically find class name and ensure it exists
		$peer_class = preg_replace('/Collection$/', '', get_called_class());
		if(class_exists($peer_class) && $properties != array())
		{
			//Build the basic select query
			$peer_object = new $peer_class();

			if($db == null) { $db = self::fetchDB(); }

			$cols = array();
			foreach($properties as $property => $value)
			{
				$col_name = Dinkly::convertFromCamelCase($property);
				if(array_key_exists($col_name, $peer_object->registry)) $cols[$col_name] = $value;
			}

			$where = '';
			$is_valid = false;
			foreach($cols as $col => $value)
			{
				if(is_array($value) && count($value) > 1)
				{
					$in_string = '';
					foreach($value as $temp)
					{
						$in_string .= $db->quote($temp) . ',';
					}
					$in_string = trim($in_string, ',');
					$where .= ' AND `' . $col . '` IN (' . $in_string . ')';
					$is_valid = true;
				}
				elseif(is_array($value) && count($value) === 1)
				{
					$where .= ' AND `' . $col . '` = ' . $db->quote($value[0]);
					$is_valid = true;
				}
				elseif(!is_array($value))
				{
					$where .= ' AND `' . $col . '` = ' . $db->quote($value);
					$is_valid = true;
				}
			}

			if($is_valid) { $where = ' where ' . trim($where, ' AND'); }

			//Enforce an order on the results
			$is_valid = false;
			if($order != array())
			{
				$chunk = ' order by ';
				foreach($order as $p)
				{
					$col_name = Dinkly::convertFromCamelCase($p);
					if(array_key_exists($col_name, $peer_object->registry))
					{
						$chunk .= $col_name . ', ';
						$is_valid = true;
					}
				}

				if($is_valid)
				{
					$where .= rtrim($chunk, ', ');

					if($direction == 'asc') { $where .= ' ASC'; }
					else if($direction == 'desc') { $where .= ' DESC'; }
				}
			}

			//Enforce a limit on the results
			$is_valid = false;
			if($limit != array())
			{
				$chunk = " limit ";
				if(is_numeric($limit[0]))
				{
					$chunk .= $limit[0];
					$is_valid = true;
				}
				if(isset($limit[1]))
				{
					if(is_numeric($limit[1]))
					{
						$chunk .= ', ' . $limit[1];
					}
				}

				if($is_valid) { $where .= $chunk; }
			}

			return self::getCollection($peer_object, $peer_object->getSelectQuery() . $where, $db);
		}
	}

	/**
	 * Retrieve all objects of specified object given a specific query
	 *
	 * @param object $peer_object Object from which to get class of collection objects
	 * @param string $query String to filter database query on
	 * @param PDO object $db Optional PDO object for recycling existing connections
	 *
	 * @return Array of matching objects filtered on query
	 */
	protected static function getCollection($peer_object, $query, $db = null)
	{
		if($db == null) { $db = self::fetchDB(); }

		$results = $db->query($query)->fetchAll();

		if($results != array() && $results != NULL)
		{
			$arrObject = array();
			$i = 0;
			foreach($results as $result)
			{
				$class_name = get_class($peer_object);
				$tempObject = new $class_name($db);
				$tempObject->hydrate($result, true);

				$arrObject[$i] = $tempObject;

				$i++;
			}

			return $arrObject;
		}
	}
}