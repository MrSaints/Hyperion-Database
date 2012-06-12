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
 * Concept derived from Wordpress DB Class:
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

	public function prepare ($stmt, Array $params, $types, $reference = true)
	{
		$this->result = $this->instance->prepare($stmt);
		array_unshift($params, $types);

		if ($reference)
			$params = $this->reference($params);

		$this->reflection->getMethod('bind_param')->invokeArgs($this->result, $params);

		return $this;
	}

	/*
		Prepared INSERT statement

		create (
			table_name,
			array(
				'insert_column_name' => $insert_value
			),
			'(i|d|s|b)'
		)
	 */
	public function create ($table, Array $data, $formats)
	{
		$specifiers = str_repeat('?,', count($data));
		$query = "INSERT INTO `{$table}` (`".implode("`,`", array_keys($data))."`) VALUES (".rtrim($specifiers, ',').")";

		$this->prepare($query, $data, $formats, true)->execute();
		return $this;
	}

	/*
	 * Returns data from result set
	 * 
	 * Original code from:
	 * http://net.tutsplus.com/tutorials/php/the-problem-with-phps-prepared-statements/
	 *
	 * read ($mode)
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
		Prepared UPDATE statement
	
		update (
			table_name,
			array(
				'update_column_name' => $update_value
			),
			array(
				'where_column_name' => $where_value
			),
			'(i|d|s|b)'
		)
	 */
	public function update ($table, Array $update, Array $where, $formats, $limit = false)
	{
		$data = array_merge($update, array_values($where));

		// Build SET statement
		$update_query = rtrim(implode('=?,', array_keys($update)), ',');
		$update_query .= '=?';

		// Build WHERE statement
		$condition_query = rtrim(implode('=?,', array_keys($where)), ',');
		$condition_query .= '=?';

		// (Optional) Build LIMIT statement
		$limit_query = $limit ? 'LIMIT ?' : '';
		if ($limit) {
			$data[] = $limit;
			$formats .= 'i';
		}

		$query = "UPDATE `{$table}` SET {$update_query} WHERE {$condition_query} {$limit_query}";

		$this->prepare($query, $data, $formats)->execute();
		return $this;
	}

	public function reference (Array $vars)
	{
		$refs = array();
		foreach ($vars AS $key => $value)
			$refs[$key] = &$vars[$key];

		return $refs;
	}

}