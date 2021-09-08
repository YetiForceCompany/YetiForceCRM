<?php

/**
 * Init language files test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\Init;

/**
 * Init language files test class.
 *
 * @internal
 * @coversNothing
 */
final class LanguageFilesTest extends \Tests\Base
{
	/**
	 * Testing language files.
	 */
	public function testLoadFiles()
	{
		static::assertTrue(\App\Installer\Languages::download('pl-PL'), 'Error while downloading the language "pl-PL"');
		$parser = new \Seld\JsonLint\JsonParser();
		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'languages', \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isFile()) {
				try {
					static::assertNotEmpty($parser->parse(file_get_contents($item->getPathname())));
					// @codeCoverageIgnoreStart
				} catch (\Seld\JsonLint\ParsingException $e) {
					throw new \Exception("File: {$item->getPathname()}:" . PHP_EOL . $e->getMessage());
				}
				// @codeCoverageIgnoreEnd
			}
		}
	}
}
