<?php

/**
 * DriverDb
 * 
 * DriverDb Abstract Class to manage connection
 *
 * Copyright 2014 Jorge Alberto Ponce Turrubiates
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category   DriverDb
 * @package    DriverDb
 * @copyright  Copyright 2014 Jorge Alberto Ponce Turrubiates
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    1.0.0, 2014-09-01
 * @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
 */
abstract class DriverDb
{

	/**
     * Connection Handler
     *
     * @var object $_connection Handler Connection
     * @access private
     */
	protected $_connection = null;

	/**
     * Error Code
     *
     * @var string $_errorCode Error Code + Error Message
     * @access private
     */
	protected $_errorCode = null;

	/**
	 * Connect to RDBMS
	 *
	 * @param string $hostname A valid hostname
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS (For ODBC is a Data Source Name (DSN))
	 * @param int 	 $port   RDBMS Listen Port
	 * @return resource | null
	 */
	public abstract function connect($hostname, $username, $password, $dbname, $port);

	/**
	 * Execute a query command in RDBMS
	 *
	 * @param string $query Query command to execute
	 * @param array $params Array of parameters in query (default = null)
	 * @return object | false
	 */
	public abstract function query($query, $params=null);

	/**
	 * Fetch a row as an array
	 *
	 * @param object $resultSet Resultset of execute query
	 * @return array (enum)
	 */
	public abstract function fetchArrayEnum($resultSet);

	/**
	 * Fetch a row as an associative array
	 *
	 * @param object $resultSet Resultset of execute query
	 * @return array (assoc)
	 */
	public abstract function fetchArrayAssoc($resultSet);

	/**
	 * Return escaped string
	 *
	 * @param string $var string to be escaped
	 * @return string
	 */
	public abstract function escape($var);

	/**
	 * Return if exists connection
	 *
	 * @return boolean
	 */
	public function isConnected()
	{
		return !is_null($this->_connection);
	}

	/**
	 * Return true if error exists
	 *
	 * @return boolean
	 */
	public function hasError()
	{
		return !is_null($this->_errorCode);
	}

	/**
	 * Return last error message
	 *
	 * @return string
	 */
	public function getErrorCode()
	{
		return $this->_errorCode;
	}

	/**
	 * Check if PHP Extension is loaded
	 * you can check extensions with php -m
	 *
	 * @param string $phpExtension PHP Extension
	 * @return boolean
	 */
	public function checkExtension($phpExtension)
	{
		$this->_errorCode = null;

		if (!extension_loaded($phpExtension)) {
			// dl($phpExtension . '.dll') to load extension
			if (strpos(PHP_OS, 'WIN') !== false){
			    $this->_errorCode = '-1 - Could not load the Module ' . $phpExtension . '.dll';
			}
			else{
			    $this->_errorCode = '-1 - Could not load the Module ' . $phpExtension . '.so';
			}
		}

		return is_null($this->_errorCode);
	}
}

/**
 * MySQL Class to manage connection to MySQL or MariaDb
 *
 * @category   MySQLDb
 * @package    DriverDb
 * @copyright  Copyright 2014 Jorge Alberto Ponce Turrubiates
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    1.0.0, 2014-09-01
 * @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
 */
class MySQLDb extends DriverDb
{

	/**
	 * Constructor of the class
	 *
	 * @param string $hostname A valid hostname
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS (For ODBC is a Data Source Name DSN)
	 * @param int 	 $port   RDBMS Listen Port
	 */
	public function __construct($hostname, $username, $password, $dbname, $port)
	{
		if ($this->checkExtension('mysqli')){
			$this->connect($hostname, $username, $password, $dbname, $port);
		}	
	}

	/**
	 * Connect to Mysql or MariaDb
	 *
	 * @param string $hostname A valid hostname
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS (For ODBC is a Data Source Name DSN)
	 * @param int 	 $port   RDBMS Listen Port
	 * @return resource | null
	 */
	public function connect($hostname, $username, $password, $dbname, $port)
	{
		$this->_connection = @mysqli_connect($hostname, $username, $password, $dbname, $port);

		if (!$this->_connection) {
			$this->_connection = null;
			$this->_errorCode = (string)mysqli_connect_errno() . '-' . mysqli_connect_error();
		}
		else{
			mysqli_set_charset($this->_connection, "utf8");
			//mysqli_query($this->_connection, "SET NAMES 'utf8' COLLATE 'utf8_spanish_ci'");
		}
	}

