<?php
/**
 * Cron test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers ModuleManager::<public>
 */
class ModuleManager extends TestCase
{

	public function testLanguageExport()
	{
		$package = new \vtlib\LanguageExport();
		$package->export('pl_pl', ROOT_DIRECTORY . 'PL.zip', 'PL.zip');
	}

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
	}

	public function testDeleteModule()
	{
		$moduleInstance = \vtlib\Module::getInstance('Test');
		$moduleInstance->delete();
	}
}
