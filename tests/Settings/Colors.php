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
		if (\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/calendar.css')) {
			\unlink(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/calendar.css');
		}
		if (\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/owners.css')) {
			\unlink(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/owners.css');
		}
		if (\file_exists(ROOT_DIRECTORY . '/user_privileges/owners_colors.php')) {
			\unlink(ROOT_DIRECTORY . '/user_privileges/owners_colors.php');
		}
		if (\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/modules.css')) {
			\unlink(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/modules.css');
		}
		if (\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/picklists.css')) {
			\unlink(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/picklists.css');
		}
		\App\Colors::generate();
		$this->assertFileExists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/calendar.css', 'File "/public_html/layouts/resources/colors/calendar.css" not exists');
		$this->assertFileExists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/owners.css', 'File "/public_html/layouts/resources/colors/owners.css" not exists');
		$this->assertFileExists(ROOT_DIRECTORY . '/user_privileges/owners_colors.php', 'File "/user_privileges/owners_colors.php" not exists');
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
	 * Testing activate module color.
	 */
	public function testActiveModuleColor()
	{
		$moduleId = \App\Module::getModuleId('Leads');
		\App\Colors::activeModuleColor($moduleId, 'true', '#A0B584');
		$this->assertSame((new\App\Db\Query())->select(['coloractive'])->from('vtiger_tab')->where(['tabid' => $moduleId])->scalar(), 1, 'Returned module color state is different from provided');
	}

	/**
	 * Testing get all filter colors.
	 */
	public function testGetAllFilterColors()
	{
		$this->assertNotEmpty(\App\Colors::getAllFilterColors(), 'Filter colors should be not empty');
	}

	/**
	 * Reset to default values.
	 */
	public function testResetToDefault()
	{
		$moduleId = \App\Module::getModuleId('Leads');
		\App\Colors::updateUserColor(\App\User::getActiveAdminId(), '#E6FAD8');
		\App\Colors::updateGroupColor((new\App\Db\Query())->select(['groupid'])->from('vtiger_groups')->scalar(), '#E6FAD8');
		\App\Colors::activeModuleColor($moduleId, 'false', '#A0B584');
		$this->assertSame((new\App\Db\Query())->select(['coloractive'])->from('vtiger_tab')->where(['tabid' => $moduleId])->scalar(), 0, 'Returned module color state is different from provided');
		\App\Colors::updateModuleColor($moduleId, '');
	}
}
