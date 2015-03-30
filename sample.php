<?php
require_once('MyDb.class.php');

// Mysql Example
$provider = 'MySQLDb';
$hostname = 'localhost';
$username = 'root';
$password = 'r00tmysql';
$dbname   = 'yorch';

$dbMySQL = Net\Yorch\MyDb::getInstance($provider, $hostname, $username, $password, $dbname);  

$query = sprintf("SELECT 2 AS %s", $dbMySQL->escape($provider));

// Associate
print_r($dbMySQL->executeCommand($query));  

// Enumerate
print_r($dbMySQL->executeCommand($query, null, Net\Yorch\MyDb::ENUM));  

$dbMySQL = null;

?>