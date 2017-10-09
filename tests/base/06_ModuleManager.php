<?php

/**
 * ModuleManager test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class ModuleManager extends TestCase
{

	/**
	 * Testing language exports
	 */
	public function testLanguageExport()
	{
		$package = new \vtlib\LanguageExport();
		$package->exportLanguage('pl_pl', ROOT_DIRECTORY . '/PL.zip', 'PL.zip');
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
		$this->assertFileExists(ROOT_DIRECTORY . '/modules/Test/Test.php');
		$this->assertTrue((new \App\Db\Query())->from('vtiger_tab')->where(['name' => 'Test'])->exists());
	}

	/**
	 * Testing module export
	 */
	public function testExportModule()
	{
		$moduleModel = \vtlib\Module::getInstance('Test');
		$this->assertTrue($moduleModel->isExportable(), 'Module not exportable!');
		$package = new vtlib\PackageExport();

		$zipFileName = $package->_export_tmpdir . '/' . $moduleModel->name . '_' . date('Y-m-d-Hi') . '_' . $moduleModel->version . '.zip';
		//Remove file if exists
		if (file_exists($zipFileName)) {
			unlink($zipFileName);
		}
		$package->export($moduleModel, '', '', false);
		$this->assertFileExists($zipFileName);
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

	/**
	 * Testing module off
	 */
	public function testOffAllModule()
	{
		$allModules = Settings_ModuleManager_Module_Model::getAll();
		$moduleManagerModel = new Settings_ModuleManager_Module_Model();
		foreach ($allModules as $module) {
			//Turn off the module if it is on
			if ((int) $module->get('presence') !== 1) {
				$moduleManagerModel->disableModule($module->get('name'));
				$this->assertEquals(1, (new \App\Db\Query())->select('presence')->from('vtiger_tab')->where(['tabid' => $module->getId()])->scalar());
			}
		}
	}

	/**
	 * Testing module on
	 */
	public function testOnAllModule()
	{
		$allModules = Settings_ModuleManager_Module_Model::getAll();
		$moduleManagerModel = new Settings_ModuleManager_Module_Model();
		foreach ($allModules as $module) {
			//Turn on the module if it is off
			if ((int) $module->get('presence') !== 0) {
				$moduleManagerModel->enableModule($module->get('name'));
				$this->assertEquals(0, (new \App\Db\Query())->select('presence')->from('vtiger_tab')->where(['tabid' => $module->getId()])->scalar());
			}
		}
	}
}