	/**
	 * Execute a query command in RDBMS
	 *
	 * @param string $query Query command to execute
	 * @param array $params Array of parameters in query (default = null)
	 * @return object | false
	 */
	public function query($query, $params=null)
	{
		$resultSet = null;

		if (is_null($params)){
			if ($resultSet = mysqli_query($this->_connection, $query)){
				return $resultSet;
			}
			else{
				$this->_errorCode = (string)mysqli_errno($this->_connection) . '-' . mysqli_error($this->_connection);
				return false;
			}
		}
		else{
			// if is associative convert to enumerate
			if ($this->is_associative_array($params))
				$params = array_values($params);
			
			if ($prepared_stmt = mysqli_prepare($this->_connection, $query)){
				$paramTypes = $this->arrayMySQLType($params);

				if (count($params) == 1){
					$prepared_stmt->bind_param($paramTypes, $params[0]);
				}
				else{
					array_unshift($params, $paramTypes);
				
					$tmp_params = array();
		        	foreach($params as $key => $value) $tmp_params[$key] = &$params[$key];

		        	call_user_func_array(array($prepared_stmt, 'bind_param'), $tmp_params); 
				}	

				if(mysqli_stmt_execute($prepared_stmt)){
					$resultSet = mysqli_stmt_get_result($prepared_stmt);

		        	mysqli_stmt_close($prepared_stmt);

		        	return $resultSet;
		        }
		        else {
		        	$this->_errorCode = (string)mysqli_stmt_errno($prepared_stmt) . '-' . mysqli_stmt_error($prepared_stmt);
		        	return false;
		        }
			}
			else {
				$this->_errorCode = (string)mysqli_errno($this->_connection) . '-' . mysqli_error($this->_connection);
				return false;
			}
		}
	}
	
	/**
	 * Fetch a row as an array
	 *
	 * @param object $resultSet Resultset of execute query
	 * @return array (enum)
	 */
	public function fetchArrayEnum($resultSet)
	{
		return mysqli_fetch_array($resultSet, MYSQLI_NUM);
	}

	/**
	 * Fetch a row as an associative array
	 *
	 * @param object $resultSet Resultset of execute query
	 * @return array (assoc)
	 */
	public function fetchArrayAssoc($resultSet)
	{
		return mysqli_fetch_array($resultSet, MYSQLI_ASSOC);
	}

	/**
	 * Return escaped string
	 *
	 * @param string $var string to be escaped
	 * @return string
	 */
	public function escape($var)
	{
		return mysqli_real_escape_string($this->_connection, $var);
	}

	/**
	 * Get Next Resultset
	 * resolves the bug: "Commands out of sync; you can't run this command now"
	 */
	public function next_result()
	{
		@mysqli_next_result($this->_connection);
		//$this->_connection->next_result();
	}

	/**
	 * Return a string with type parameters to execute prepared statement
	 *
	 * @param array $arrayParams Query command to execute
	 * @return string
	 * @access private
	 */
	private function arrayMySQLType($arrayParams)
	{
		$arrayTypes = '';
		$size = count($arrayParams);

		for ($i = 0; $i <= $size-1; $i++) {
		    if (gettype($arrayParams[$i])=='string'){
		    	$arrayTypes = $arrayTypes . 's';
		    } 
		    else if(gettype($arrayParams[$i])=='integer') {
		    	$arrayTypes = $arrayTypes . 'i';
		    } 
		    else if(gettype($arrayParams[$i])=='double') {
		    	$arrayTypes = $arrayTypes . 'd';
		    }
		    else{
		    	$arrayTypes = $arrayTypes . 'b'; // BLOB
		    }
		}

		return $arrayTypes;
	}

	/**
	 * Return true if the array is associative else return false
	 * 
	 * @param  array  $array Array Parameters
	 * @return boolean
	 */
	private function is_associative_array($array)
	{
		return (is_array($array) && !is_numeric(implode("", array_keys($array))));
	}
}

/**
 * SQLServerDb Class to manage connection to SQL Server in Windows
 *
 * @category   SQLServerDb
 * @package    DriverDb
 * @copyright  Copyright 2014 Jorge Alberto Ponce Turrubiates
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    1.0.0, 2014-09-01
 * @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
 */
