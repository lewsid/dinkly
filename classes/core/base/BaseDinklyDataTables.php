<?php

class BaseDinklyDataTables 
{
	/**
	* Server Side Processing for DataTables - Heavily inspired by https://github.com/DataTables/DataTables/tree/master/examples/server_side
	* Authors: Andrew Rousseau (andrewvt), Christopher Lewis (lewsid)
	*
	* Note: This is a modified version of the php code supplied by DataTables
	* Additional features:
	*  - Accommodates any kind of JOINs, in any quantity
	*  - Accomodates passing in additional where criteria string
	*
	* Kitchen Sink Example:
	*
	*	// In the Controller:
	*	public function loadUserDataTable()
	*	{
	*		$table_info = array('table_name' => 'dinkly_user', 'primary_key' => 'id');
	*		$joins = array();
	*		$joins[] = array('left', 'dinkly_user', 'user_affiliation', 'id', 'user_id');
	*		$joins[] = array('left', 'user_affiliation', 'congressional_district', 'congressional_district_id', 'id');
	*
	*		$columns = array(
	*			array('db' => 'username','dt' => 0, 'table' => 'dinkly_user'),
	*			array('db' => 'first_name',   'dt' => 1, 'table' => 'dinkly_user'),
	*			array('db' => 'last_name',  'dt' => 2, 'table' => 'dinkly_user'),
	*			array('db' => 'created_at',  'dt' => 3, 'table' => 'dinkly_user'),
	*			array('db' => 'district_code',  'dt' => 4, 'table' => 'congressional_district'),
	*			array('db' => 'id', 'dt' => 5, 'table' => 'user_referral', 'count' => 'true', 'label' => 'referral_count'),
	*			array('db' => 'id', 'dt' => 6, 'table' => 'dinkly_user', 
	*				'formatter' => function($d, $row) { return '<a target="_blank" href="/admin/user/detail/id/' . $d . '">view</a>'; }
	*			)
	*		);
	*
	*		echo json_encode(DinklyDataTables::doQuery($this->db, $_POST, $table_info, $columns, $joins));
	*	}
	*
	*	// The js for the table:
	*	<script type="text/javascript">
	*	$('#user-list').DataTable( {
	*	    "processing": true,
	*	    "serverSide": true,
	*	    "columns": [
	*	         null,
	*	         null,
	*	         null,
	*	         null,
	*	         null,
	*	         { "bSortable": false },
	*	         { "bSortable": false }
	*	       ],
	*	    "ajax": {
	*	      "url": "/admin/user/user_data_table",
	*	      "type": "POST"
	*	    }
	*	  });
	*	  </script>
	*/

	/**
	 * Create the data output array for the DataTables rows
	 *
	 *  @param  array $columns Column information array
	 *  @param  array $data    Data from the SQL get
	 *  @return array          Formatted data in a row based format
	 */
	static function genDataOutput($columns, $data)
	{
		$out = array();

		for($i = 0, $ien = count($data); $i < $ien; $i++)
		{
			$row = array();

			for($j = 0, $jen = count($columns); $j < $jen; $j++)
			{
				$column = $columns[$j];

				//Is there a formatter?
				if(isset($column['formatter']))
				{
					$row[$column['dt']] = $column['formatter']($data[$i][$column['db']], $data[$i]);
				}
				else if(isset($column['count']))
				{
					$row[$column['dt']] = $data[$i][$columns[$j]['label']];
				}
				else
				{
					$row[$column['dt']] = $data[$i][$columns[$j]['db']];
				}
			}

			$out[] = $row;
		}

		return $out;
	}

	/**
	 * Paging
	 *
	 * Construct the LIMIT clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @return string SQL limit clause
	 */
	static function constructLimitClause($request)
	{
		$limit = '';

		if(isset($request['start']) && $request['length'] != -1 )
		{
			$limit = "LIMIT " . intval($request['start']) . ", " . intval($request['length']);
		}

		return $limit;
	}

	/**
	 * Ordering
	 *
	 * Construct the ORDER BY clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL order by clause
	 */
	static function constructOrderClause($request, $columns, $primary_table)
	{
		$order = '';

		if(isset($request['order']) && count($request['order']))
		{
			$orderBy = array();
			$dtColumns = self::pluck($columns, 'dt', $primary_table);

			for($i = 0, $ien = count($request['order']); $i < $ien; $i++)
			{
				// Convert the column index into the column data property
				$columnIdx = intval($request['order'][$i]['column']);
				$requestColumn = $request['columns'][$columnIdx];

				$columnIdx = array_search($requestColumn['data'], $dtColumns);
				$column = $columns[$columnIdx];

				if($requestColumn['orderable'] == 'true')
				{
					$dir = $request['order'][$i]['dir'] === 'asc' ?
						'ASC' :
						'DESC';

					$orderBy[] = (isset($column['table']) ? $column['table']:$primary_table) 
						. '.`' . (isset($column['from']) ? $column['from']:$column['db']) . '` ' . $dir;
				}
			}

			$order = 'ORDER BY ' . implode(', ', $orderBy);
		}

		return $order;
	}

