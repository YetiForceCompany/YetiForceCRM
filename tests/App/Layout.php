<?php
/**
 * Layout test class.
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
		$this->assertSame(\AppConfig::main('defaultLayout'), \App\Layout::getActiveLayout(), 'Expected default layout');
	}

	/**
	 * Testing getLayoutFile function.
	 */
	public function testGetLayoutFile()
	{
		$this->assertFileExists(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . \App\Layout::getLayoutFile('styles/Main.css'), 'Expected default layout');
		$this->assertFileNotExists(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . \App\Layout::getLayoutFile('styles/NxFile.css'), 'Expected default layout');
	}

	/**
	 * Testing getAllLayouts function.
	 */
	public function testGetAllLayouts()
	{
		$result0 = \App\Layout::getAllLayouts();
		$this->assertCount(1, $result0, 'Expected only default layout');
		$this->assertSame(\App\Language::translate('LBL_DEFAULT'), $result0['basic'], 'Expected only default layout with standard label');
		\App\Db::getInstance()->createCommand()->insert('vtiger_layout', ['name'=>'unitTest0', 'label'=>'UnitTest0'])->execute();
		$result1 = \App\Layout::getAllLayouts();
		$this->assertCount(2, $result1, 'Expected only default layout');
		$this->assertSame('UnitTest0', $result0['unitTest0'], 'Expected test layout with reference label');
	}
}
