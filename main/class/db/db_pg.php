<?php
/*
** Allow the connection to a PostgreSQL database and queries in.
** The config.php file allow to change quickly and easily the server parameters.
*/
class Db_pg extends Tempo_model
{
	// Ressource ID from the connexion
	protected $id = NULL;

	// Last query executed by Postgres
	private $last_query = '';
	
	// Cached queries table
	private $cache_query;

	// Last result from query
	private $result_query = 0;

	// Queries count
	public $count = 0;
	
	// Can use EXPLAIN queries ?
	public $can_use_explain = FALSE;

	// Can use REPLACE queries ?
	public $can_use_replace = FALSE;

	// Can use multi_insert ?
	public $can_use_multi_insert = TRUE;

	// Can use TRUNCATE queries ?
	public $can_use_truncate = TRUE;

	// Itérator for cached queries
	private $iterator_query = array();

	// Multi-insert array
	protected $multi_insert = array();

	// Cache array to access to the offset of a field from its name in string format
	private $cache_field_type = array();

	// Define if we are in a transaction
	protected $in_transaction = FALSE;

	// Object cache
	public $cache = NULL;

	/* Connection to the database
	** $server ::		Postgres server adress
	** $login ::		Postgres login
	** $pass ::			password
	** $db ::			database name
	** $port ::			Connexion port
	** $use_cache ::	Use of the SQL cache ?
	*/
	public function __construct($db_server, $db_login, $db_pass, $db, $db_port = NULL, $use_cache = TRUE)
	{
		$this->use_cache = $use_cache;
		$this->id = NULL;
		$str = "user=$db_login password=$db_pass dbname=$db host=$db_server";
		if ($db_port)
		{
			$str .= "port=$db_port ";
		}
		if (!$this->id = pg_connect($str))
		{
			return (FALSE);
		}

		if (!$this->id)
		{
			return ;
		}
		$this->can_use_explain = FALSE;
		$this->can_use_replace = FALSE;
		$this->can_use_multi_insert = TRUE;
		$this->can_use_truncate = TRUE;
	}

	public function __destruct()
	{
		if ($this->_get_id())
		{
			$this->close();
		}
	}

	/*
	** Execute the SQL query and return the result
	** -----
	** $sql ::		Query to execute
	** $buffer ::	If TRUE, the result is buffered. Use FALSE for queries that do not return any result (UPDATE, DELETE, INSERT, etc ...)
	*/
	public function query($sql, $cache_prefix = NULL)
	{
		if (!$this->use_cache)
		{
			$cache_prefix = NULL;
		}

		// On calcul un hash MD5 de la requète
		$hash_query = md5($sql);

		$buffer = (preg_match('#^(SELECT|SHOW)#i', trim($sql))) ? TRUE : FALSE;
		
		// Control of queries limits
		preg_match('/^(.*?)\sLIMIT ([0-9]+)(,\s?([0-9]+))?\s*$/si', $sql, $match);
		if (isset($match[2]))
		{
			$sql = $match[1] . ' LIMIT ' . ((isset($match[4])) ? $match[4] : $match[2]). ((!empty($match[3])) ? ' OFFSET ' . $match[2] : '');
		}
		
		$this->last_query = $sql;
		if (!($result = pg_query($this->id, $sql)) && pg_last_error())
		{
			trigger_error('error_sql :: ' . $this->sql_error() . '<br />-----<br />' . htmlspecialchars($sql));
		}
		
		$this->count++;
		if ($cache_prefix)
		{
			// We place the query in the cache
			$data = $this->rows($result, 'array');

			// Return the result of the query
			$this->result_query++;
			$this->iterator_query[$this->result_query] = 0;
			$result = $this->result_query;
		}
		return ($result);
	}

	/*
	** Simple query which does not show any error
	** -----
	** $sql ::		Query to execute
	*/
	public function simple_query($sql)
	{
		$this->last_query = $sql;
		return (pg_query($this->id, $sql));
	}
	
