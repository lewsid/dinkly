<?php

class BaseDinklyDataTables 
{
	/**
	* Server Side Processing for DataTables - Author: Andrew Rousseau (andrewvt)
	*
	* Note: This is a modified version of the php code supplied by DataTables
	* Additional features:
	*  - Accommodates LEFT JOINs
	*  - Accomodates passing in additional where criteria string
	*
	* Kitchen Sink Example:
	*
	*	// In the Controller:
	*	public function loadPolicyServerSide()
	*	{
	*		// policy is the from table and policy_meta will be left joined
	*		// ie: FROM policy LEFT JOIN policy_meta ON policy.`id` = policy_meta.`policy_id`
	*		$table = array(
	*			'policy',
	*			array(
	*				'primary_table' => 'policy',
	*				'primary_field' => 'id',
	*				'join_table' => 'policy_meta',
	*				'join_field' => 'policy_id'
	*			)
	*		);
	*
	*		// Table's primary key
	*		$primaryKey = 'id';
	*
	*		// Array of database columns which should be read and sent back to DataTables.
	*		// The `db` parameter represents the column name in the database, while the `dt`
	*		// parameter represents the DataTables column identifier. In this case simple
	*		// indexes
	*		$columns = array(
	*			array('db' => 'id','dt' => 0,'table' => 'policy'),
	*			array( 'db' => 'number', 'dt' => 1 , 'table' => 'policy_meta'),
	*			array( 'db' => 'name',   'dt' => 2 , 'table' => 'policy_meta'),
	*			array( 'db' => 'description',  'dt' => 3 , 'table' => 'policy_meta'),
	*			array( 'db' => 'abstract',  'dt' => 4 , 'table' => 'policy_meta'),
	*			array( 'db' => 'created_at',  'dt' => 5 , 'table' => 'policy'),
	*			array( 'db' => 'updated_at',  'dt' => 6 , 'table' => 'policy')
	*		);
	*
	*		// This will be added to the where statement
	*		$additional_where = '((policy.is_deleted = 0 OR policy.is_deleted IS NULL) AND policy_meta.is_current_live = 1)';
	*
	*		$this->handleResponse(json_encode(
	*			DinklyDataTables::simple( $this->db, $_POST, $table, $primaryKey, $columns, $additional_where )
	*		));
	*	}
	*
	*	// The js for the table:
	*	<script type="text/javascript">
	*	$(function() {
	*	  var datatable = $('.my-datatable').DataTable({
	*	    "dom": "<'row'<'col-6'><'col-6'l><'pull-right' f>r>t<'row'<'col-6'i><'col-6'<'pull-right' p>>>",
	*	    "PaginationType": "bootstrap",
	*		"ColumnDefs": [{
	*	        "Sortable" : false,
	*	        "Targets" : [ "no-sort" ]
	*	    }],
	*	    "columns": [
	*	      null,
	*	      null,
	*	      null,
	*	      null,
	*	      null,
	*	      null,
	*	      null,
	*	      null
	*	    ],
	*	    "processing": true,
	*	    "serverSide": true,
	*	    "ajax": {
	*	      "url": "/app/module/policy_server_side",
	*	      "type": "POST"
	*	      }
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
	static function data_output ( $columns, $data )
	{
		$out = array();

		for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
			$row = array();

			for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
				$column = $columns[$j];

				// Is there a formatter?
				if ( isset( $column['formatter'] ) ) {
					$row[ $column['dt'] ] = $column['formatter']( $data[$i][ $column['db'] ], $data[$i] );
				}
				else {
					$row[ $column['dt'] ] = $data[$i][ $columns[$j]['db'] ];
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
	 *  @param  array $columns Column information array
	 *  @return string SQL limit clause
	 */
	static function limit ( $request, $columns )
	{
		$limit = '';

		if ( isset($request['start']) && $request['length'] != -1 ) {
			$limit = "LIMIT ".intval($request['start']).", ".intval($request['length']);
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
	static function order ( $request, $columns, $primary_table )
	{
		$order = '';

		if ( isset($request['order']) && count($request['order']) ) {
			$orderBy = array();
			$dtColumns = self::pluck( $columns, 'dt', $primary_table );

			for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
				// Convert the column index into the column data property
				$columnIdx = intval($request['order'][$i]['column']);
				$requestColumn = $request['columns'][$columnIdx];

				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['orderable'] == 'true' ) {
					$dir = $request['order'][$i]['dir'] === 'asc' ?
						'ASC' :
						'DESC';

					$orderBy[] = (isset($column['table']) ? $column['table']:$primary_table).'.`'.(isset($column['from']) ? $column['from']:$column['db']).'` '.$dir;
				}
			}

			$order = 'ORDER BY '.implode(', ', $orderBy);
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
	 *    sql_exec() function
	 *  @return string SQL where clause
	 */
	static function filter ( $request, $columns, &$bindings, $primary_table )
	{
		$globalSearch = array();
		$columnSearch = array();
		$dtColumns = self::pluck( $columns, 'dt', $primary_table );

		if ( isset($request['search']) && $request['search']['value'] != '' ) {
			$str = $request['search']['value'];

			for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['searchable'] == 'true' ) {
					$binding = self::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
					$globalSearch[] = (isset($column['table']) ? $column['table']:$primary_table).".`".(isset($column['from']) ? $column['from']:$column['db'])."` LIKE ".$binding;
				}
			}
		}

		// Individual column filtering
		for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $request['columns'][$i];
			$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = $columns[ $columnIdx ];

			$str = $requestColumn['search']['value'];

			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$binding = self::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
				$columnSearch[] = (isset($column['table']) ? $column['table']:$primary_table).".`".(isset($column['from']) ? $column['from']:$column['db'])."` LIKE ".$binding;
			}
		}

		// Combine the filters into a single string
		$where = '';

		if ( count( $globalSearch ) ) {
			$where = '('.implode(' OR ', $globalSearch).')';
		}

		if ( count( $columnSearch ) ) {
			$where = $where === '' ?
				implode(' AND ', $columnSearch) :
				$where .' AND '. implode(' AND ', $columnSearch);
		}

		if ( $where !== '' ) {
			$where = 'WHERE '.$where;
		}

		return $where;
	}

	/**
	 * Perform the SQL queries needed for an server-side processing requested,
	 * utilising the helper functions of this class, limit(), order() and
	 * filter() among others. The returned array is ready to be encoded as JSON
	 * in response to an SSP request, or can be modified if needed before
	 * sending back to the client.
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $sql_details SQL connection details - see sql_connect()
	 *  @param  string $table SQL table to query
	 *  @param  string $primaryKey Primary key of the table
	 *  @param  array $columns Column information array
	 *  @return array          Server-side processing response array
	 */
	static function simple ( $db, $request, $table, $primaryKey, $columns, $additional_where = null )
	{
		$bindings = array();
		$join = "";
		$primary_table = $table;
		if(is_array($table))
		{
			$primary_table = $table[0];
			foreach($table as $key => $to_join)
			{
				if($key !== 0)
				{
					if(is_array($to_join)){
						$join .= "LEFT JOIN " . $to_join['join_table'] . " ON " . $to_join['join_table'] . ".`" . $to_join['join_field'] . "` = " . $to_join['primary_table'] . ".`" . $to_join['primary_field'] . "` ";
					}
					else
						$join .= "LEFT JOIN " . $to_join . " ON " . $to_join . ".id = " . $table[0] . "." . $to_join . "_id ";
				}
			}
		}
		$select = implode(", ", self::pluck($columns, 'db', $primary_table));

		// Build the SQL query string from the request
		$limit = self::limit( $request, $columns );
		$order = self::order( $request, $columns, $primary_table );
		$where = self::filter( $request, $columns, $bindings, $primary_table );
		if ( $where == '' && $additional_where)
			$where = 'WHERE ' . $additional_where;
		elseif ( $where !== '' && $additional_where)
			$where = $where . ' AND ' . $additional_where;

		// Main query to actually get the data
		$data = self::sql_exec( $db, $bindings,
			"SELECT SQL_CALC_FOUND_ROWS $select
			 FROM `$primary_table` $join 
			 $where
			 $order
			 $limit"
		);

		// Data set length after filtering
		$resFilterLength = self::sql_exec( $db,
			"SELECT FOUND_ROWS()"
		);
		$recordsFiltered = $resFilterLength[0][0];

		// Total data set length
		$resTotalLength = self::sql_exec( $db,
			"SELECT COUNT(`{$primaryKey}`)
			 FROM   `$primary_table`"
		);
		$recordsTotal = $resTotalLength[0][0];


		/*
		 * Output
		 */
		return array(
			"draw"            => intval( $request['draw'] ),
			"recordsTotal"    => intval( $recordsTotal ),
			"recordsFiltered" => intval( $recordsFiltered ),
			"data"            => self::data_output( $columns, $data )
		);
	}

	/**
	 * Execute an SQL query on the database
	 *
	 * @param  resource $db  Database handler
	 * @param  array    $bindings Array of PDO binding values from bind() to be
	 *   used for safely escaping strings. Note that this can be given as the
	 *   SQL query string if no bindings are required.
	 * @param  string   $sql SQL query to execute.
	 * @return array         Result from the query (all rows)
	 */
	static function sql_exec ( $db, $bindings, $sql=null )
	{
		// Argument shifting
		if ( $sql === null ) {
			$sql = $bindings;
		}

		$stmt = $db->prepare( $sql );

		// Bind parameters
		if ( is_array( $bindings ) ) {
			for ( $i=0, $ien=count($bindings) ; $i<$ien ; $i++ ) {
				$binding = $bindings[$i];
				$stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
			}
		}

		// Execute
		try {
			$stmt->execute();
		}
		catch (PDOException $e) {
			self::fatal( "An SQL error occurred: ".$e->getMessage() );
		}

		// Return all
		return $stmt->fetchAll();
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Internal methods
	 */

	/**
	 * Throw a fatal error.
	 *
	 * This writes out an error message in a JSON string which DataTables will
	 * see and show to the user in the browser.
	 *
	 * @param  string $msg Message to send to the client
	 */
	static function fatal ( $msg )
	{
		echo json_encode( array( 
			"error" => $msg
		) );

		exit(0);
	}

	/**
	 * Create a PDO binding key which can be used for escaping variables safely
	 * when executing a query with sql_exec()
	 *
	 * @param  array &$a    Array of bindings
	 * @param  *      $val  Value to bind
	 * @param  int    $type PDO field type
	 * @return string       Bound key to be used in the SQL where this parameter
	 *   would be used.
	 */
	static function bind ( &$a, $val, $type )
	{
		$key = ':binding_'.count( $a );

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
	static function pluck ( $a, $prop, $primary_table )
	{
		$out = array();

		if($prop == 'db')
		{
			for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
				if(isset($a[$i]['from']))
					$out[] = (isset($a[$i]['table']) ? $a[$i]['table']:$primary_table).".`".$a[$i]['from']."` as ".$a[$i][$prop];
				else
					$out[] = (isset($a[$i]['table']) ? $a[$i]['table']:$primary_table).".`".$a[$i][$prop]."`";
			}
		}
		else
		{
			for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
				$out[] = $a[$i][$prop];
			}
		}

		return $out;
	}
}

