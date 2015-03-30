<?php
require_once('DriverDb.class.php');
require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * MyDb 
 *
 * MyDb Abstract Class to connect to different RDBMS using Singleton and Factory Pattern
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
 * @category   MyDb
 * @package    MyDb
 * @copyright  Copyright 2014 Jorge Alberto Ponce Turrubiates
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    1.0.0, 2014-09-01
 * @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
 */
class MyDb
{
	/**
     * Instance Handler to Singleton Pattern
     *
     * @var object $_instance Instance Handler
     * @access private
     */
	private static $_instance;

	/**
     * Instance Handler
     * @var object $_provider Abstract Connection Handler
     *
     * @access private
     */
	private $_provider;

	/**
     * Result Types ASSOC or ENUM
     *
     * @const ASSOC For Associative Results
     * @const ENUM  For Enumerate Results
     * @access private
     */
	const ASSOC = 1;
	const ENUM = 2;

	/**
     * LOG Object to manage error log
     *
     * @var object $_log Log Object
     * @access private
     */
	private $_log = null;

	/**
     * Provider Name
     * @var string $_providerName
     *
     * @access private
     */
	private $_providerName;

	/**
	 * Constructor of class is private for implements Singleton Pattern
	 *
	 * @param string $provider A valid provider if Abstract Class
	 * @param string $hostname A valid hostname
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS (For ODBC is a Data Source Name DSN)
	 * @return resource | null
	 */
	private function __construct($provider, $hostname, $username, $password, $dbname)
	{
		$logName = 'mydb_log-' . date("Y-m-d") . '.log';

		$this->_log = new Logger('MyDb');
		$this->_log->pushHandler(new StreamHandler($logName, Logger::ERROR));

		$this->_providerName = $provider;

		if(class_exists($provider)){
			$this->_provider = new $provider($hostname, $username, $password, $dbname);

			if(!$this->_provider->isConnected()){
				$this->_log->addError($this->_provider->getErrorCode());
				$this->_provider = null;
			}
		}
		else{
			$this->_provider = null;
			$this->_log->addError('Provider ' . $provider . ' Not Implented.');
		}
	}

	/**
	 * Implements Singleton Pattern
	 *
	 * @param string $provider A valid provider if Abstract Class
	 * @param string $hostname A valid hostname
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS (For ODBC is a Data Source Name (DSN))
	 * @return resource | null
	 */
	public static function getConnection($provider, $hostname, $username, $password, $dbname)
	{
		// If exists Instance return same Instance
		if(self::$_instance){
			return self::$_instance;
		}
		else{
			$class = __CLASS__;
			self::$_instance = new $class($provider, $hostname, $username, $password, $dbname);
			return self::$_instance;
		}
	}

	/**
	 * Implements Factory Pattern
	 *
	 * @param string $provider A valid provider if Abstract Class
	 * @param string $hostname A valid hostname
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS (For ODBC is a Data Source Name (DSN))
	 * @return resource | null
	 */
	public static function getInstance($provider, $hostname, $username, $password, $dbname)
	{
		// If exists Instance return null
		if(self::$_instance){
			trigger_error('I could not create instance a Singleton instance has previously compiled.', E_USER_ERROR);
			return null;
		}
		else{
			$class = __CLASS__;
			return new $class($provider, $hostname, $username, $password, $dbname);
		}
	}

	/**
	 * Return escaped string
	 *
	 * @param string $var string to be escaped
	 * @return string
	 */
	public function escape ($var)
	{
		if (!is_null($this->_provider)){
			return $this->_provider->escape($var);
		}
		else return $var;
	}

	/**
	 * Execute Command in RDBMS
	 *
	 * @param string $query A valid commando to execute in RDBMS
	 * @param array  $params Array with parameters to execute in RDBMS
	 * @param int    $resultType Type of Result default ASSOC
	 * @return array | false
	 */
	public function executeCommand($query, $params = null, $resultType = self::ASSOC)
	{
		if (!is_null($this->_provider)){
			$result = $this->_provider->query($query, $params);

			if($result === false) {
				$this->_log->addError($this->_provider->getErrorCode() . '(' . $query . ')');
			}
			else{
				$retArray = array();

				if (gettype($result) != 'boolean'){			
					if ($resultType == self::ASSOC){
						while($row = $this->_provider->fetchArrayAssoc($result)){
							$retArray[] = $row;
						}
					}
					else{
						while($row = $this->_provider->fetchArrayEnum($result)){
							$retArray[] = $row;
						}
					}

					/********************************************************
					If Mysql resolves the bug ("Commands out of sync; you can't run this command now") 
					of call 2 stored procedures, example:

					$conn->executeCommand('CALL sp1;');
					$conn->executeCommand('CALL sp2;');
					********************************************************/
					if ($this->getProvider() == 'MySQLDb'){
						//$result->close();
						@mysqli_free_result($result);
						$this->_provider->next_result();
					}
				}

				return $retArray;
			}
		}
		else
			// Not Connected
			return false;
	}

	/**
	 * Return if exists connection
	 *
	 * @return true | false
	 */
	public function isConnected()
	{
		return !is_null($this->_provider);
	}

	/**
	 * Return Provider
	 *
	 * @return string
	 */
	public function getProvider()
	{
		return $this->_providerName;
	}

	/**
	 * Return error when try clone object
	 *
	 * @return error
	 */
	public function __clone()
	{
		trigger_error('Clone is not permitted.', E_USER_ERROR);
	}
	
	/**
	 * Return error when try deserialize object
	 *
	 * @return error
	 */
	public function __wakeup()
	{
		trigger_error("Could not deserialize ". get_class($this) ." class.", E_USER_ERROR);
	}
}
?>