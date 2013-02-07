<?php
namespace Hion\Database\Adapter;
use Hion\Database as Database;

/**
 * Hyperion Database Interface
 *
 * @package		Hyperion
 * @subpackage	Database
 * @author		Ian Lai
 * @copyright	Copyright (C) 2013, Ian Lai
 * @license		Modified BSD License (refer to LICENSE)
 */

interface IAdapter
{
	public function init (Database\Core $core);
	public function prepare (Database\Core $core);
	public function save (Database\Core $core);
	public function read ($mode);

	public function affected_rows ();
	public function last_insert_id ();	
}