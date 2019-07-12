<?php
/**
 * Colors test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\Settings;

class Colors extends \Tests\Base
{
	/**
	 * Testing generation of colors css.
	 */
	public function testGenerateColorsCss()
	{
		\App\Colors::generate();
		if (\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/owners.css')) {
			\unlink(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/owners.css');
		}
		if (\file_exists(ROOT_DIRECTORY . '/app_data/owners_colors.php')) {
			\unlink(ROOT_DIRECTORY . '/app_data/owners_colors.php');
		}
		if (\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/modules.css')) {
			\unlink(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/modules.css');
		}
		if (\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/picklists.css')) {
			\unlink(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/picklists.css');
		}
		$this->assertFileNotExists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/owners.css', 'File "/public_html/layouts/resources/colors/owners.css" should not exists');
		$this->assertFileNotExists(ROOT_DIRECTORY . '/app_data/owners_colors.php', 'File "/app_data/owners_colors.php" should not exists');
		$this->assertFileNotExists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/modules.css', 'File "/public_html/layouts/resources/colors/modules.css" should not exists');
		$this->assertFileNotExists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/picklists.css', 'File "/public_html/layouts/resources/colors/picklists.css" should not exists');

		\App\Colors::generate();
		$this->assertFileExists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/owners.css', 'File "/public_html/layouts/resources/colors/owners.css" not exists');
		$this->assertFileExists(ROOT_DIRECTORY . '/app_data/owners_colors.php', 'File "/app_data/owners_colors.php" not exists');
		$this->assertFileExists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/modules.css', 'File "/public_html/layouts/resources/colors/modules.css" not exists');
		$this->assertFileExists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/picklists.css', 'File "/public_html/layouts/resources/colors/picklists.css" not exists');
	}

	/**
	 * Testing update user color.
	 */
	public function testUpdateUserColor()
	{
		\App\Colors::updateUserColor(\App\User::getActiveAdminId(), '#A0B584');
		$this->assertSame((new\App\Db\Query())->select(['cal_color'])->from('vtiger_users')->where(['id' => \App\User::getActiveAdminId()])->scalar(), '#A0B584', 'Returned user color is different from provided');
	}

	/**
	 * Testing update group color.
	 */
	public function testUpdateGroupColor()
	{
		$groupId = (new\App\Db\Query())->select(['groupid'])->from('vtiger_groups')->scalar();
		\App\Colors::updateGroupColor($groupId, '#A0B584');
		$this->assertSame((new\App\Db\Query())->select(['color'])->from('vtiger_groups')->where(['groupid' => $groupId])->scalar(), '#A0B584', 'Returned group color is different from provided');
	}

	/**
	 * Testing update module color.
	 */
	public function testUpdateModuleColor()
	{
		$moduleId = \App\Module::getModuleId('Leads');
		\App\Colors::updateModuleColor($moduleId, '#A0B584');
		$this->assertSame((new\App\Db\Query())->select(['color'])->from('vtiger_tab')->where(['tabid' => $moduleId])->scalar(), 'A0B584', 'Returned module color is different from provided');
	}

	/**
	 * Testing update picklist value color.
	 */
	public function testUpdatePicklistValueColor()
	{
		$picklistValueId = (new\App\Db\Query())->select(['activitytypeid'])->from('vtiger_activitytype')->where(['activitytype' => 'Call'])->scalar();
		\App\Colors::updatePicklistValueColor((new\App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tabid' => \App\Module::getModuleId('Calendar'), 'fieldname' => 'activitytype'])->scalar(), $picklistValueId, '#A0B584');
		$this->assertSame((new\App\Db\Query())->select(['color'])->from('vtiger_activitytype')->where(['activitytypeid' => $picklistValueId])->scalar(), 'A0B584', 'Returned picklist value color is different from provided');

		\App\Colors::updatePicklistValueColor((new\App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tabid' => \App\Module::getModuleId('Calendar'), 'fieldname' => 'activitytype'])->scalar(), $picklistValueId, '#80B584');
		$this->assertSame((new\App\Db\Query())->select(['color'])->from('vtiger_activitytype')->where(['activitytypeid' => $picklistValueId])->scalar(), '80B584', 'Returned picklist value color is different from default');
	}

	/**
	 * Testing picklist fields by module getter.
	 */
	public function testGetPicklistFieldsByModule()
	{
		$this->assertGreaterThan(0, (\count(\App\Colors::getPicklistFieldsByModule(\App\Module::getModuleId('Leads')))), 'Leads should contain picklists');
	}

	/**
	 * Testing activate module color.
	 */
	public function testActiveModuleColor()
	{
		$moduleId = \App\Module::getModuleId('Leads');
		\App\Colors::activeModuleColor($moduleId, true, '#A0B584');
		$this->assertSame((new\App\Db\Query())->select(['coloractive'])->from('vtiger_tab')->where(['tabid' => $moduleId])->scalar(), 1, 'Returned module color state is different from provided');
		$this->assertNotEmpty(\App\Colors::activeModuleColor($moduleId, true, ''), 'Returned module color should be random generated if provided empty string');
	}

	/**
	 * Testing get all filter colors.
	 */
	public function testGetAllFilterColors()
	{
		$this->assertNotEmpty(\App\Colors::getAllFilterColors(), 'Filter colors should be not empty');
		$this->assertNotEmpty(\App\Colors::getAllFilterColors(), 'Filter colors(cached) should be not empty');
	}

	/**
	 * Testing color generation.
	 */
	public function testGetRandomColor()
	{
		$this->assertNotEmpty(\App\Colors::getRandomColor(), 'Generated color should be not empty');
	}

	/**
	 * Testing color normalization.
	 */
	public function testGetColor()
	{
		$this->assertNotEmpty(\App\Colors::get('', ''), 'Normalized empty color should be random generated');
		$this->assertNotEmpty(\App\Colors::get('', 'fs$gdftd'), 'Normalized empty color with value should be random generated');
		$this->assertNotEmpty(\App\Colors::get('#', ''), 'Normalized empty color(#only) should be random generated');
		$this->assertNotEmpty(\App\Colors::get('#', 'fs$gdftd'), 'Normalized empty color(#only) with value should be random generated');
		$this->assertSame(\App\Colors::get('A0B584', ''), '#A0B584', 'Color without # prefix should be normalized');
	}

	/**
	 * Testing creation picklist color column.
	 */
	public function testAddPicklistColorColumn()
	{
		$db = \App\Db::getInstance();
		$tableSchema = $db->getSchema()->getTableSchema('vtiger_contract_priority', true);
		$this->assertNotEmpty($tableSchema, 'Table vtiger_contract_priority not exists');
		if ($tableSchema) {
			$column = $tableSchema->getColumn((string) 'color');
			$this->assertEmpty($column, 'column color in vtiger_contract_priority should not exists');
			if (null === $column) {
				\App\Colors::addPicklistColorColumn((new\App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tabid' => \App\Module::getModuleId('ServiceContracts'), 'fieldname' => 'contract_priority'])->scalar());
				\App\Cache::clear();
				$tableSchema = $db->getSchema()->getTableSchema('vtiger_contract_priority', true);
				$column = $tableSchema->getColumn((string) 'color');
				$this->assertNotEmpty($column, 'Column color should exist on vtiger_contract_priority');
				if ($column) {
					$db->createCommand()->dropColumn('vtiger_contract_priority', 'color')->execute();
					$tableSchema = $db->getSchema()->getTableSchema('vtiger_contract_priority', true);
					$column = $tableSchema->getColumn((string) 'color');
					$this->assertEmpty($column, 'Column color should be removed from vtiger_contract_priority');
				}
			}
		}
	}

	/**
	 * Reset to default values.
	 */
	public function testResetToDefault()
	{
		$moduleId = \App\Module::getModuleId('Leads');
		\App\Colors::updateUserColor(\App\User::getActiveAdminId(), '#E6FAD8');
		\App\Colors::updateGroupColor((new\App\Db\Query())->select(['groupid'])->from('vtiger_groups')->scalar(), '#E6FAD8');
		\App\Colors::activeModuleColor($moduleId, false, '#A0B584');
		$this->assertSame((new\App\Db\Query())->select(['coloractive'])->from('vtiger_tab')->where(['tabid' => $moduleId])->scalar(), 0, 'Returned module color state is different from provided');
		\App\Colors::updateModuleColor($moduleId, '');
	}
}
