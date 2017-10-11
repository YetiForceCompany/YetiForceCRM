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
	 * Zip file name
	 * @var string
	 */
	private static $zipFileName;

	/**
	 * Block id
	 * @var int
	 */
	private static $blockId;

	/**
	 * Field id
	 * @var int
	 */
	private static $fieldId;

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
		$langFileToCheck = $this->getLangPathToFile('Test.php');
		foreach ($langFileToCheck as $pathToFile) {
			$this->assertFileExists($pathToFile);
		}
		$this->assertTrue((new \App\Db\Query())->from('vtiger_tab')->where(['name' => 'Test'])->exists());
	}

	/**
	 * Testing the creation of a new block for the module
	 */
	public function testCreateNewBlock()
	{
		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName('Test');
		$blockInstance = new Settings_LayoutEditor_Block_Model();
		$blockInstance->set('label', 'label block');
		$blockInstance->set('iscustom', 1);
		static::$blockId = $blockInstance->save($moduleModel);

		$row = (new \App\Db\Query())->from('vtiger_blocks')->where(['blockid' => static::$blockId])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$blockId);
		$this->assertEquals($row['blocklabel'], 'label block');
		$this->assertEquals($row['iscustom'], 1);
	}

	/**
	 * Testing the creation of a new field for the module
	 */
	public function testCreateNewField()
	{
		$param['fieldType'] = 'Text';
		$param['fieldLabel'] = 'test label';
		$param['fieldName'] = 'testfieldname';
		$param['fieldTypeList'] = 0;
		$param['fieldLength'] = 12;
		$param['decimal'] = '';
		$param['tree'] = '-';
		$param['blockid'] = static::$blockId;
		$param['sourceModule'] = 'Test';

		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($param['sourceModule']);
		$fieldModel = $moduleModel->addField($param['fieldType'], static::$blockId, $param);
		static::$fieldId = $fieldModel->getId();

		$row = (new \App\Db\Query())->from('vtiger_field')->where(['fieldid' => static::$fieldId])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$fieldId);
		$this->assertEquals($row['fieldname'], $param['fieldName']);
		$this->assertEquals($row['fieldlabel'], $param['fieldLabel']);
	}

	/**
	 * Testing the deletion of a new field for the module
	 */
	public function testDeleteNewField()
	{
		$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance(static::$fieldId);
		$this->assertTrue($fieldInstance->isCustomField(), 'Field is not customized');
		$fieldInstance->delete();

		$this->assertFalse((new App\Db\Query())->from('vtiger_field')->where(['fieldid' => static::$fieldId])->exists(), 'The record was not removed from the database ID: ' . static::$fieldId);
	}

	/**
	 * Testing the deletion of a new block for the module
	 *
	 */
	public function testDeleteNewBlock()
	{
		$this->assertFalse(Vtiger_Block_Model::checkFieldsExists(static::$blockId), 'Fields exists');
		$blockInstance = Vtiger_Block_Model::getInstance(static::$blockId);
		$this->assertTrue($blockInstance->isCustomized(), 'Block is not customized');
		$blockInstance->delete(false);
	}

	/**
	 * Testing module export
	 */
	public function testExportModule()
	{
		$moduleModel = \vtlib\Module::getInstance('Test');
		$this->assertTrue($moduleModel->isExportable(), 'Module not exportable!');
		$packageExport = new vtlib\PackageExport();

		$packageExport->export($moduleModel, '', '', false);
		static::$zipFileName = $packageExport->getZipFileName();
		$this->assertFileExists(static::$zipFileName);

		$package = new vtlib\Package();
		$this->assertEquals('Test', $package->getModuleNameFromZip(static::$zipFileName));

		$zip = new \App\Zip(static::$zipFileName, ['checkFiles' => false]);
		$zipFiles = [];
		for ($i = 0; $i < $zip->numFiles; $i++) {
			$fileName = $zip->getNameIndex($i);
			$zipFiles[] = $fileName;
		}
		$zip->close();
		$this->assertContains('manifest.xml', $zipFiles);
		$this->assertContains('modules/Test/Test.php', $zipFiles);

		$langFileToCheck = $this->getLangPathToFile('Test.php');
		foreach ($langFileToCheck as $pathToFile) {
			$pathToFile = str_replace('./', '', $pathToFile);
			$this->assertContains($pathToFile, $zipFiles);
		}
	}

	/**
	 * Testing module removal
	 * @group extended
	 */
	public function testDeleteModule()
	{
		$moduleInstance = \vtlib\Module::getInstance('Test');
		$moduleInstance->delete();
		$this->assertFileNotExists(ROOT_DIRECTORY . '/modules/Test/Test.php');

		$langFileToCheck = $this->getLangPathToFile('Test.php');
		foreach ($langFileToCheck as $pathToFile) {
			$this->assertFileNotExists($pathToFile);
		}

		$this->assertFalse((new \App\Db\Query())->from('vtiger_tab')->where(['name' => 'Test'])->exists(), 'The test module exists in the database');
	}

	/**
	 * Testing module import
	 * @group extended
	 */
	public function testImportModule()
	{
		$package = new vtlib\Package();

		$this->assertEquals('Test', $package->getModuleNameFromZip(static::$zipFileName));
		$this->assertFalse($package->isLanguageType(static::$zipFileName), 'The module is a language type');
		$this->assertFalse($package->isUpdateType(static::$zipFileName), 'The module is a update type');
		$this->assertFalse($package->isModuleBundle(static::$zipFileName), 'The module is a bundle type');

		$package->import(static::$zipFileName);

		$this->assertEquals('LBL_INVENTORY_MODULE', $package->getTypeName());
		$this->assertFileExists(ROOT_DIRECTORY . '/modules/Test/Test.php');
		$this->assertTrue((new \App\Db\Query())->from('vtiger_tab')->where(['name' => 'Test'])->exists(), 'The test module does not exist in the database');

		unlink(static::$zipFileName);
		$this->assertFileNotExists(static::$zipFileName);
	}

	/**
	 * Testing imported module removal
	 * @group extended
	 */
	public function testDeleteImportedModule()
	{
		$this->testDeleteModule();
	}

	/**
	 * Testing download librares
	 * @group extended
	 */
	public function testDownloadLibraryModule()
	{
		$removeLib = [];
		$libraries = Settings_ModuleManager_Library_Model::getAll();
		foreach ($libraries as $key => $library) {
			$removeLib[$key]['toRemove'] = Settings_ModuleManager_Library_Model::checkLibrary($key);
			$removeLib[$key]['dir'] = $library['dir'];

			//Check if remote file exists
			$header = get_headers($library['url'], 1);
			$this->assertNotRegExp('/404/', $header['Status']);

			Settings_ModuleManager_Library_Model::download($key);
			$this->assertFileExists($library['dir'] . 'version.php');
		}

		//Delete unnecessary libraries
		foreach ($removeLib as $libToRemove) {
			if ($libToRemove['toRemove']) {
				\vtlib\Functions::recurseDelete($libToRemove['dir']);
				$this->assertFileNotExists($removeLib['dir'] . 'version.php');
			}
		}
	}

	/**
	 * Testing module off
	 * @group extended
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
	 * @group extended
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

	/**
	 *
	 * @param string $fileName
	 * @return array
	 * @throws Exception
	 */
	private function getLangPathToFile($fileName)
	{
		$langFileToCheck = [];
		$allLang = \App\Language::getAll();
		foreach ($allLang as $key => $lang) {
			$langFileToCheck[] = './languages/' . $key . '/' . $fileName;
		}
		return $langFileToCheck;
	}
}
