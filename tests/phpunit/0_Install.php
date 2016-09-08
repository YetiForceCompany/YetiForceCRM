<?php

/**
 * Cron test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class Install extends TestCase {

    public function test() {
	require_once('install/models/InitSchema.php');

	$db = PearDatabase::getInstance();
	$initSchema = new Install_InitSchema_Model($db);
	$initSchema->initialize();
    }

}
