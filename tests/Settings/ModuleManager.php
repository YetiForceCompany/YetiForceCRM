<?php
/**
 * ModuleManager test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\Settings;

class ModuleManager extends \Tests\Base
{
	/**
	 * Zip file name.
	 *
	 * @var string
	 */
	private static $zipFileName;

	/**
	 * Block id.
	 *
	 * @var int
	 */
	private static $blockId;

	/**
	 * Array of fields id.
	 *
	 * @var array()
	 */
	private static $fieldsId;

	/**
	 * Id for field extra.
	 *
	 * @var array()
	 */
	private static $fieldsExtraId;

	/**
	 * Tables name for uitype: 16, 15.
	 *
	 * @var array()
	 */
	private static $tablesName;

	/**
	 * Id for picklist.
	 *
	 * @var array()
	 */
	private static $pickList;

	/**
	 * Id for tree.
	 *
	 * @var int
	 */
	private static $treeId;

	/**
	 * Testing language exports.
	 */
	public function testLanguageExport()
	{
		$package = new \vtlib\LanguageExport();
		$package->exportLanguage('pl_pl', ROOT_DIRECTORY . '/PL.zip', 'PL.zip');
		$this->assertTrue(file_exists(ROOT_DIRECTORY . '/PL.zip') && filesize(ROOT_DIRECTORY . '/PL.zip') > 0);
		unlink(ROOT_DIRECTORY . '/PL.zip');
	}

	/**
	 * Testing the module creation.
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
		$langFileToCheck = $this->getLangPathToFile('Test.json');
		foreach ($langFileToCheck as $pathToFile) {
			$this->assertFileExists($pathToFile);
		}
		$this->assertTrue((new \App\Db\Query())->from('vtiger_tab')->where(['name' => 'Test'])->exists());
	}

	/**
	 * @param string $fileName
	 *
	 * @throws Exception
	 *
	 * @return array
	 */
	private function getLangPathToFile($fileName)
	{
		$langFileToCheck = [];
		$allLang = \App\Language::getAll();
		foreach ($allLang as $key => $lang) {
			$langFileToCheck[] = 'languages' . DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR . $fileName;
		}
		return $langFileToCheck;
	}

	/**
	 * Testing the creation of a new block for the module.
	 */
	public function testCreateNewBlock()
	{
		$moduleModel = \Settings_LayoutEditor_Module_Model::getInstanceByName('Test');
		$blockInstance = new \Settings_LayoutEditor_Block_Model();
		$blockInstance->set('label', 'label block');
		$blockInstance->set('iscustom', 1);
		static::$blockId = $blockInstance->save($moduleModel);

		$row = (new \App\Db\Query())->from('vtiger_blocks')->where(['blockid' => static::$blockId])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$blockId);
		$this->assertSame($row['blocklabel'], 'label block');
		$this->assertSame($row['iscustom'], 1);
	}

	/**
	 * Testing the creation of a new field for the module.
	 *
	 * @param string $type
	 * @param array  $param
	 * @dataProvider providerForField
	 */
	public function testCreateNewField($type, $param, $suffix = '')
	{
		$key = $type . $suffix;
		$param['fieldType'] = $type;
		$param['fieldLabel'] = $type . 'FL' . $suffix;
		$param['fieldName'] = strtolower($type . 'FL' . $suffix);
		$param['blockid'] = static::$blockId;
		$param['sourceModule'] = 'Test';
		if ($type === 'Tree' || $type === 'CategoryMultipicklist') {
			//Add a tree if it does not exist
			if (empty(static::$treeId)) {
				static::$treeId = (new TreesManager())->testAddTree(1, \Settings_LayoutEditor_Module_Model::getInstanceByName('Test')->getId());
			}
			$param['tree'] = static::$treeId;
		} elseif ($type === 'MultiReferenceValue') {
			$param['MRVField'] = $this->getMRVField();
		}

		$moduleModel = \Settings_LayoutEditor_Module_Model::getInstanceByName($param['sourceModule']);
		$fieldModel = $moduleModel->addField($param['fieldType'], static::$blockId, $param);
		static::$fieldsId[$key] = $fieldModel->getId();
		$details = $moduleModel->getTypeDetailsForAddField($type, $param);
		$row = (new \App\Db\Query())->from('vtiger_field')->where(['fieldid' => static::$fieldsId[$key], 'tabid' => $moduleModel->getId()])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$fieldsId[$key]);
		$this->assertSame($row['fieldname'], $param['fieldName']);
		$this->assertSame($row['fieldlabel'], $param['fieldLabel']);
		$this->assertSame($row['typeofdata'], $details['typeofdata']);
		$this->assertSame($row['uitype'], $details['uitype']);

		$this->assertTrue((new \App\Db\Query())->from('vtiger_def_org_field')->where(['fieldid' => static::$fieldsId[$key], 'tabid' => $moduleModel->getId()])->exists(), 'No record in the table "vtiger_def_org_field" for type ' . $type);

		$profilesId = \vtlib\Profile::getAllIds();
		$this->assertCount((new \App\Db\Query())->from('vtiger_profile2field')->where(['fieldid' => static::$fieldsId[$key]])->count(), $profilesId, "The field \"$type\" did not add correctly to the profiles");

		switch ($row['uitype']) {
			case 11: //Phone
				$rowExtra = (new \App\Db\Query())->from('vtiger_field')->where(['fieldname' => $param['fieldName'] . '_extra'])->one();
				$this->assertNotFalse($rowExtra, 'No "extra" record for uitype: ' . $row['uitype']);
				$this->assertCount((new \App\Db\Query())->from('vtiger_profile2field')->where(['fieldid' => $rowExtra['fieldid']])->count(), $profilesId, "The \"extra\" field \"$type\" did not add correctly to the profiles");
				static::$fieldsExtraId[$key] = $rowExtra['fieldid'];
				break;
			case 10: //Related1M
				$this->assertCount((new \App\Db\Query())->from('vtiger_fieldmodulerel')->where(['fieldid' => static::$fieldsId[$key]])->count(), $param['referenceModule'], 'Problem with table "vtiger_fieldmodulerel" in database');
				break;
			case 16: //Picklist
				static::$tablesName[$key] = 'vtiger_' . $param['fieldName'];
				$this->assertNotNull(\App\Db::getInstance()->getTableSchema(static::$tablesName[$key]), 'Table "' . static::$tablesName[$key] . '" does not exist');
				$this->assertCount(0, array_diff($param['pickListValues'], (new \App\Db\Query())->select($param['fieldName'])->from(static::$tablesName[$key])->column()), 'Bad values in the table "' . static::$tablesName[$key] . '"');
				break;
			case 15: //Picklist
			case 33: //MultiSelectCombo
				static::$tablesName[$key] = 'vtiger_' . $param['fieldName'];
				$this->assertNotNull(\App\Db::getInstance()->getTableSchema(static::$tablesName[$key]), 'Table "' . static::$tablesName[$key] . '" does not exist');
				$this->assertCount(0, array_diff($param['pickListValues'], (new \App\Db\Query())->select($param['fieldName'])->from(static::$tablesName[$key])->column()), 'Bad values in the table "' . static::$tablesName[$key] . '"');

				$rowPicklist = (new \App\Db\Query())->from('vtiger_picklist')->where(['name' => $param['fieldName']])->one();
				static::$pickList[$key] = $param['pickListValues'];
				$this->assertNotFalse($rowPicklist, 'The record from "vtiger_picklist" not exists NAME: ' . $param['fieldName']);

				$this->assertSame((new \App\Db\Query())->from('vtiger_role')->count() * count($param['pickListValues']), (new \App\Db\Query())->from('vtiger_role2picklist')->where(['picklistid' => $rowPicklist['picklistid']])->count(), 'Wrong number of rows in the table "vtiger_role2picklist"');
				break;
			case 305: //MultiReferenceValue
				$this->assertTrue((new \App\Db\Query())->from('s_#__multireference')->where(['source_module' => 'Test', 'dest_module' => 'Contacts'])->exists(), 'No record in the table "s_yf_multireference" for type ' . $type);
				break;
		}
	}

	/**
	 * Get Id of MultiReferenceValue field.
	 *
	 * @return int
	 */
	private function getMRVField()
	{
		$source_Module = \vtlib\Module::getInstance('Test');
		$moduleInstance = \vtlib\Module::getInstance('Contacts');
		$source_Module->setRelatedList($moduleInstance, 'TestRel123', ['ADD', 'SELECT'], 'getRelatedList');

		$moduleModel = \Settings_LayoutEditor_Module_Model::getInstanceByName('Test');
		$fields = [];
		foreach ($moduleModel->getRelations() as $value) {
			foreach ($value->getFields() as $valF) {
				$fields[] = $valF->getId();
			}
		}
		return $fields[0];
	}

	/**
	 * Data provider for testCreateNewField and testDeleteNewField.
	 *
	 * @return array
	 * @codeCoverageIgnore
	 */
	public function providerForField()
	{
		return [
			['Text', ['fieldTypeList' => 0, 'fieldLength' => 12]],
			['Decimal', ['fieldTypeList' => 0, 'fieldLength' => 6, 'decimal' => 2]],
			['Integer', ['fieldTypeList' => 0, 'fieldLength' => 2]],
			['Percent', ['fieldTypeList' => 0]],
			['Currency', ['fieldTypeList' => 0, 'fieldLength' => 4, 'decimal' => 3]],
			['Date', ['fieldTypeList' => 0]],
			['Email', ['fieldTypeList' => 0]],
			['URL', ['fieldTypeList' => 0]],
			['Checkbox', ['fieldTypeList' => 0]],
			['TextArea', ['fieldTypeList' => 0]],
			['Skype', ['fieldTypeList' => 0]],
			['Time', ['fieldTypeList' => 0]],
			['Editor', ['fieldTypeList' => 0]],
			['Phone', ['fieldTypeList' => 0]],
			['Related1M', ['fieldTypeList' => 0, 'referenceModule' => ['Contacts', 'Accounts', 'Leads']]],
			['Picklist', ['fieldTypeList' => 0, 'pickListValues' => ['a1', 'a2', 'a3']]],
			['Picklist', ['fieldTypeList' => 0, 'pickListValues' => ['b1', 'b2', 'b3'], 'isRoleBasedPickList' => 1], '2'],
			['MultiSelectCombo', ['fieldTypeList' => 0, 'pickListValues' => ['c1', 'c2', 'c3']]],
			['Tree', ['fieldTypeList' => 0]],
			['CategoryMultipicklist', ['fieldTypeList' => 0]],
			['MultiReferenceValue', ['fieldTypeList' => 0, 'MRVModule' => 'Contacts']],
		];
	}

	/**
	 * Testing the deletion of a new field.
	 *
	 * @link         https://phpunit.de/manual/3.7/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers
	 * @dataProvider providerForField
	 */
	public function testDeleteNewField($type, $param, $suffix = '')
	{
		$key = $type . $suffix;
		$fieldInstance = \Settings_LayoutEditor_Field_Model::getInstance(static::$fieldsId[$key]);
		$uitype = $fieldInstance->getUIType();
		$columnName = $fieldInstance->getColumnName();
		$this->assertTrue($fieldInstance->isCustomField(), 'Field is not customized');
		$fieldInstance->delete();

		$this->assertFalse((new \App\Db\Query())->from('vtiger_field')->where(['fieldid' => static::$fieldsId[$key]])->exists(), 'The record was not removed from the database ID: ' . static::$fieldsId[$key]);
		$schema = \App\Db::getInstance()->getSchema();
		$schema->refresh();
		switch ($uitype) {
			case 11: //Phone
				$this->assertFalse((new \App\Db\Query())->from('vtiger_field')->where(['fieldid' => static::$fieldsExtraId[$key]])->exists(), 'The record "extra" was not removed from the database ID: ' . static::$fieldsExtraId[$key]);
				break;
			case 10: //Related1M
				$this->assertSame((new \App\Db\Query())->from('vtiger_fieldmodulerel')->where(['fieldid' => static::$fieldsId[$key]])->count(), 0, 'Problem with table "vtiger_fieldmodulerel" in database');
				break;
			case 16: //Picklist
				$this->assertNull($schema->getTableSchema(static::$tablesName[$key]), 'Table "' . static::$tablesName[$key] . '" exist');
				break;
			case 15: //Picklist
			case 33: //MultiSelectCombo
				$this->assertNull($schema->getTableSchema(static::$tablesName[$key]), 'Table "' . static::$tablesName[$key] . '" exist');
				$this->assertFalse((new \App\Db\Query())->from('vtiger_picklist')->where(['name' => $columnName])->exists(), 'The record from "vtiger_picklist" was not removed from the database ID: ' . static::$fieldsExtraId[$key]);

				$this->assertSame(0, (new \App\Db\Query())->from('vtiger_role2picklist')->where(['picklistid' => static::$pickList[$key]])->count(), 'All rows in the table "vtiger_role2picklist" have not been deleted');
				break;
			case 305: //MultiReferenceValue
				$this->assertFalse((new \App\Db\Query())->from('s_#__multireference')->where(['source_module' => 'Test', 'dest_module' => 'Contacts'])->exists(), 'The record from "s_#__multireference" was not removed.');
				break;
		}
	}

	/**
	 * Testing the deletion of a new block for the module.
	 */
	public function testDeleteNewBlock()
	{
		$this->assertFalse(\Vtiger_Block_Model::checkFieldsExists(static::$blockId), 'Fields exists');
		$blockInstance = \Vtiger_Block_Model::getInstance(static::$blockId);
		$this->assertTrue($blockInstance->isCustomized(), 'Block is not customized');
		$blockInstance->delete(false);
	}

	/**
	 * Testing module export.
	 */
	public function testExportModule()
	{
		$moduleModel = \vtlib\Module::getInstance('Test');
		$this->assertTrue($moduleModel->isExportable(), 'Module not exportable!');
		$packageExport = new \vtlib\PackageExport();

		$packageExport->export($moduleModel, '', '', false);
		static::$zipFileName = $packageExport->getZipFileName();
		$this->assertFileExists(static::$zipFileName);

		$package = new \vtlib\Package();
		$this->assertSame('Test', $package->getModuleNameFromZip(static::$zipFileName));

		$zip = \App\Zip::openFile(static::$zipFileName, ['checkFiles' => false]);
		$zipFiles = [];
		for ($i = 0; $i < $zip->numFiles; ++$i) {
			$fileName = $zip->getNameIndex($i);
			$zipFiles[] = $fileName;
		}
		$zip->close();
		$this->assertContains('manifest.xml', $zipFiles);
		$this->assertContains('modules' . DIRECTORY_SEPARATOR . 'Test' . DIRECTORY_SEPARATOR . 'Test.php', $zipFiles);

		$langFileToCheck = $this->getLangPathToFile('Test.json');
		foreach ($langFileToCheck as $pathToFile) {
			$this->assertContains($pathToFile, $zipFiles);
		}
	}

	/**
	 * Testing module removal.
	 */
	public function testDeleteModule()
	{
		\App\Db::getInstance()->getSchema()->refresh();
		$moduleInstance = \vtlib\Module::getInstance('Test');
		$moduleInstance->delete();
		$this->assertFileNotExists(ROOT_DIRECTORY . '/modules/Test/Test.php');

		$langFileToCheck = $this->getLangPathToFile('Test.json');
		foreach ($langFileToCheck as $pathToFile) {
			$this->assertFileNotExists(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $pathToFile);
		}
		$this->assertFalse((new \App\Db\Query())->from('vtiger_tab')->where(['name' => 'Test'])->exists(), 'The test module exists in the database');
		$this->assertFalse((new \App\Db\Query())->from('vtiger_trees_templates')->where(['templateid' => static::$treeId])->exists(), 'The tree was not removed');
	}

	/**
	 * Testing module import.
	 */
	public function testImportModule()
	{
		\App\Db::getInstance()->getSchema()->refresh();
		$package = new \vtlib\Package();

		$this->assertSame('Test', $package->getModuleNameFromZip(static::$zipFileName));
		$this->assertFalse($package->isLanguageType(static::$zipFileName), 'The module is a language type');
		$this->assertFalse($package->isUpdateType(static::$zipFileName), 'The module is a update type');
		$this->assertFalse($package->isModuleBundle(static::$zipFileName), 'The module is a bundle type');

		$package->import(static::$zipFileName);
		$this->assertSame('LBL_INVENTORY_MODULE', $package->getTypeName());
		$this->assertFileExists(ROOT_DIRECTORY . '/modules/Test/Test.php');
		$this->assertTrue((new \App\Db\Query())->from('vtiger_tab')->where(['name' => 'Test'])->exists(), 'The test module does not exist in the database');

		unlink(static::$zipFileName);
		$this->assertFileNotExists(static::$zipFileName);
	}

	/**
	 * Testing download librares.
	 */
	public function testDownloadLibraryModule()
	{
		$libraries = \Settings_ModuleManager_Library_Model::getAll();
		foreach ($libraries as $key => $library) {
			//Check if remote file exists
			$header = get_headers($library['url'], 1);
			$this->assertNotRegExp('/404/', $header['Status']);

			\Settings_ModuleManager_Library_Model::download($key);
			$this->assertFileExists($library['dir'] . 'version.php');
		}
	}

	/**
	 * Testing module off.
	 */
	public function testOffAllModule()
	{
		$allModules = \Settings_ModuleManager_Module_Model::getAll();
		$moduleManagerModel = new \Settings_ModuleManager_Module_Model();
		foreach ($allModules as $module) {
			//Turn off the module if it is on
			if ((int) $module->get('presence') !== 1) {
				$moduleManagerModel->disableModule($module->get('name'));
				$this->assertSame(1, (new \App\Db\Query())->select('presence')->from('vtiger_tab')->where(['tabid' => $module->getId()])->scalar());
			}
		}
	}

	/**
	 * Testing module on.
	 */
	public function testOnAllModule()
	{
		$allModules = \Settings_ModuleManager_Module_Model::getAll();
		$moduleManagerModel = new \Settings_ModuleManager_Module_Model();
		foreach ($allModules as $module) {
			//Turn on the module if it is off
			if ((int) $module->get('presence') !== 0) {
				$moduleManagerModel->enableModule($module->get('name'));
				$this->assertSame(0, (new \App\Db\Query())->select('presence')->from('vtiger_tab')->where(['tabid' => $module->getId()])->scalar());
			}
		}
	}
}
