<?php
namespace Hion\Database;
use Hion\Database\Adapter as Adapter;

/**
 * Hyperion MySQL Database Abstraction Layer (CRUD)
 *
 * Concept loosely based on WordPress database class:
 * http://codex.wordpress.org/Class_Reference/wpdb
 *
 * @package		Hyperion
 * @subpackage	Database
 * @author		Ian Lai
 * @copyright	Copyright (C) 2013, Ian Lai
 * @license		Modified BSD License (refer to LICENSE)
 */

class Core
{
	public $host;
	public $username;
	public $password;
	public $schema;

	public $type;
	public $query = '';

	public $table;
	public $data = array();
	public $conditions = array();
	public $formats = array();

	public $offset;
	public $limit;

	public function __construct ($host, $username, $password, $schema)
	{
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->schema = $schema;
		return $this;
	}

	public function select ($table, Array $columns)
	{
		$this->table = $table;
		$this->data = $columns;

		return $this;
	}

	public function where ($column, $operator = '=', $value, $format = 'string', $prefix = '')
	{
		$this->conditions[] = array("{$prefix} `{$column}` {$operator} ?", $value);
		$this->formats[] = $format;

		return $this;
	}

	public function limit (Integer $limit)
	{
		$this->limit = $limit;
		return $this;
	}

	public function offset (Integer $offset)
	{
		$this->offset = $offset;
		return $this;
	}

	public function build (Adapter\IAdapter $adapter)
	{
		$adapter->init($this);

		switch ($this->type)
		{
			case 'create';
				$bindings = str_repeat('?,', count($this->data));
				$this->query = "INSERT INTO `{$this->table}` (`".implode("`,`", array_keys($this->data))."`) VALUES (".rtrim($bindings, ',').")";
				break;

			case 'read':
			case 'update':
			case 'delete':
				$conditions = '';
				$condition_statements = $condition_values = array();
				foreach ($this->conditions AS $key => $condition) {
					$condition_statements[] = $condition[0];
					$condition_values[] = $condition[1];
				}
				$conditions = !empty($this->conditions) ? 'WHERE' . implode(' ', $condition_statements) : '';

				$offset = sprintf('LIMIT %d,', $this->offset);
				$limits = $this->limit ? sprintf($offset . '%d', $this->limit) : '';

				if ($this->type === 'read') {	// READ Template
					$columns = !empty($this->data) ? '`'.implode('`, `', $this->data).'`' : '*';
					$this->query = "SELECT {$columns} FROM `{$this->table}` {$conditions} {$limits}";
				} else if ($this->type === 'update') {	// UPDATE Template
					$data = implode('` = ?, `', array_keys($this->data));
					$this->query = "UPDATE `{$this->table}` SET `{$data}` = ? {$conditions} {$limits}";
				} else {	// DELETE Template
					$this->query = "DELETE FROM {$this->table} {$conditions} {$limits}";
				}

				$this->data = $this->type === 'read' || $this->type === 'delete' ? $condition_values : 
								array_merge(array_values($this->data), $condition_values);
				break;
		}

		$adapter->prepare($this);

		return $this;
	}

	public function save (Adapter\IAdapter $adapter)
	{
		$this->build($adapter);
		$adapter->save($this);

		$this->reset();

		if ($this->type === 'create')
			return $adapter->last_insert_id();
		elseif ($this->type === 'update' || $this->type === 'delete')
			return $adapter->affected_rows();

		return $this;
	}

	public function reset ()
	{
		$this->query = '';
		$this->data = array();
		$this->conditions = array();
		$this->formats = array();

		$this->offset = false;
		$this->limit = false;
	}

	public function create ($table, Array $data, Array $formats)
	{
		$this->type = 'create';

		$this->table = $table;
		$this->data = $data;
		$this->formats = $formats;

		return $this;
	}

	public function read (Adapter\IAdapter $adapter, $mode = 'all')
	{
		$this->type = 'read';
		$this->save($adapter);

		return $adapter->read($mode);
	}

	public function update ($table, Array $data, Array $formats)
	{
		$this->type = 'update';

		$this->table = $table;
		$this->data = $data;
		$this->formats = $formats;

		return $this;
	}

	public function delete ($table)
	{
		$this->type = 'delete';

		$this->table = $table;

		return $this;
	}
}