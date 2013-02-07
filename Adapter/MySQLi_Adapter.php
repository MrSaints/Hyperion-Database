<?php
namespace Hion\Database\Adapter;
use Hion\Database as Database;

/**
 * Hyperion MySQLi Driver Class
 *
 * @package		Hyperion
 * @subpackage	Database
 * @author		Ian Lai
 * @copyright	Copyright (C) 2013, Ian Lai
 * @license		Modified BSD License (refer to LICENSE)
 */

class MySQLi_Adapter implements IAdapter
{
	private $instance;
	private $reflection;
	private $result;

	private $formats = array(
		'boolean'	=>	'i',
		'integer'	=>	'i',
		'string'	=>	's',
		'blob'		=>	'b',
		'double'	=>	'd',
	);

	public function init (Database\Core $core)
	{
		try {
			$this->instance = new \mysqli($core->host, $core->username, $core->password, $core->schema);

			if ($this->instance->connect_error)
				throw new Exception($this->instance->connect_error);

			/*
			 * @link http://www.php.net/manual/en/intro.reflection.php
			 */
			$this->reflection = new \ReflectionClass('mysqli_stmt');
		} catch (Exception $e) {
			die ($e->getMessage());
		}
	}

	/*
	 * Prepare an SQL statement for execution.
	 * @link http://www.php.net/manual/en/mysqli-stmt.prepare.php
	 *
	 * @param Database\Core $core
	 * @return object
	 */
	public function prepare (Database\Core $core)
	{
		$this->result = $this->instance->prepare($core->query);

		if (!$this->result)
			throw new Exception($this->instance->error);

		$formats = array();
		foreach ($core->formats AS $key => $format) {
			$formats[] = $this->formats[$format];
		}

		array_unshift($core->data, implode('', $formats));

		$this->reflection->getMethod('bind_param')->invokeArgs($this->result, $this->reference($core->data));

		return $this;
	}

	public function save (Database\Core $core)
	{
		if (empty($this->result)) return;

		$this->result->execute();

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
		switch ($mode)
		{
			case 'row':
			case 'all':
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

					if ($mode === 'row')
						break;
				}

				$return = $mode === 'all' ? $results : $results[0];
				break;

			case 'var':
				$this->result->bind_result($row);
				while ($this->result->fetch()) {
					$ret = $row;
				}
				break;
		}

		$this->result->close();
		return $return;
	}

	public function affected_rows ()
	{
		$rows = $this->result->affected_rows;
		$this->result->close();
		return $rows;
	}

	public function last_insert_id ()
	{
		$id = $this->result->insert_id;
		$this->result->close();
		return $id;
	}

	/*
	 * Reference all values in an array.
	 * @link http://www.php.net/manual/en/mysqli-stmt.bind-param.php
	 *
	 * @param array $vars Data (in column => value pairs) to reference in a new array.
	 * @return array An array containing referenced values (in column => value pairs) - e.g. &string.
	 * @uses prepare() The invoked mysqli_stmt_bind_param() method requires parameters to be passed by reference.
	 */
	private function reference (Array $vars)
	{
		$refs = array();
		foreach ($vars AS $key => $value)
			$refs[$key] = &$vars[$key];

		return $refs;
	}
}