<?php

/**
 * Language Files test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\Init;

class A_LanguageFiles extends \Tests\Base
{
	/**
	 * Testing language files.
	 */
	public function testLoadFiles()
	{
		$parser = new \Seld\JsonLint\JsonParser();
		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'languages', \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isFile()) {
				try {
					$this->assertNotEmpty($parser->parse(file_get_contents($item->getPathname())));
					// @codeCoverageIgnoreStart
				} catch (\Seld\JsonLint\ParsingException $e) {
					$this->fail("File: {$item->getPathname()}:" . \PHP_EOL . $e->getMessage());
				}
				// @codeCoverageIgnoreEnd
			}
		}
	}

	/**
	 * Testing translation functions.
	 */
	public function testTranslate()
	{
		\App\Language::setTemporaryLanguage('pl-PL');
		$this->assertTrue(\App\Language::translate('LBL_MONTH') === 'miesiąc');
		$this->assertTrue(\App\Language::translateArgs('LBL_VALID_RECORDS', 'Vtiger', 'aaa', 'bbb') === 'aaa z bbb są poprawne dla wybranego szablonu.');
		$this->assertTrue(\App\Language::translatePluralized('PLU_SYSTEM_WARNINGS', 'Settings::Vtiger', 1) === 'Ostrzeżenie systemowe');
		$this->assertTrue(\App\Language::translatePluralized('PLU_SYSTEM_WARNINGS', 'Settings::Vtiger', 2) === 'Ostrzeżenia systemowe');
		$this->assertTrue(\App\Language::translatePluralized('PLU_SYSTEM_WARNINGS', 'Settings::Vtiger', 9) === 'Ostrzeżeń systemowych');
	}
}
