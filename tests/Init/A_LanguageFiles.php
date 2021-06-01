<?php

/**
 * Language Files test class.
 *
 * @package   Tests
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
		$this->assertTrue(\App\Installer\Languages::download('pl-PL'), 'Error while downloading the language "pl-PL"');
		$parser = new \Seld\JsonLint\JsonParser();
		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'languages', \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isFile()) {
				try {
					$this->assertNotEmpty($parser->parse(file_get_contents($item->getPathname())));
					// @codeCoverageIgnoreStart
				} catch (\Seld\JsonLint\ParsingException $e) {
					throw new \Exception("File: {$item->getPathname()}:" . \PHP_EOL . $e->getMessage());
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
		$this->assertSame('pl-PL', \App\Language::getLanguage());
		$this->assertSame('miesiąc', \App\Language::translate('LBL_MONTH'));
		$this->assertTrue('aaa z bbb są poprawne dla wybranego szablonu.' === \App\Language::translateArgs('LBL_VALID_RECORDS', 'Vtiger', 'aaa', 'bbb'));
		$this->assertTrue('Ostrzeżenie systemowe' === \App\Language::translatePluralized('PLU_SYSTEM_WARNINGS', 'Settings::Vtiger', 1));
		$this->assertTrue('Ostrzeżenia systemowe' === \App\Language::translatePluralized('PLU_SYSTEM_WARNINGS', 'Settings::Vtiger', 2));
		$this->assertTrue('Ostrzeżeń systemowych' === \App\Language::translatePluralized('PLU_SYSTEM_WARNINGS', 'Settings::Vtiger', 9));
	}
}