	/**
	 * Searching / Filtering
	 *
	 * Construct the WHERE clause for server-side processing SQL query.
	 *
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here performance on large
	 * databases would be very poor
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @param  array $bindings Array of values for PDO bindings, used in the
	 *    executeSql() function
	 *  @return string SQL where clause
	 */
	static function constructWhereClause($request, $columns, &$bindings, $primary_table)
	{
		$globalSearch = array();
		$columnSearch = array();
		$dtColumns = self::pluck($columns, 'dt', $primary_table);

		if(isset($request['search']) && $request['search']['value'] != '')
		{
			$str = $request['search']['value'];

			for($i = 0, $ien = count($request['columns']); $i < $ien; $i++)
			{
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search($requestColumn['data'], $dtColumns);
				$column = $columns[$columnIdx];

				if($requestColumn['searchable'] == 'true')
				{
					$binding = self::setBinding($bindings, '%' . $str . '%', PDO::PARAM_STR);
					$globalSearch[] = (isset($column['table']) ? $column['table']:$primary_table)
						. ".`" . (isset($column['from']) ? $column['from']:$column['db']) . "` LIKE " . $binding;
				}
			}
		}

		// Individual column filtering
		if(isset($request['columns']))
		{
			for($i = 0, $ien = count($request['columns']); $i < $ien; $i++)
			{
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search($requestColumn['data'], $dtColumns);
				$column = $columns[$columnIdx];

				$str = $requestColumn['search']['value'];

				if($requestColumn['searchable'] == 'true' && $str != '')
				{
					$binding = self::setBinding($bindings, '%' . $str . '%', PDO::PARAM_STR);
					$columnSearch[] = (isset($column['table']) ? $column['table']:$primary_table)
						. ".`" . (isset($column['from']) ? $column['from']:$column['db']) . "` LIKE ".$binding;
				}
			}
		}

		// Combine the filters into a single string
		$where = '';

		if(count($globalSearch))
		{
			$where = '(' . implode(' OR ', $globalSearch) . ')';
		}

		if(count($columnSearch))
		{
			$where = $where === '' ?
				implode(' AND ', $columnSearch) :
				$where .' AND ' . implode(' AND ', $columnSearch);
		}

		if($where !== '')
		{
			$where = 'WHERE '.$where;
		}

		return $where;
	}

	/**
	 * Perform the SQL queries needed for an server-side processing requested,
	 * utilising the helper functions of this class, constructLimitClause(), constructOrderClause() and
	 * constructWhereClause() among others. The returned array is ready to be encoded as JSON
	 * in response to an DataTables request, or can be modified if needed before
	 * sending back to the client.
	 *
	 *  @param  array $db PDO database object
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $primary_table Array containing primary SQL table to query, and primary key field example: $table_info = array('table_name' => 'dinkly_user', 'primary_key' => 'id');
	 *  @param  array $columns Column information array example:
	 *				 	$columns = array(
	 *						array('db' => 'username','dt' => 0, 'table' => 'dinkly_user'),
	 *						array('db' => 'first_name',   'dt' => 1, 'table' => 'dinkly_user'),
	 *						array('db' => 'last_name',  'dt' => 2, 'table' => 'dinkly_user'),
	 *						array('db' => 'created_at',  'dt' => 3, 'table' => 'dinkly_user'),
	 *						array('db' => 'district_code',  'dt' => 4, 'table' => 'congressional_district'),
	 *						array('db' => 'id', 'dt' => 5, 'table' => 'user_referral', 'count' => 'true', 'label' => 'referral_count'),
	 *						array('db' => 'id', 'dt' => 6, 'table' => 'dinkly_user', 
	 *							'formatter' => function($d, $row) { return '<a target="_blank" href="/admin/user/detail/id/' . $d . '">view</a>'; }
	 *						)
	 *					);
	 *  @param  array $joins Join information (supports multiple joins, and of any type) example:
	 *					$joins = array();
	 *					$joins[] = array('left', 'dinkly_user', 'user_affiliation', 'id', 'user_id');
	 *					$joins[] = array('left', 'user_affiliation', 'congressional_district', 'congressional_district_id', 'id');
	 *	@param  string optional group by clause
	 *  @return string $additional_where additional where clause
	 */
	static function doQuery($db, $request, $primary_table_info, $columns, $joins = array(), $group_by = null, $additional_where = null)
	{
		$primary_table = $primary_table_info['table_name'];
		$primary_key = $primary_table_info['primary_key'];
		$bindings = array();
		$join = "";

		if(is_array($joins))
		{
			foreach($joins as $key => $join_info)
			{
				$join_type = $join_info[0];

				$table_a = $join_info[1];
				$table_b = $join_info[2];

				$join_on_a = $join_info[3];
				$join_on_b = $join_info[4];

				$join .= $join_type . " JOIN " . $table_b . " ON " . $table_a . '.`' . $join_on_a 
					. '` = ' . $table_b . '.`' . $join_on_b . '` ';
			}
		}

		$select = implode(", ", self::pluck($columns, 'db', $primary_table));

		// Build the SQL query string from the request
		$limit = self::constructLimitClause($request);
		$order = self::constructOrderClause($request, $columns, $primary_table);
		$where = self::constructWhereClause($request, $columns, $bindings, $primary_table);

		if($where == '' && $additional_where)
		{
			$where = 'WHERE ' . $additional_where;
		}
		elseif($where !== '' && $additional_where)
		{
			$where = $where . ' AND ' . $additional_where;
		}

		$group = null;
		if($group_by)
		{
			$group = " group by " .  str_replace("'", "", $db->quote($group_by)) . " ";
		}

		$query = "SELECT SQL_CALC_FOUND_ROWS $select
					FROM `$primary_table` $join 
					$where
					$group
					$order
					$limit";

		// Main query to actually get the data
		$data = self::executeSql($db, $bindings, $query);

		// Data set length after filtering
		$result_filter_length = self::executeSql($db,
			"SELECT FOUND_ROWS()"
		);
		$records_filtered = $result_filter_length[0][0];

		// Total data set length
		$result_total_length = self::executeSql($db,
			"SELECT COUNT(`{$primary_key}`)
			 FROM   `$primary_table`"
		);
		$records_total = $result_total_length[0][0];

		/*
		 * Output
		 */

		$draw = 0;
		if(isset($request['draw']))
		{
			$draw = $request['draw'];
		}

		return array(
			"draw"            => intval($draw),
			"recordsTotal"    => intval($records_total),
			"recordsFiltered" => intval($records_filtered),
			"data"            => self::genDataOutput($columns, $data)
		);
	}

