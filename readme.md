# miniDB

miniDB is a mysqli helper class making it just a little more user friendly. Unllike some other database classes this is not a complete interpritation layer and does not include features one would have like anquery builder. This is simply to make rapid prototyping or development in MySQL (or compatible) exclusive enviroments a tad simpler. 

## Requirements
A modern enough version of PHP to support the mysqli class.

## Usage
When creating a new instance of the db class you will need to provide the appropraite database connection info, that can be done in 1 of 3 ways.

- Pass the apprpriate information to the constructor (see details below).
- Update the private vars at the top of the class (they should be self explanitory),
- Create some definitions containing the connection information. The definitions need to be names `db_host`,`db_user`,`db_pass`,`db_db` (yess that is a tad redundant),`db_charset`.
Any information can be passed, with information passed to the contructor taking priority, followed by the private vars, then the definitions.

This class contains the following variables as functions functions:

##### $last_query
Contains a copy of the last query ran through the class.

##### $last_query
Contains a 'error' of the last query ran through the class.

##### $__construct(`[$host]`,`[$user]`,`[$pass]`,`[$db]`,`[$charset]`)
Opens a new connection to the database with the passed varibles or the ones found by other means as described above. All variables are need to connect to the database except the charset, which will default to utf8 if no value is found. When creating a new instace you can skip any field by passing null.

##### __destruct()
Closes the database.

##### e(`$value`), esc(`$value`), escape(`$value`)
These are all aliases of `$mysqli->real_escape_string($value)`.

##### query(`$query`)
Passes the provided query to `$mysqli->query($query)` and storing a copy of the query in the `last_query` variable. After running the query it stores the error (if there was one) in the `last_error` variable.

##### get(`$query`)
Will return the results of the `$query` in a multidimensional array regardless of the number of rows returned. If there is an error with the query a warning is thrown and false is returned. If there are no results a blank array is returned.

##### get_single(`$query`)
Behaves the same as `get($query)` except it will only return the first result in a associative array.

##### insert(`$table`,`$data`)
This will insert an array into the database `$table`, Good for dumping something in in a sort of set-it-and-forget-it.

##### update(`$table`,`$data`,`$where`)
This function updates the `$table` with the `$data` array. The where clause is used in the update query to limit what is being updated. 

## Note
miniDB is not a replacement for a full fledged database class, that is not the goal of this class. If you need features like a query builder or an interpritation layer so you can change database language easier then this is not the class for you. This is more designed with direct mysqli interaction and rapid prototyping in mind. So why build a class for something so pointless? Because I hate typing out `real_escape_string()`. While this may (and probably will) be expanded over time I do not have the end goal of creating yet another full featured database class, there are already plenty of good ones out there.