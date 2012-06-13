Hyperion Database
=================

A modern, experimental, object-oriented (My)SQL database abstraction layer with a CRUD interface written in PHP.
I do not guarantee an unparalleled DBAL performance nor will I promise a well-documented source code.
This database package was originally written for an experimental [Hyperion Engine](http://hion.trawl.in) branch to replace the legacy MySQL driver class.

Key Principles
--------------
- Create, read, update and delete.
- Object-oriented.
- Do not repeat yourself.
- _You ain't gonna need it_.
- 0% tolerance for human error.
- 0% compatibility with PHP < 5.3.

Supported (My)SQL Drivers
-------------------------
- MySQLi

Usage
-----
Assuming `$model` contains the instantiated MySQLi_DBAL class:

    // Example
    $model = new MySQLi_DBAL(...);

### Create (INSERT)
Call the `create ($table, Array $data, $formats)` function to insert a new row.
`$formats` must be a string containing the data types for the corresponding bind variables in `$data`.
Click [here](http://my.php.net/manual/en/mysqli-stmt.bind-param.php) for more information.

    // Example
    $model->create (
        'table_name',
        array (
            'column_name_1' =>  'insert_value_1',
            'column_name_2' =>  'insert_value_2',
        ),
        // Specifiers (i|d|s|b)
        'ss'
    );
    
The above code will be processed as:

    INSERT INTO `table_name` (`column_name_1`, `column_name_2`) VALUES ('insert_value_1', 'insert_value_2');

### Read (SELECT)
Call the `read ($mode = 'all')` function to read a single variable or entire row(s) in a database.
A result set (a prepared and executed query) must be present beforehand to successfully obtain the results of the query.
`$mode` is an optional parameter and may be set to one of the three pre-defined selection and output types _(all|row|var)_.
If the `$mode` parameter is not supplied, it will simply default to _'all'_ which will return all selected rows.
_var_ however, will simply return a single row-column variable.

    // Example (with method chaining)
    $model->prepare (
        // Query statement
        'SELECT `column_name` FROM `table_name` WHERE `x_column_name` = ? && `y_column_name` = ?',
        // Values to be binded to their corresponding markers
        array (
            'x_column_name_value_to_match',
            '5',
        ),
        // Specifiers (i|d|s|b)
        'si'
    )->execute(false)->read();

The above code will be processed as:

    SELECT `column_name` FROM `table_name` WHERE `x_column_name` = 'x_column_name_value_to_match' && `y_column_name` = 5;

Furthermore, as no parameter has been set for `$mode`, the above example will return all rows where _x_column_name_ is equal to (string) _x_column_name_value_to_match_
and _y_column_name_ is equal to the (integer) _5_.
The returned data will be in the form of an associative array (multidimensional if the `$mode` is set to _'all'_).

### Update (UPDATE)
Call the `update ($table, Array $update, Array $where, $formats, $limit = false)` function to update row(s) in a table.
Both `$update` and `$where` must be an array with column => value pairs as its data.
`$update` must contain the column(s) (as array keys) that are to be updated with the new value(s) (as array values).
To set the maximum number of rows to be updated, pass an integer to the 5th parameter (`$limit`) of the `update ()` method.
You are not required to specify the maximum number of rows to be updated and on default, it updates all matched rows.

    // Example
    $model->update (
        'table_name',
        // UPDATE: column = value, column_2 = value_2...
        array(
            'column_to_update'		    =>	'new_value',
            'column_to_update_2'		=>  'new_value_2',
        ),
        // WHERE: column = value &&...
        array(
            'where_column_x'	    	=>	'value_x',
        ),
        // Specifiers (i|d|s|b)
        'sss'
    );

The above code will be processed as:

    UPDATE `table_name` SET `column_to_update` = 'new_value', `column_to_update_2` = 'new_value_2'
        WHERE `where_column_x` = 'value_x'

If `$limit` is defined as _5_, an additional:

    LIMIT 5

Will be appended to the above query string and executed.

### Auto Commit
On default, all modifications made to a database will automatically be committed.
If you would like to manually commit each or all changes at once (which can be beneficial for your application's performance), call:

    $model->autocommit(false);
    
The above code will disable autocommit and no changes will be made to the database until the following is called:

    $model->commit();
    
_This function does not work with non transactional table types (like MyISAM or ISAM)._
Click [here](http://php.net/manual/en/mysqli.autocommit.php) for more information.

Boring Stuff
------------
### Acknowledgements
- [WordPress](http://www.wordpress.org)
- [Net Tuts+](http://net.tutsplus.com)

### Copyright
Copyright (C) 2012, Ian Lai

### Licensing
Modified (a/k/a "New") BSD License - refer to the LICENSE file for more information or click [here](http://www.opensource.org/licenses/bsd-3-clause).