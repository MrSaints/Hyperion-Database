<?php
/*
 * Dependencies
 */
include 'Core.php';
include 'Adapter/IAdapter.php';
include 'Adapter/PDO_Adapter.php';
include 'Adapter/MySQLi_Adapter.php';

/*
 * Database connection
 */
$database = new Hion\Database\Core('127.0.0.1', 'root', '', 'pockyms');

/*
 * PHP MySQL adapter/driver
 */
$PDO = new Hion\Database\Adapter\PDO_Adapter;
$MySQLi = new Hion\Database\Adapter\MySQLi_Adapter;

/*
 * UPDATE using MySQLi
 */
$update = $database->update('accounts', array('points' => '1337'), array('integer'))
					->where('name', '=', 'Leet', 'string')
					->save($MySQLi);

/*
 * SELECT using PDO
 */
$select = $database->select('accounts', array('points'))
					->where('name', '=', 'Leet', 'string')
					->read($PDO, 'object');