	/*
	** Make a INSERT query from a table.
	** -----
	** $table ::		Name of the table
	** $ary ::			Table containing in key the fields of the query and in value the values for the query
	** $multi_insert ::	TRUE for multi-insert, to use with final method
	**						Dbal::query_multi_insert()
	*/
	public function insert($table, $ary, $multi_insert = FALSE)
	{
		$fields = '';
		$values = '';
		foreach ($ary AS $key => $value)
		{
			$fields .= $key . ', ';

			if (is_array($value))
			{
				$value = $value[0];
			}
			$values .= ((is_int($value)) ? $value . ', ' : '\'' . $this->escape($value) . '\', ');
		}
		$fields = substr($fields, 0, -2);
		$values = substr($values, 0, -2);

		if ($multi_insert && $this->can_use_multi_insert)
		{
			if (!$this->multi_insert)
			{
				$this->multi_insert = array(
					'insert' => $insert,
					'table' =>	$table,
					'fields' =>	$fields,
					'values' =>	array(),
				);
			}

			$this->multi_insert['values'][] = $values;
		}
		else
		{
			$sql = 'INSERT INTO ' . $table . "
							($fields)
						VALUES ($values)";
			return ($this->query($sql));
		}
	}

	/*
	** Make an UPDATE query from a table
	** -----
	** $table ::	Name of the table
	** $ary ::		Table containing in key the fields of the query and in value the values for the query
	** $where ::	Clause where of the query
	*/
	public function update($table, $ary, $where = '')
	{
		$sql = 'UPDATE ' . $table . ' SET ';
		foreach ($ary AS $key => $value)
		{
			$is_field = FALSE;
			if (is_array($value))
			{
				$is_field = (isset($value['is_field']) && $value['is_field']) ? TRUE : FALSE;
				$value = $value[0];
			}
			
			if ($is_field)
			{
				$sql .= ' ' . $key . ' = ' . $value . ', ';
			}
			else
			{
				$sql .= (is_int($value) || is_bool($value)) ? ' ' . $key . ' = ' . (int) $value . ', ' : ' ' . $key . ' = \'' . $this->escape($value) . '\', ';
			}
		}
		$sql = substr($sql, 0, -2);
		$sql .= ' ' . $where;
		return ($this->query($sql));
	}
	
	/*
	** Truncate a table
	** -----
	** $table ::		Name of the table
	*/
	public function query_truncate($table)
	{
		$this->query('TRUNCATE ' . $table);
	}
	
	/*
	** Return une line of the result and move the pointer into the next line.
	** -----
	** $result ::		Result of a query
	** $function ::		Function to call.
	*/
	public function row($result, $function = 'assoc')
	{
		if (is_int($result) && isset($this->cache_query[$result]))
		{
			if (isset($this->cache_query[$result][$this->iterator_query[$result]]))
			{
				return ($this->cache_query[$result][$this->iterator_query[$result]++]);
			}
			return (NULL);
		}
		else
		{
			switch ($function)
			{
				case "assoc" :
					$flag = PGSQL_ASSOC;
				break;
	
				case "row" :
					$flag = PGSQL_NUM;
				break;
	
				default :
					$flag = PGSQL_BOTH;
				break;
			}
		return (pg_fetch_array($result, NULL, $flag));
		}
	}

	/*
	** Return a table containing each lines of the result
	** -----
	** $result ::		Result of a query
	** $function ::		See row()
	** $field_name ::	If the name of a field is given as a parameter, the array will be associated to the values of this field. This field must be unique.
	*/
	public function rows($result, $function = 'assoc', $field_name = NULL)
	{
		$data = array();
		while ($tmp = $this->row($result, $function))
		{
			if ($field_name)
			{
				$data[$tmp[$field_name]] = $tmp;
			}
			else
			{
				$data[] = $tmp;
			}

			unset($tmp);
		}
		$this->free($result);
		return ($data);
	}


	/*
	** Free the result of a query
	** -----
	** $result ::		Result of a query
	*/
	public function free($result)
	{
		if (is_resource($result))
		{
			pg_free_result($result);
		}
	}
	
