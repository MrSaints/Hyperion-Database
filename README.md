Hyperion Database
=================
A modern, experimental, object-oriented (My)SQL database abstraction layer (DBAL) with a CRUD interface written in PHP.
I do not guarantee an unparalleled DBAL performance nor will I promise a well-documented source code.
This database package was originally written for an experimental [Hyperion Engine](http://hion.trawl.in) branch to replace the legacy MySQL driver class.
The concept was loosely inspired by [Wordpress database class](http://codex.wordpress.org/Class_Reference/wpdb).


Key Principles
--------------
- Create, read, update and delete.
- Object-oriented; polymorphism.
- Do not repeat yourself.
- _You ain't gonna need it_.
- 0% tolerance for human error.
- 0% compatibility with PHP < 5.3.


Supported (My)SQL Drivers
-------------------------
- MySQLi
- PDO


Usage
-----
Please refer to the sample file named `example.php` for an example on how you can setup and begin using Hyperion database.


### Dependencies
- Core.php
- Adapter/IAdapter.php
- Adapter/(MySQLi|PDO)_Adapter.php


### Initiate Database Connection
`new Hion\Database\Core('Host', 'Username', 'Password', 'Schema');`


### Instantiate Driver Class
`new Hion\Database\Adapter\(MySQLi|PDO)_Adapter;`


### Query Building
Assuming `$database` contains the instantiated Hyperion database core:

    // Example
    $database = new Hion\Database\Core(...);

And `$PDO` and `$MySQLi` comprise of their respective instantiated (MySQLi|PDO)_Adapter class (MySQL driver):

    // Example (MySQLi)
    $MySQLi = new Hion\Database\Adapter\MySQLi_Adapter;
    // OR (PDO)...
    $PDO = new Hion\Database\Adapter\PDO_Adapter;


#### SELECT
_Applicable only for READ methods_.

To select a set of columns from a given table call the `select (String $table, Array $columns)` function.
`$table` must be the name of the table to make the column and row selections in.
`$columns` must be the name of the columns you wish to select.


#### WHERE
_Applicable only for READ, UPDATE and DELETE methods_.

Call the `where (String $column, $operator = '=', $value, $format = 'string', $prefix = '')` function to create a single query condition.
The function can be chained to form multiple conditions.
`$prefix` is an optional seperator (e.g. AND / &&, OR / ||) between each WHERE condition.
All other parameters are self-explanatory.


#### LIMIT / OFFSET
_Applicable only for READ, UPDATE and DELETE methods_.

Use `limit (Integer $limit)` and/or `offset (Integer $offset)` to limit the number of affected rows.


#### CREATE
Call the `create (String $table, Array $data, Array $formats)` function to insert a new row.
`$table` must be the name of the table to execute the create statement.
`$data` must be a key-value array where the _key_ represents the column name and the _value_ represents the column value.
`$formats` must be an array containing the data types for the corresponding bind variables in `$data` in string format. Possible specifiers include _boolean, integer, string and blob_ for both drivers. An additional _double_ specifier is available in the MySQLi adapter and a _null_ specifier is available in the PDO adapter. The ID of the last inserted row or sequence value will be returned upon a successful insertion.
Click [here (MySQLi)](http://my.php.net/manual/en/mysqli-stmt.bind-param.php) or [here (PDO)](http://www.php.net/manual/en/pdostatement.bindvalue.php) for more information.

##### Example
    $database->create (
        'table_name',
        array (
            'column_name_1' =>  'insert_value_1',
            'column_name_2' =>  'insert_value_2',
        ),
        // Specifiers (boolean|integer|string|blob)
        array (
            'string',
            'string',
        )
    );

The above code will be processed as:

    INSERT INTO `table_name` (`column_name_1`, `column_name_2`) VALUES ('insert_value_1', 'insert_value_2');--


#### READ
Call the `read (Adapter\IAdapter $adapter, $mode = 'all')` function to read a single variable or entire row(s) in a database.
A result set (a prepared query) must be present beforehand to successfully obtain the results of the query.
The instantiated (MySQLi|PDO)_Adapter class (MySQL driver), `$MySQLi` or `$PDO`, must be injected into the first param of the function, `$adapter`.
`$mode` is an optional parameter and may be set to one of the two pre-defined selection and output types _(all|row)_ (an additional _var_ option is available for the MySQLi adapter and an _object_ option is available in the PDO adapter).
If the `$mode` parameter is not supplied, it will simply default to _'all'_ which will return all selected rows.
_var_ however, will simply return a single row-column variable.

##### Example
    // PDO
    $database->select('table_name', array('column_name'))
             ->where('condition_column_name', '=', 'condition_value', 'string')
             ->read($PDO);

The above code will be processed as:

    SELECT `column_name` FROM `table_name` WHERE `condition_column_name` = 'condition_value';--

Furthermore, as no parameter has been set for `$mode`, the above example will return all rows where `condition_column_name` is equal to (string) `condition_value`.
The returned data will be in the form of an associative array (multidimensional if the `$mode` is set to _'all'_).


#### UPDATE
Call the `update (String $table, Array $data, Array $formats)` function to update row(s) in a table.
`$table` must be the name of the table to execute the create statement.
A result set (a prepared query) must be present beforehand to successfully obtain the results of the query.
`$data` must contain the column(s) (as array keys) that are to be updated with the new value(s) (as array values) - key-value pairs.
`$formats` must be an array containing the data types for the corresponding bind variables in `$data` in string format. Possible specifiers include _boolean, integer, string and blob_ for both drivers. An additional _double_ specifier is available in the MySQLi adapter and a _null_ specifier is available in the PDO adapter.
Upon a successful update, the number of affected rows will be returned.
You are not required to specify the maximum number of rows to be updated and on default, it updates all matched rows.

##### Example
    // MySQLi
    $database->update('table_name', array('column_name' => 'new_column_value'), array('integer'))
             ->where('condition_column_name', '=', 'condition_value', 'string')
             ->save($MySQLi);

The above code will be processed as:

    UPDATE `table_name` SET `column_name` = 'new_column_value' WHERE `condition_column_name` = 'condition_value';--

If `->limit(5)` is defined before `save(...)` is called, an additional:

    LIMIT 5

Will be appended to the above query string and executed.


### Auto Commit
_Coming Soon_


Boring Stuff
------------
### Acknowledgements
- [WordPress](http://www.wordpress.org).
- [Net Tuts+](http://net.tutsplus.com).

### Copyright
Copyright (C) 2013, Ian Lai.

### Licensing
Modified (a/k/a "New") BSD License - refer to the LICENSE file for more information or click [here](http://www.opensource.org/licenses/bsd-3-clause).
