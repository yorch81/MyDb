<?php
require_once "./vendor/autoload.php";
require_once "MyDb.class.php";
require_once "config.php";

class MyDbTest extends PHPUnit_Framework_TestCase
{
    protected $db;

    protected function setUp() {
    	$hostname = $GLOBALS["hostname"];
		$username = $GLOBALS["username"];
		$password = $GLOBALS["password"];
		$dbname   = $GLOBALS["dbname"];

        $this->db = MyDb::getInstance('MySQLDb', $hostname, $username, $password, $dbname);
    }

    protected function tearDown() {
        unset($this->db);
    }

    public function testConnected() {
        $expected = true;
        $current = $this->db->isConnected();

        $this->assertEquals($expected, $current);
    }
}
?>