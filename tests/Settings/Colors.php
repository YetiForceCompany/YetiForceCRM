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
	 * Testing generation of colors css
	 */
	public function testGenerateColorsCss()
	{
		if(\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/calendar.css')) {
			\unlink(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/calendar.css');
		}
		if(\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/owners.css')) {
			\unlink(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/owners.css');
		}
		if(\file_exists(ROOT_DIRECTORY . '/user_privileges/owners_colors.php')) {
			\unlink(ROOT_DIRECTORY . '/user_privileges/owners_colors.php');
		}
		if(\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/modules.css')) {
			\unlink(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/modules.css');
		}
		if(\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/picklists.css')) {
			\unlink(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/picklists.css');
		}
		\App\Colors::generate();
		$this -> assertTrue(\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/calendar.css'));
		$this -> assertTrue(\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/owners.css'));
		$this -> assertTrue(\file_exists(ROOT_DIRECTORY . '/user_privileges/owners_colors.php'));
		$this -> assertTrue(\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/modules.css'));
		$this -> assertTrue(\file_exists(ROOT_DIRECTORY . '/public_html/layouts/resources/colors/picklists.css'));
	}

	/**
	 * Testing update user color
	 */
	public function testUpdateUserColor()
	{
		\App\Colors::updateUserColor(\App\User::getActiveAdminId(), '#A0B584');
		$this->assertSame((new\App\Db\Query())->select(['cal_color'])->from('vtiger_users')->where(['id' => \App\User::getActiveAdminId()])->scalar(), '#A0B584');
	}

	/**
	 * Testing update module color
	 */
	public function testUpdateModuleColor()
	{
		$moduleId = \App\Module::getModuleId('Leads');
		\App\Colors::updateModuleColor($moduleId, '#A0B584');
		$this->assertSame((new\App\Db\Query())->select(['color'])->from('vtiger_tab')->where(['tabid' => $moduleId])->scalar(), 'A0B584');
	}

	/**
	 * Reset to default values.
	 */
	public function testResetToDefault()
	{
		\App\Colors::updateUserColor(\App\User::getActiveAdminId(), '#E6FAD8');
		\App\Colors::updateModuleColor(\App\Module::getModuleId('Leads'), '');
	}
}