class SQLServerDb extends DriverDb
{

	/**
	 * Constructor of the class
	 *
	 * @param string $hostname A valid hostname
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS (For ODBC is a Data Source Name DSN)
	 * @param int 	 $port   RDBMS Listen Port
	 */
	public function __construct($hostname, $username, $password, $dbname, $port)
	{
		if ($this->checkExtension('sqlsrv')){
			$this->connect($hostname, $username, $password, $dbname, $port);
		}	
	}

	/**
	 * Connect to SQL Server 2x using Microsoft PHP Official Driver
	 *
	 * @param string $hostname A valid hostname
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS (For ODBC is a Data Source Name DSN)
	 * @param int 	 $port   RDBMS Listen Port
	 * @return resource | null
	 */
	public function connect($hostname, $username, $password, $dbname, $port)
	{
		$connectionInfo = array("Database"=>$dbname, "UID"=>$username, "PWD"=>$password, "CharacterSet" => "UTF-8", "MultipleActiveResultSets" => false);

		$hostname = $hostname . ',' . $port;

		$this->_connection = @sqlsrv_connect($hostname, $connectionInfo);

		if (!$this->_connection) {
			$this->_connection = null;
			$this->loadError();
		}
	}

	/**
	 * Execute a query command in RDBMS
	 *
	 * @param string $query Query command to execute
	 * @param array $params Array of parameters in query (default = null)
	 * @return object | false
	 */
	public function query($query, $params=null)
	{
		$resultSet = null;

		if (is_null($params)){
			if ($resultSet = sqlsrv_query($this->_connection, $query)){
				return $resultSet;
			}
			else{
				$this->loadError();
				return false;
			}
		}
		else{
			if ($resultSet = sqlsrv_query($this->_connection, $query, $params)){
				return $resultSet;
			}
			else{
				$this->loadError();
				return false;
			}
		}
	}
	

	/**
	 * Fetch a row as an array
	 *
	 * @param object $resultSet Resultset of execute query
	 * @return array (enum)
	 */
	public function fetchArrayEnum($resultSet)
	{
		return sqlsrv_fetch_array($resultSet, SQLSRV_FETCH_NUMERIC);
	}
	
	/**
	 * Fetch a row as an associative array
	 *
	 * @param object $resultSet Resultset of execute query
	 * @return array (assoc)
	 */
	public function fetchArrayAssoc($resultSet)
	{
		return sqlsrv_fetch_array($resultSet, SQLSRV_FETCH_ASSOC);
	}
	

	/**
	 * Return escaped string
	 *
	 * @param string $var string to be escaped
	 * @return string
	 */
	public function escape($var)
	{
		if(get_magic_quotes_gpc()){
			$var = stripslashes($var);
		}

		return str_replace("'", "''", $var);
	}

	/**
	 * Load Error Code
	 *
	 * @access private
	 */
	private function loadError()
	{
		if( ($errors = sqlsrv_errors() ) != null) {
	        foreach( $errors as $error ) {
	        	$this->_errorCode = $error['SQLSTATE'] . '-' . $error['message'];
	        }
	    }
	}
}

/**
 * ODBCDb Class to manage connection to ODBC Conections
 *
 * @category   ODBCDb
 * @package    DriverDb
 * @copyright  Copyright 2014 Jorge Alberto Ponce Turrubiates
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    1.0.0, 2014-09-01
 * @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
 */
class ODBCDb extends DriverDb
{

	/**
	 * Constructor of the class
	 *
	 * @param string $hostname A valid hostname default 'localhost'
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS (For ODBC is a Data Source Name DSN)
	 * @param int 	 $port   RDBMS Listen Port
	 */
	public function __construct($hostname = 'localhost', $username, $password, $dbname, $port)
	{
		if ($this->checkExtension('odbc')){
			$this->connect($hostname, $username, $password, $dbname, $port);
		}	
	}

	/**
	 * Connect to ODBC DSN using Microsoft PHP Official Driver
	 *
	 * @param string $hostname A valid hostname
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS (For ODBC is a Data Source Name DSN)
	 * @param int 	 $port   RDBMS Listen Port
	 * @return resource | null
	 */
	public function connect($hostname, $username, $password, $dbname, $port)
	{
		$this->_connection = @odbc_connect($dbname, $username, $password);

		if (!$this->_connection) {
			$this->_errorCode =  odbc_error() . '-' . odbc_errormsg();
			$this->_connection = null;
		}
	}

