Hyperion Database
=================

A modern, experimental, object-oriented (My)SQL database abstraction layer with a CRUD interface written in PHP.
I do not guarantee an unparalleled DBAL performance nor will I promise a well-documented source code.

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

### Create (Insert)
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
        // Value specifiers (i|d|s|b)
        'ss'
    );
    
The above code will be processed as:

    INSERT INTO `table_name` (`column_name_1`, `column_name_2`) VALUES ('insert_value_1', 'insert_value_2');
    
    
Boring Stuff
------------
### Copyright
Copyright (C) 2012, Ian Lai

### Licensing
Modified (a/k/a "New") BSD License - refer to the LICENSE file for more information or click [here](http://www.opensource.org/licenses/bsd-3-clause).