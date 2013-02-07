<?php
namespace Hion\Database\Adapter;
use Hion\Database as Database;

/**
 * Hyperion PDO Driver Class
 *
 * @package		Hyperion
 * @subpackage	Database
 * @author		Ian Lai
 * @copyright	Copyright (C) 2013, Ian Lai
 * @license		Modified BSD License (refer to LICENSE)
 */

class PDO_Adapter implements IAdapter
{
	private $instance;
	private $result;

	private $formats = array(
		'boolean'	=>	\PDO::PARAM_BOOL,	
		'null'		=>	\PDO::PARAM_NULL,
		'integer'	=>	\PDO::PARAM_INT,
		'string'	=>	\PDO::PARAM_STR,
		'blob'		=>	\PDO::PARAM_LOB,
	);

	public function init (Database\Core $core)
	{
		try {
			$this->instance = new \PDO("mysql:dbname={$core->schema};host={$core->host}", 
										$core->username, $core->password);
			$this->instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			die ($e->getMessage());
		}
	}

	public function prepare (Database\Core $core)
	{
		$this->result = $this->instance->prepare($core->query);

		if (!$this->result) {
			$error = $this->instance->errorInfo();
			throw new Exception($error[2]);
		}

		$i = 0;

		foreach ($core->data AS $key => $value) {
			$format = $core->formats[$i];
			$type = ($acceptable = $this->formats[$format]) ? $acceptable : $this->formats['string'];

			$i++;
			$this->result->bindValue($i, $value, $type);
		}

		return $this;
	}

	public function save (Database\Core $core)
	{
		if (empty($this->result)) return;

		$this->result->execute();
		
		return $this;
	}

	public function read ($mode = 'all')
	{
		switch ($mode)
		{
			case 'row':
				return $this->result->fetch();
				break;
			case 'object':
				return $this->result->fetchObject();
				break;
			default:
				return $this->result->fetchAll();
				break;
		}
	}

	public function affected_rows ()
	{
		return $this->result->rowCount();
	}

	public function last_insert_id ()
	{
		return $this->instance->lastInsertId();
	}
}