<?php
/*
	miniDB

	miniDB is a lightweight mysqli extension class that adds a few additional features without changing how you query 
	the database. This is not intended as a database iterpitation layer, and does not build quieries, it simple makes 
	mysqli a little nicer to use.
*/
class db extends mysqli{

	public $last_query; // Stores the last query
	public $last_error; // Stores the last error

	// The database connection settings
	private $host = null;
	private $user = null;
	private $pass = null;
	private $db = null;
	private $charset = null;

	/*
		The constructor will attempt find the required connection information and then attempt to connect. If any 
		information is passed to the constructor it will attempt to use that, followed by the information stored in the 
		private variables of the db class, and finally by attempting to find a definition call db_[missing value]. If it 
		cannot find any peice of information (except the charset) it will throw an exception). If the charset is missing 
		it will use "utf8" 
	*/
	function __construct($host=null,$user=null,$pass=null,$db=null,$charset=null){
		// Since were doing a lot of the same things were going to form an array and loop through it
		$args = array(
			'host' => $host,
			'user' => $user,
			'pass' => $pass,
			'db' => $db,
			'charset' => $charset,
		);

		// loop through each, checking if it is null, then progressing through the above outlines alternatives, if none are found throw an exception
		foreach($args as $arg => $value){
			// If it was not passed a value the default is null
			if(is_null($value)){
				// If the private varible is not null is has been updated
				if(!is_null($this->$arg)){
					$args[$arg] = $this->$arg;
				}else{
					// if it is defined then we'll use that
					if(defined('db_'.$arg)){
						$args[$arg] = constant('db_'.$arg);
					}else{
						if($arg!='charset'){
							// Null was passed, private was not updated, and it was not defined, throw an exception
							throw new Exception('the "db.php" class was unable to find "'.$arg.'", was not passed, found in class, or found as a definition.');
						}
					}
				}
			}
		}

		// Try to connect to the db
		@$db = parent::mysqli($args['host'],$args['user'],$args['pass'],$args['db']);

		// If there is an error fail out with an exception
		if($this->connect_errno){
			throw new Exception('Unable to connect to database: '.$this->connect_error);
		}

		// Else set the charset and return the object
		if($args['charset']!=null){
			parent::set_charset($args['charset']);
		}
		return $db;

	}

	/*
		Simply closes the database
	*/
	function __destruct(){
		// Destroys the db class on close, fixes a bug in PHP 5.3
		parent::close();
	}

	/*
		These are just various aliases for the real_escape_string function
	*/
	function e($v){ return $this->escape($v); }
	function esc($v){ return $this->escape($v); }
	function escape($v){ return parent::real_escape_string($v); }

	/*
		The query function is a passthrough to the mysqli query function however it also logs the last query and the 
		last error in the process
	*/
	function query($query){
		$this->last_query = $query;
		$query = parent::query($query);
		$this->last_error = $this->error;
		return $query;
	}

	/*
		This will take a query and return the results in a multi-dimensional array. If there are no results then it will 
		return an empty array, if there was an error with the query it will throw a warning and return false. If your 
		only looking for a single result consider get_single(). If you pass the option key_column the function will take 
		the value of that column and use it as the key to the array containing that information. That column will not be 
		removed from the nested array. If it cannot find the key column it will revert to using standard numic keys
	*/
	function get($query,$key_column=null){

		// Run the provided query
		$query = $this->query($query);

		// Check if there was an error processing the query
		if(!$query){
			trigger_error($this->last_error,E_USER_WARNING);
			return false;
		}

		// Check if we have no results
		if($query->num_rows<1){
			return array();
		}

		// Otherwise crate a varible to store the results in and loop through them all, and return it
		$results = array();
		while($result = $query->fetch_assoc()){
			if(!is_null($key_column)){
				$results[$result[$key_column]] = $result;
			}else{
				$results[] = $result;
			}
		}
		return $results;
	}

	/*
		This will return a single result from the query. It does not modify the query, it simply only takes one result. 
		Will throw a warning on error and a blank array when there are no results.
	*/
	function get_single($query){

		// Run the provided query
		$query = $this->query($query);

		// Check if there was an error processing the query
		if(!$query){
			trigger_error($this->last_error,E_USER_WARNING);
			return false;
		}

		// Check if we have no results
		if($query->num_rows<1){
			return array();
		}

		// Return the first row regardless
		return $query->fetch_assoc();
	}

	/*
		This will insert an array into the database, Good for dumping something in in a sort of set-it-and-forget-it 
		sorta way.
	*/
	function insert($table,$data){
		
		// Create the appropraite data string
		$data_string_k = '';
		$data_string_v = '';
		foreach($data as $k => $v){
			$data_string_k .= ', `'.$k.'`';
			$data_string_v .= ', "'.$this->esc($v).'"';
		}
		$data_string = '('.substr($data_string_k,1).') values ('.substr($data_string_v,1).')';

		// Inserts an array into the specified table
		return $this->query('insert into `'.$this->e($table).'` '.$data_string);
	}

	/*
		This function updates. It works much the same way that insert does, however it also required a where clause.
	*/
	function update($table,$data,$where){

		// lets clean of the where clause
		$where = preg_replace('~^where ?~','',trim($where));

		// Process the form and trun it to an update_string
		$update_string = '';
		foreach($data as $k => $v){
			$update_string .= ', `'.$k.'` = "'.$this->e($v).'"';
		}
		$update_string = substr($update_string,2);

		// Craft and call the query
		return $this->query('update `'.$this->e($table).'` set '.$update_string.' where '.$where);
	}

}