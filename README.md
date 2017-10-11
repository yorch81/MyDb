# MyDb #

## Description ##
Abstract Class to manage RDBMS (MySQL, MSSQLServer, ODBC, PostgreSQL) connections

## Requirements ##
* [PHP 5.4.1 or higher](http://www.php.net/)
* [mysqli extension](http://php.net/manual/en/class.mysqli.php)
* [sqlsrv extension](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx/)
* [odbc extension](http://php.net/manual/en/ref.uodbc.php)
* [pgsql extension](http://php.net/manual/en/ref.pgsql.php)
* [monolog](https://github.com/Seldaek/monolog)

## Developer Documentation ##
Execute phpdoc -d MyDb/

## Installation ##
Create file composer.json

{
    "require": {
        "yorch/mydb": "dev-master"
    }
}

Execute composer.phar install

## Example ##
~~~
// Mysql Example
$provider = 'MySQLDb';
$hostname = '';
$username = '';
$password = '';
$dbname   = '';

$dbMySQL = MyDb::getInstance($provider, $hostname, $username, $password, $dbname, 3306);  

$query = sprintf("SELECT 1 AS %s", $dbMySQL->escape($provider));

// Associate
print_r($dbMySQL->executeCommand($query));  

// Enumerate
print_r($dbMySQL->executeCommand($query, null, MyDb::ENUM));  

$dbMySQL = null;
~~~

## Notes ##
The SQL Server connection only works in MS Windows.

For PostgreSQL the parameters in SQL Query must be $#.
example: 'SELECT * FROM MYTABLE WHERE ID = $1 AND DESCRIPTION = $2'

For ODBC connection, if the query insert uses ? parameters throw this error
(COUNT field incorrect or syntax error).

Sorry, my english is bad :(.

## References ##
http://es.wikipedia.org/wiki/Patr%C3%B3n_de_dise%C3%B1o

http://es.wikipedia.org/wiki/Singleton

http://es.wikipedia.org/wiki/Abstract_Factory

## Donate ##
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=GXT4C7UZ3HFA8)

P.D. Let's go play !!!




