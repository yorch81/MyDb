<?php
require_once('MyDb.class.php');

// Mysql Example
$provider = 'MySQLDb';
$hostname = '';
$username = '';
$password = '';
$dbname   = '';

$dbMySQL = MyDb::getInstance($provider, $hostname, $username, $password, $dbname);  

$query = sprintf("SELECT 1 AS %s", $dbMySQL->escape($provider));

// Associate
print_r($dbMySQL->executeCommand($query));  

// Enumerate
print_r($dbMySQL->executeCommand($query, null, MyDb::ENUM));  

$dbMySQL = null;

// SQL Server Example
$provider = 'SQLServerDb';
$hostname = '';
$username = '';
$password = '';
$dbname   = '';

$dbSQLServer = MyDb::getInstance($provider, $hostname, $username, $password, $dbname);  

$query = sprintf("SELECT 1 AS %s", $dbSQLServer->escape($provider));

// Associate
print_r($dbSQLServer->executeCommand($query));  

// Enumerate
print_r($dbSQLServer->executeCommand($query, null, MyDb::ENUM));  

$dbSQLServer = null;

// ODBC Example
$provider = 'ODBCDb';
$hostname = 'localhost';
$username = '';
$password = '';
$dbname   = 'ODBCDSN'; // ODBC Data Source Name

$dbODBC = MyDb::getInstance($provider, $hostname, $username, $password, $dbname);  

$query = sprintf("SELECT 1 AS %s", $dbODBC->escape($provider));

// Associate
print_r($dbODBC->executeCommand($query));  

// Enumerate
print_r($dbODBC->executeCommand($query, null, MyDb::ENUM));  

$dbODBC = null;

// PostgreSQL Example
$provider = 'PostgreSQLDb';
$hostname = '';
$username = '';
$password = '';
$dbname   = '';

$dbPgSQL = MyDb::getInstance($provider, $hostname, $username, $password, $dbname);  

$query = sprintf("SELECT 1 AS %s", $dbPgSQL->escape($provider));

// Associate
print_r($dbPgSQL->executeCommand($query));  

// Enumerate
print_r($dbPgSQL->executeCommand($query, null, MyDb::ENUM));  

$dbPgSQL = null;

// Mysql Singleton Example
$provider = 'MySQLDb';
$hostname = '';
$username = '';
$password = '';
$dbname   = '';

$db = MyDb::getConnection($provider, $hostname, $username, $password, $dbname);  

$query = sprintf("SELECT 1 AS %s", $dbMySQL->escape($provider));

// Associate
print_r($dbMySQL->executeCommand($query));  

// Clone in not permitted
$dbclone = clone $db;

?>