<?php
require_once "./vendor/autoload.php";
require_once "MyDb.class.php";
require_once "config.php";

/**
 * MyDbTest 
 *
 * MyDbTest MyDb Class Test
 *
 * Copyright 2015 Jorge Alberto Ponce Turrubiates
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
 * @category   MyDbTest
 * @package    MyDbTest
 * @copyright  Copyright 2015 Jorge Alberto Ponce Turrubiates
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    1.0.0, 2015-06-23
 * @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
 */

class MyDbTest extends PHPUnit_Framework_TestCase
{
    protected $db;

    /**
     * Setup Test
     */
    protected function setUp() {
    	$hostname = $GLOBALS["hostname"];
    	$username = $GLOBALS["username"];
    	$password = $GLOBALS["password"];
    	$dbname   = $GLOBALS["dbname"];

        $this->db = MyDb::getInstance('MySQLDb', $hostname, $username, $password, $dbname);
    }

    /**
     * TearDown Test
     */
    protected function tearDown() {
        unset($this->db);
    }

    /**
     * Test Method for isConnected
     */
    public function testIsConnected() {
        $expected = true;
        $current = $this->db->isConnected();

        $this->assertEquals($expected, $current);
    }

    /**
     * Test Method for escape
     */
    public function testEscape() {
        $expected = 'escape';
        $current = $this->db->escape($expected);

        $this->assertEquals($expected, $current);
    }

    /**
     * Test Method for getProvider
     */
    public function testGetProvider() {
        $expected = 'MySQLDb';
        $current = $this->db->getProvider();

        $this->assertEquals($expected, $current);
    }

    /**
     * Test Method for executeCommand
     */
    public function testExecuteCommand() {
    	$query = "SELECT 1 AS FIELD";

        $expected =  array(array('FIELD' => 1));

        $current = $this->db->executeCommand($query);

        $this->assertEquals($expected, $current);
    }
}
?>