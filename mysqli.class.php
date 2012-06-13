<?php
/**
 * Hyperion MySQLi Wrapper Class
 *
 * @package		Hyperion
 * @subpackage	Database
 * @author		Ian Lai <ian@fyianlai.com>
 * @copyright	Copyright (C) 2012, Ian Lai
 * @license		Modified BSD License (refer to LICENSE)
 */

defined('HYPERION') or die('No direct script access.');

/**
 * Hyperion MySQL Database Abstraction Layer
 *
 * Concept loosely based on WordPress database class:
 * http://codex.wordpress.org/Class_Reference/wpdb
 *
 * @package		Hyperion
 * @subpackage	Database
 *
 * @todo		Optimise CRUD methods
 */
class MySQLi_DBAL implements Database
{
	private $instance;
	private $reflection;
	private $result;

	public function __construct ($host, $username, $password, $database)
	{
		try {
			$this->connect($host, $username, $password, $database);
			
			/*
			 * @link http://www.php.net/manual/en/intro.reflection.php
			 */
			$this->reflection = new ReflectionClass('mysqli_stmt');
		} catch (Exception $e) {
			$e->getMessage();
		}
	}

	public function __destruct ()
	{
		if ((!empty($this->instance) && $this->instance->close()) || 
			!empty($this->result) && $this->result->close())
			return true;

		return false;
	}

	public function connect ($host, $username, $password, $database)
	{
		$this->instance = new mysqli($host, $username, $password, $database);

		if ($this->instance->connect_error)
			throw new Exception($this->instance->connect_error);

		return $this->instance;
	}

	public function execute ($terminate = true)
	{
		if (empty($this->result)) return;

		$this->result->execute();

		if (!$terminate)
			return $this;

		$ret = $this->result->affected_rows;
		$this->result->close();
		return $ret;
	}

	/*
	 * Prepare an SQL statement for execution.
	 * @link http://www.php.net/manual/en/mysqli-stmt.prepare.php
	 *
	 * @param string $query A single SQL statement to be prepared.
	 * @param array $params An array containing values to be binded to their corresponding markers in $query (in the order of their position in the array)
	 * @return object The MySQLi_DBAL class instance is returned allowing for a fluent interface (chaining methods).
	 */
	public function prepare ($query, Array $params, $types, $reference = true)
	{
		$this->result = $this->instance->prepare($query);
		array_unshift($params, $types);

		if ($reference)
			$params = $this->reference($params);

		$this->reflection->getMethod('bind_param')->invokeArgs($this->result, $params);

		return $this;
	}

	/*
	 * Insert row(s) into a table through a prepared statement.
	 *
	 * @param string $table The name of the table to insert data into.
	 * @param array $data Data to insert (in column => value pairs). Both $data columns and $data values should be "raw" (neither should be SQL escaped).
	 * @param string $formats A string containing all the specifiers/data types to be mapped to each of the values in $data.
	 * @return object The MySQLi_DBAL class instance is returned allowing for a fluent interface (chaining methods).
	 */
	public function create ($table, Array $data, $formats)
	{
		// Build INSERT statement
		$specifiers = str_repeat('?,', count($data));
		$query = "INSERT INTO `{$table}` (`".implode("`,`", array_keys($data))."`) VALUES (".rtrim($specifiers, ',').")";

		// Prepare statement
		$this->prepare($query, $data, $formats, true)->execute();
		return $this;
	}

	/*
	 * Read a single variable or entire row(s) in a database through a result set.
	 * @link http://net.tutsplus.com/tutorials/php/the-problem-with-phps-prepared-statements/ Original code from Tuts+ NET.
	 *
	 * @param string $mode (Optional) One of three pre-defined selection and output types (all|row|var). Defaults to 'all' returning all selected rows.
	 * @return mixed The entire result of a query in the form of either an associative array (all|row) or a single variable (var) depending on $mode.
	 */
	public function read ($mode = 'all')
	{
		if (empty($this->result)) return;

		switch ($mode)
		{
			case 'all': case 'row':
				$parameters = array();
				$results = array();

				$meta = $this->result->result_metadata();
				while ($field = $meta->fetch_field()) {
					$parameters[] = &$row[$field->name];
				}

				$this->reflection->getMethod('bind_result')->invokeArgs($this->result, $parameters);

				while ($this->result->fetch()) {
					$x = array();
					foreach($row as $key => $val) {
						$x[$key] = $val;
					}
					$results[] = $x;
				}

				$ret = $mode === 'all' ? $results : $results[0];
				break;

			case 'var':
				$this->result->bind_result($row);
				while ($this->result->fetch()) {
					$ret = $row;
				}
				break;
		}

		$this->result->close();
		return $ret;
	}

	/*
	 * Update row(s) in a table through a prepared statement.
	 *
	 * @param string $table The name of the table to update.
	 * @param array $update Data to update (in column => value pairs). Both $update columns and $update values should be "raw" (neither should be SQL escaped).
	 * @param array $where A named array of WHERE clauses (in column => value pairs). Multiple clauses will be joined with ANDs. Both $where columns and $where values should be "raw".
	 * @param string $formats A string containing all the specifiers/data types to be mapped to each of the values in $update and $where.
	 * @param boolean $limit (Optional) The maximum number of rows to update. All matched rows will be updated if $limit is false.
	 * @return object The MySQLi_DBAL class instance is returned allowing for a fluent interface (chaining methods).
	 */
	public function update ($table, Array $update, Array $where, $formats, $limit = false)
	{
		// Build array of values to bind
		$data = array_merge($update, array_values($where));

		// Build SET statement
		$update_query = implode('=?,', array_keys($update));

		// Build WHERE statement
		$condition_query = implode('=? && ', array_keys($where));

		// (Optional) Build LIMIT statement
		$limit_query = $limit ? 'LIMIT ?' : '';
		if ($limit) {
			$data[] = $limit;
			$formats .= 'i';
		}

		// Prepare statement
		$query = "UPDATE `{$table}` SET {$update_query}=? WHERE {$condition_query}=? {$limit_query}";
		$this->prepare($query, $data, $formats)->execute();

		return $this;
	}

	/*
	 * Reference all values in an array.
	 * @link http://www.php.net/manual/en/mysqli-stmt.bind-param.php
	 *
	 * @param array $vars Data (in column => value pairs) to reference in a new array.
	 * @return array An array containing referenced values (in column => value pairs) - e.g. &string.
	 * @uses prepare() The invoked mysqli_stmt_bind_param() method requires parameters to be passed by reference.
	 */
	public function reference (Array $vars)
	{
		$refs = array();
		foreach ($vars AS $key => $value)
			$refs[$key] = &$vars[$key];

		return $refs;
	}

}