	/**
	 * Execute a query command in RDBMS
	 *
	 * @param string $query Query command to execute
	 * @param array $params Array of parameters in query (default = null)
	 * @return object | false
	 */
	public function query($query, $params=null)
	{
		$resultSet = null;

		if (is_null($params)){
			if ($resultSet = @odbc_exec($this->_connection, $query)){
				return $resultSet;
			}
			else{
				$this->_errorCode =  odbc_error($this->_connection) . '-' . odbc_errormsg($this->_connection);
				return false;
			}
		}
		else{
			if ($prepared_stmt = @odbc_prepare($this->_connection, $query)){
				$resultSet = @odbc_execute($prepared_stmt, $params);

				if ($resultSet) {
					return $prepared_stmt;
				}
				else{
					$this->_errorCode =  odbc_error($this->_connection) . '-' . odbc_errormsg($this->_connection);
					return false;
				}
			}
			else{
				$this->_errorCode =  odbc_error($this->_connection) . '-' . odbc_errormsg($this->_connection);
				return false;
			}
		}
	}

	/**
	 * Fetch a row as an array
	 *
	 * @param object $resultSet Resultset of execute query
	 * @return array (enum)
	 */
	public function fetchArrayEnum($resultSet)
	{
		if (gettype($resultSet) == 'resource'){
			$enumArray = array();

			@odbc_fetch_into($resultSet,$enumArray);

			return $enumArray;
		}
	}

	/**
	 * Fetch a row as an associative array
	 *
	 * @param object $resultSet Resultset of execute query
	 * @return array (assoc)
	 */
	public function fetchArrayAssoc($resultSet)
	{
		if (gettype($resultSet) == 'resource'){
			return @odbc_fetch_array($resultSet);
		}
	}
	
	/**
	 * Return escaped string
	 *
	 * @param string $var string to be escaped
	 * @return string
	 */
	public function escape($var)
	{
		if(get_magic_quotes_gpc()){
			$var = stripslashes($var);
		}
		return str_replace("'", "''", $var);
	}
}

/**
 * PostgreSQLDb Class to manage connection to PostgreSQL
 *
 * @category   PostgreSQLDb
 * @package    DriverDb
 * @copyright  Copyright 2014 Jorge Alberto Ponce Turrubiates
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    1.0.0, 2014-09-01
 * @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
 */
class PostgreSQLDb extends DriverDb
{
	/**
	 * Constructor of the class
	 *
	 * @param string $hostname A valid hostname
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS (For ODBC is a Data Source Name DSN)
	 * @param int 	 $port   RDBMS Listen Port
	 */
	public function __construct($hostname, $username, $password, $dbname, $port)
	{
		if ($this->checkExtension('pgsql')){
			$this->connect($hostname, $username, $password, $dbname, $port);
		}	
	}

	/**
	 * Connect to PostgreSQL
	 *
	 * @param string $hostname A valid hostname
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS (For ODBC is a Data Source Name DSN)
	 * @param int 	 $port   RDBMS Listen Port
	 * @return resource | null
	 */
	public function connect($hostname, $username, $password, $dbname, $port)
	{
		$connString = "host=$hostname port=" . $port . " dbname=$dbname user=$username password=$password";

		$this->_connection = @pg_connect($connString);

		if (!$this->_connection) {
			$this->_connection = null;
			$this->_errorCode = '0 - Could not connect to PostgreSQL';
		}
		
		/*
		$status = pg_connection_status($this->_connection);

		if ($status === PGSQL_CONNECTION_BAD) {
			$this->_connection = null;
			$this->_errorCode = '0 - Could not connect to PostgreSQL';
		}
		*/
	}

	/**
	 * Execute a query command in RDBMS
	 * NOTE: The parameters in SQL Query must be $#, example: 'SELECT * FROM MYTABLE WHERE ID = $1 AND DESCRIPTION = $2'
	 *
	 * @param string $query Query command to execute
	 * @param array $params Array of parameters in query (default = null)
	 * @return object | false
	 */
	public function query($query, $params=null)
	{
		$resultSet = null;

		if (is_null($params)){
			if ($resultSet = @pg_query($this->_connection, $query)){
				return $resultSet;
			}
			else{
				$this->_errorCode = '1 - ' . pg_last_error($this->_connection);
				return false;
			}
		}
		else{
			if ($resultSet = @pg_query_params($this->_connection, $query, $params)){
				return $resultSet;
			}
			else{
				$this->_errorCode = '2 - ' . pg_last_error($this->_connection);
				return false;
			}
		}
	}