	/**
	 * Execute an SQL query on the database
	 *
	 * @param  resource $db  Database handler
	 * @param  array    $bindings Array of PDO binding values from setBinding() to be
	 *   used for safely escaping strings. Note that this can be given as the
	 *   SQL query string if no bindings are required.
	 * @param  string   $sql SQL query to execute.
	 * @return array         Result from the query (all rows)
	 */
	static function executeSql($db, $bindings, $sql = null)
	{
		// Argument shifting
		if($sql === null)
		{
			$sql = $bindings;
		}

		$stmt = $db->prepare($sql);

		// Bind parameters
		if(is_array($bindings))
		{
			for($i = 0, $ien = count($bindings); $i < $ien; $i++)
			{
				$binding = $bindings[$i];
				$stmt->bindValue($binding['key'], $binding['val'], $binding['type']);
			}
		}

		// Execute
		try 
		{
			$stmt->execute();
		}
		catch(PDOException $e)
		{
			echo json_encode( array( 
				"error" => $msg
			));
			return false;
		}

		// Return all
		return $stmt->fetchAll();
	}

	/**
	 * Create a PDO binding key which can be used for escaping variables safely
	 * when executing a query with executeSql()
	 *
	 * @param  array &$a    Array of bindings
	 * @param  *      $val  Value to bind
	 * @param  int    $type PDO field type
	 * @return string       Bound key to be used in the SQL where this parameter
	 *   would be used.
	 */
	static function setBinding(&$a, $val, $type)
	{
		$key = ':binding_' . count($a);

		$a[] = array(
			'key' => $key,
			'val' => $val,
			'type' => $type
		);

		return $key;
	}

	/**
	 * Pull a particular property from each assoc. array in a numeric array, 
	 * returning and array of the property values from each item.
	 *
	 *  @param  array  $a    Array to get data from
	 *  @param  string $prop Property to read
	 *  @return array        Array of property values
	 */
	static function pluck($a, $prop, $primary_table)
	{
		$out = array();

		if($prop == 'db')
		{
			for($i = 0, $len = count($a); $i < $len; $i++)
			{
				if(isset($a[$i]['count']) && isset($a[$i]['label']))
				{
					$out[] = 'count(' . $a[$i]['table'] . '.`' . $a[$i][$prop] . '`) as ' . $a[$i]['label'];
				}
				else if(isset($a[$i]['from']))
				{
					$out[] = (isset($a[$i]['table']) ? $a[$i]['table']:$primary_table)
						. ".`" . $a[$i]['from'] . "` as " . $a[$i][$prop];
				}
				else
				{
					$out[] = (isset($a[$i]['table']) ? $a[$i]['table']:$primary_table)
						. ".`" . $a[$i][$prop] . "`";
				}
			}
		}
		else
		{
			for($i = 0, $len = count($a); $i < $len; $i++)
			{
				$out[] = $a[$i][$prop];
			}
		}

		return $out;
	}
}