	/*
	** Return one or several fields from a query, for example :
	**	$sql = 'SELECT field FROM table WHERE field = xxx';
	**	$field = Tempo::$db->get($sql, 'field');
	**
	** or for several fiedls :
	**	$sql = 'SELECT field1, field2 FROM table WHERE field = xxx';
	**	list($field1, $field2) = Tempo::$db->get($sql, array('field1', 'field2'));
	*/
	public function get($query, $fields)
	{
		$result = $this->query($query);
		$row = $this->row($result);
		$this->free($result);

		if (!$row)
		{
			return (NULL);
		}

		if (is_array($fields))
		{
			$return = array();
			foreach ($fields AS $field)
			{
				$return[] = $row[$field];
			}
		}
		else
		{
			$return = $row[$fields];
		}
		return ($return);
	}
	
	/*
	** Return the result of a query
	** -----
	** $query ::	SQL query
	*/
	public function request($query)
	{
		$result = $this->query($query);
		$row = $this->row($result);
		$this->free($result);

		return ($row);
	}

	/*
	** Count the number of line returned by a SELECT query
	** -----
	** $result ::		Result of a query
	*/
	public function count($result)
	{
		return ((is_int($result) && isset($this->cache_query[$result])) ? count($this->cache_query[$result]) : pg_num_rows($result));
	}

	/*
	** Return the last Postgres error
	*/
	public function sql_error()
	{
		return (pg_last_error());
	}

	/*
	** Close the connexion to the database
	*/
	public function close()
	{		
		unset($this->cache_field_type, $this->cache_query, $this->iterator_query);
		pg_close($this->id);
	}

	/*
	** Return the last ID after an INSERT in case of an automatic incrementation
	*/
	public function last_id()
	{
		$last_id = 0;
		if (preg_match('/^INSERT INTO\s+([a-zA-Z0-9_]*?)\s+/si', $this->last_query, $match))
		{
			$sql = 'SELECT currval(\'' . $match[1] . '_seq\') AS last_id';
			$result = $this->simple_query($sql);
			$data = pg_fetch_array($result, NULL, PGSQL_ASSOC);
			$this->free($result);
			$last_id = (isset($data['last_id'])) ? intval($data['last_id']) : 1;
		}
		return ($last_id);
	}

	/*
	** Protect a field of the query
	** -----
	** $str :: String to protect
	*/
	public function escape($str)
	{
		return (pg_escape_string($str));
	}

	/*
	** Return the number of lines affected by a query
	** -----
	** $result ::		Result of a query
	*/
	public function affected_rows($result)
	{
		return (pg_affected_rows($result));
	}


	/*
	** Return the type of a field
	** -----
	** $result ::	Result of a query
	** $field ::	Field to verify
	** $table ::	Name of the table concerned
	*/
	public function field_type($result, $field, $table = NULL)
	{
		return (pg_field_type($result, (is_int($field)) ? $field : pg_field_num($result, $field)));
	}

	/*
	** Return 'string' or 'int' if the field is a string or an integer.
	** -----
	** $result ::	Result of a query
	** $field ::	Field to verify
	** $table ::	Name of the table concerned
	*/
	public function get_field_type($result, $field, $table = NULL)
	{
		$field_type = $this->field_type($result, $field);
		if (!$field_type)
		{
			$field_type = 'string';
		}

		switch (strtolower($field_type))
		{
			case 'int4' :
			case 'int2' :
				return ('int');

			case 'varchar' :
			case 'text' :
			case 'string' :
			default :
				return ('string');
		}
	}

	/*
	** Return a table containing a list of tables
	*/
	public function list_tables()
	{
		$sql = 'SELECT tablename FROM pg_tables
					WHERE schemaname = \'public\'';
		return ($this->query($sql));
	}

	/*
	** Execute a multi-insert
	*/
	public function query_multi_insert()
	{
		if ($this->multi_insert)
		{
			$sql = $this->multi_insert['insert'] . ' INTO ' . $this->multi_insert['table']
						. ' (' . $this->multi_insert['fields'] . ')
						VALUES (' . implode('), (', $this->multi_insert['values']) . ')';
			$this->multi_insert = array();
			return ($this->query($sql));
		}
	}

