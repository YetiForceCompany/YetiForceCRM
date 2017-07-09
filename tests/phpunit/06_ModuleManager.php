<?php

/**
 * Cron test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers ModuleManager::<public>
 */
class ModuleManager extends TestCase
{

	/**
	 * Testing language exports
	 */
	public function testLanguageExport()
	{
		$package = new \vtlib\LanguageExport();
		$package->export('pl_pl', ROOT_DIRECTORY . '/PL.zip', 'PL.zip');
		$this->assertTrue(file_exists(ROOT_DIRECTORY . '/PL.zip') && filesize(ROOT_DIRECTORY . '/PL.zip') > 0);
		unlink(ROOT_DIRECTORY . '/PL.zip');
	}

	/**
	 * Testing the module creation
	 */
	public function testCreateModule()
	{
		$moduleManagerModel = new \Settings_ModuleManager_Module_Model();
		$moduleManagerModel->createModule([
			'module_name' => 'Test',
			'entityfieldname' => 'test',
			'module_label' => 'Test',
			'entitytype' => 1,
			'entityfieldlabel' => 'Test',
		]);
		$this->assertTrue(file_exists(ROOT_DIRECTORY . '/modules/Test/Test.php'));
		$this->assertTrue((new \App\Db\Query())->from('vtiger_tab')->where(['name' => 'Test'])->exists());
	}

	/**
	 * Testing module removal
	 */
	public function testDeleteModule()
	{
		$moduleInstance = \vtlib\Module::getInstance('Test');
		$moduleInstance->delete();
		$this->assertFalse(file_exists(ROOT_DIRECTORY . '/modules/Test/Test.php'));
		$this->assertFalse((new \App\Db\Query())->from('vtiger_tab')->where(['name' => 'Test'])->exists());
	}
}
