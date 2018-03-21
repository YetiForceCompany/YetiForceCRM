<?php

/**
 * Language Files test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class LanguageFiles extends \Tests\Base
{
	/**
	 * Testing language files.
	 */
	public function testLoadFiles()
	{
		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'languages', \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isFile()) {
				$this->assertNotEmpty(json_decode(file_get_contents($item->getPathname()), true), 'File: ' . $item->getPathname());
			}
		}
	}

	/**
	 * Testing translation functions.
	 */
	public function testTranslate()
	{
		\App\Language::setTemporaryLanguage('pl_pl');
		$this->assertTrue(\App\Language::translate('LBL_MONTH') === 'miesiąc');
		$this->assertTrue(\App\Language::translateArgs('LBL_VALID_RECORDS', 'Vtiger', 'aaa', 'bbb') === 'aaa z bbb są poprawne dla wybranego szablonu.');
		$this->assertTrue(\App\Language::translatePluralized('PLU_SYSTEM_WARNINGS', 'Settings::Vtiger', 1) === 'Ostrzeżenie systemowe');
		$this->assertTrue(\App\Language::translatePluralized('PLU_SYSTEM_WARNINGS', 'Settings::Vtiger', 2) === 'Ostrzeżenia systemowe');
		$this->assertTrue(\App\Language::translatePluralized('PLU_SYSTEM_WARNINGS', 'Settings::Vtiger', 9) === 'Ostrzeżeń systemowych');
	}
}