	/*
	** Transactions
	** -----
	** $type ::		State of the transaction (begin, commit or rollback)
	*/
	public function transaction($type)
	{
		switch ($type)
		{
			case 'begin' :
				if (!$this->in_transaction)
				{
					$this->simple_query('BEGIN');
				}
				$this->in_transaction = TRUE;
			break;

			case 'commit' :
				if ($this->in_transaction)
				{
					$this->simple_query('COMMIT');
				}
				$this->in_transaction = FALSE;
			break;

			case 'rollback' :
				if ($this->in_transaction)
				{
					$this->simple_query('ROLLBACK');
				}
				$this->in_transaction = FALSE;
			break;
		}
	}

	/*
	** Delete elements from several tables
	** (PostgreSQL does not support multi-delete)
	** -----
	** $default_table ::		Default table where we will take the fields
	** $default_where ::		Clause WHERE for the retrieval of the fields
	** $delete_join ::			Associativ table containing in key the fields and in value the tables' values from the SQL tables
	*/
	public function delete_tables($default_table, $default_where, $delete_join)
	{
		foreach ($delete_join AS $field => $tables)
		{
			$sql = 'SELECT ' . $field . '
					FROM ' . $default_table
					. ' ' . $default_where;
			$result = $this->query($sql);
			$list_idx = '';
			while ($row = Tempo::$db->row($result))
			{
				$list_idx .= $row[$field] . ',';
			}
			$list_idx = substr($list_idx, 0, -1);
			$this->free($result);

			if ($list_idx)
			{
				foreach ($tables AS $table)
				{
					$sql = 'DELETE FROM ' . $table . ' WHERE ' . $field . ' IN(' . $list_idx . ')';
					$this->query($sql);
				}
			}
		}
	}

	/*
	** Return the operator LIKE
	*/
	public function like()
	{
		return ('ILIKE');
	}
}

/*
** Creation of dynamic SQL SELECT queries
** Use only on dynamic / difficult reading queries.
*/
class Sql_select extends Tempo_model
{
	private $query = '';
	private $fields = '';
	private $join = '';
	private $where = '';
	private $order = '';
	private $group = '';
	private $limit = '';

	/*
	** Builder
	** -----
	** $select_state ::		Selection clause of the query (SELECT DISTINCT for exemple)
	*/
	public function __construct($select_state = 'SELECT')
	{
		$this->query = $select_state . ' ';
	}

	/*
	** Add a table to the query
	** -----
	** $join_state ::		Link of the table in the query (FROM, LEFT JOIN, INNER JOIN, etc...)
	** $tablename ::		Name of the table
	** $fields ::			Fields to select
	** $on ::				Table joint
	*/
	public function join_table($join_state, $tablename, $fields = '', $on = '')
	{
		$this->fields .= ($this->fields && $fields) ? ', ' . $fields : $fields;
		$this->join .= (($this->join) ? "\n" : '') . $join_state . ' ' . $tablename . (($on) ? "\n" . $on : '');
	}

	/*
	** Fill the clause WHERE
	*/
	public function where($str)
	{
		$this->where .= ((!$this->where) ? 'WHERE ' . $str : $str) . ' ';
	}

	/*
	** Fill the clause GROUP BY
	*/
	public function group_by($str)
	{
		$this->group .= ((!$this->group) ? 'GROUP BY ' . $str : $str) . ' ';
	}

	/*
	** Fill the clause ORDER BY
	*/
	public function order_by($str)
	{
		$this->order .= ((!$this->order) ? 'ORDER BY ' . $str : $str) . ' ';
	}

	/*
	** Fill the clause GROUP BY
	** -----
	** $first ::	First offset for the limit
	** $second ::	Second offset for the limit
	*/
	public function limit($first, $second)
	{
		$this->limit .= 'LIMIT ' . $first . (($second) ? ', ' . $second : '');
	}

	/*
	** Execute the query
	** -----
	** $get ::			Use of Sql_dbal::get() 
	** $cache_query ::	Prefix of the query if we will cache it
	*/
	public function execute($get = '', $cache_query = '')
	{
		foreach (array('fields', 'join', 'where', 'group', 'order', 'limit') AS $property)
		{
			if ($this->$property)
			{
				$this->query .= $this->$property . "\n";
			}
		}

		if ($get)
		{
			return (Tempo::$db->get($this->query, $get));
		}
		return (Tempo::$db->query($this->query, $cache_query));
	}
}

?>