	/**
	 * Fetch a row as an array
	 *
	 * @param object $resultSet Resultset of execute query
	 * @return array (enum)
	 */
	public function fetchArrayEnum($resultSet)
	{
		return pg_fetch_array($resultSet, NULL, PGSQL_NUM);
	}

	/**
	 * Fetch a row as an associative array
	 *
	 * @param object $resultSet Resultset of execute query
	 * @return array (assoc)
	 */
	public function fetchArrayAssoc($resultSet)
	{
		return pg_fetch_array($resultSet, NULL, PGSQL_ASSOC);
	}

	/**
	 * Return escaped string
	 *
	 * @param string $var string to be escaped
	 * @return string
	 */
	public function escape($var)
	{
		return pg_escape_string($var);
	}
}

/**
 * MySQL PDO Class to manage connection to MySQL or MariaDb
 *
 * @category   MySQLPDO
 * @package    DriverDb
 * @copyright  Copyright 2017 Jorge Alberto Ponce Turrubiates
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    1.0.0, 2017-10-11
 * @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
 */
class MySQLPDO extends DriverDb
{
	/**
	 * Prepared Query
	 * 
	 * @var null
	 */
	private $pQuery = null;

	/**
	 * Constructor of the class
	 *
	 * @param string $hostname A valid hostname
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS (For ODBC is a Data Source Name DSN)
	 * @param int 	 $port   RDBMS Listen Port
	 */
	public function __construct($hostname, $username, $password, $dbname, $port)
	{
		if ($this->checkExtension('pdo_mysql')){
			$this->connect($hostname, $username, $password, $dbname, $port);
		}	
	}

	/**
	 * Connect to Mysql or MariaDb
	 *
	 * @param string $hostname A valid hostname
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS (For ODBC is a Data Source Name DSN)
	 * @param int 	 $port   RDBMS Listen Port
	 * @return resource | null
	 */
	public function connect($hostname, $username, $password, $dbname, $port)
	{
		$dsn = "mysql:host=" . $hostname .";port=" . $port . ";dbname=" . $dbname;

		try{
			$this->_connection = new PDO($dsn, $username, $password);
			$this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} 
		catch(PDOException $e){
			$this->_connection = null;
			$this->_errorCode = '0 - Could not connect to Mysql';
		}
	}

	/**
	 * Execute a query command in RDBMS
	 *
	 * @param string $query Query command to execute
	 * @param array $params Array of parameters in query (default = null)
	 * @return object | false
	 */
	public function query($query, $params=null)
	{
		$resultSet = array('MSG' => 'OK');

		if (is_null($params)){
			try{
				$this->pQuery = $this->_connection->prepare($query);
				$this->pQuery->execute();

				return $resultSet;
			} 
			catch(PDOException $e){
				$this->_errorCode = $e->getMessage();
				return false;
			}
		}
		else{
			try{
				$this->pQuery = $this->_connection->prepare($query);
				$this->pQuery->execute($params);

				return $resultSet;
			} 
			catch(PDOException $e){
				$this->_errorCode = $e->getMessage();
				return false;
			}
		}
	}
	
	/**
	 * Fetch a row as an array
	 *
	 * @param object $resultSet Resultset of execute query
	 * @return array (enum)
	 */
	public function fetchArrayEnum($resultSet)
	{
		$result = null;

		try{
			$result = $this->pQuery->fetchAll(PDO::FETCH_NUM);
		} 
		catch(PDOException $e){
			$result = null;
		}

		return $result;
	}

	/**
	 * Fetch a row as an associative array
	 *
	 * @param object $resultSet Resultset of execute query
	 * @return array (assoc)
	 */
	public function fetchArrayAssoc($resultSet)
	{
		$result = null;

		try{
			$result = $this->pQuery->fetchAll(PDO::FETCH_ASSOC);
		} 
		catch(PDOException $e){
			$result = null;
		}

		return $result;
	}

	/**
	 * Return escaped string
	 *
	 * @param string $var string to be escaped
	 * @return string
	 */
	public function escape($var)
	{
		return $var;
	}
}

?>
