<?php
/**
 * Layout test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class Layout extends \Tests\Base
{
	/**
	 * Testing getActiveLayout function.
	 */
	public function testGetActiveLayout()
	{
		$this->assertSame(\App\Config::main('defaultLayout'), \App\Layout::getActiveLayout(), 'Expected default layout');
		\App\Session::set('layout', \App\Config::main('defaultLayout'));
		$this->assertSame(\App\Config::main('defaultLayout'), \App\Layout::getActiveLayout(), 'Expected default layout(session)');
	}

	/**
	 * Testing getLayoutFile function.
	 */
	public function testGetLayoutFile()
	{
		$this->assertFileExists(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . \App\Layout::getLayoutFile('modules/Accounts/resources/Detail.js'), 'Expected file in provided path');
		$this->assertFileDoesNotExist(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . \App\Layout::getLayoutFile('styles/NxFile.css'), 'Expected file in provided path not exists');
		$this->assertFileDoesNotExist(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . \App\Layout::getLayoutFile('modules/Accounts/AccountHierarchy.tpl'), 'Expected file in provided path not exists');
	}

	/**
	 * Testing getAllLayouts function.
	 */
	public function testGetAllLayouts()
	{
		$result0 = \App\Layout::getAllLayouts();
		$this->assertCount(1, $result0, 'Expected only default layout');
		$this->assertSame(\App\Language::translate('LBL_DEFAULT'), $result0['basic'], 'Expected only default layout with standard label');
		\App\Db::getInstance()->createCommand()->insert('vtiger_layout', ['name' => 'unitTest0', 'label' => 'UnitTest0'])->execute();
		$result1 = \App\Layout::getAllLayouts();
		$this->assertCount(2, $result1, 'Expected only two layouts, default and unitTest0');
		$this->assertSame('UnitTest0', $result1['unitTest0'], 'Expected test layout with reference label');
		\App\Db::getInstance()->createCommand()->delete('vtiger_layout', ['name' => 'unitTest0', 'label' => 'UnitTest0'])->execute();
	}

	/**
	 * Testing getPublicUrl.
	 */
	public function testGetPublicUrl()
	{
		$this->assertSame(\App\Config::main('site_URL') . 'public_html/unitTest', \App\Layout::getPublicUrl('unitTest', true), 'Url differs from reference');
	}

	/**
	 * Testing getImagePath.
	 */
	public function testGetImagePath()
	{
		$this->assertSame('public_html/layouts/basic/images/Accounts.png', \App\Layout::getImagePath('Accounts.png'), 'Image path differs from provided');
	}

	/**
	 * Testing getTemplatePath function.
	 */
	public function testGetTemplatePath()
	{
		$this->assertSame('modules/Vtiger/basic', \App\Layout::getTemplatePath('basic'), 'Tempate path differs from provided');
	}

	/**
	 * Testing getUniqueId function.
	 */
	public function testGetUniqueId()
	{
		$this->assertSame(0, strpos(\App\Layout::getUniqueId('basic'), 'basic'), 'Unique ID should contain provided prefix');
	}
}
