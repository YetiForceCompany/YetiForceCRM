<?php
/**
 * Install test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers Install::<public>
 */
class Install extends TestCase
{

	public function testInstall()
	{
		require_once('install/models/InitSchema.php');

		$db = PearDatabase::getInstance();
		$initSchema = new Install_InitSchema_Model($db);
		$initSchema->initialize();
	}
	
	public function testDownloadLibrary()
	{
		Settings_ModuleManager_Library_Model::downloadAll();
	}
}
