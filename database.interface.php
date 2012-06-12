<?php defined('HYPERION') or die('No direct script access.');
/**
 * Hyperion Database Interfance (Abstract)
 *
 * @package		Hyperion
 * @subpackage	Database
 * @author		Ian Lai <ian@fyianlai.com>
 * @copyright	Copyright (C) 2012, Ian Lai
 * @license		Modified BSD License (refer to LICENSE)
 */

/**
 * @abstract Database
 */
interface Database
{
	public function __construct ($host, $username, $password, $database);

	public function prepare ($stmt, Array $params, $types, $reference);

	public function create ($table, Array $data, $formats);
	public function read ($mode);
	public function update ($table, Array $update, Array $where, $formats, $limit);

	//public function reference (Array $vars);
}